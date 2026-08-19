<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BroadcastRecipientResource;
use App\Http\Resources\Api\BroadcastResource;
use App\Http\Resources\Api\MessageResource;
use App\Http\Resources\Api\MessageThreadResource;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\MessageBroadcast;
use App\Models\MessageTemplate;
use App\Models\User;
use App\Services\BroadcastSegmentService;
use App\Services\MessageTemplateService;
use App\Services\MessagingService;
use App\Services\NotificationFeedService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function threads(Request $request)
    {
        $user = $request->user();
        $messagingService = app(MessagingService::class);

        $threads = Conversation::query()
            ->whereHas('participants', fn ($query) => $query->where('user_id', $user->id))
            ->with([
                'participants.user:id,name',
                'messages' => fn ($query) => $query->latest('sent_at')->limit(1),
            ])
            ->orderByDesc('last_message_at')
            ->paginate(20);

        $threads->getCollection()->transform(function (Conversation $thread) use ($user, $messagingService) {
            $participant = $thread->participants->firstWhere('user_id', $user->id);
            $thread->unread_count = $messagingService->unreadCountForConversation(
                (int) $thread->id,
                (int) $user->id,
                $participant?->last_read_at
            );

            return $thread;
        });

        return MessageThreadResource::collection($threads);
    }

    public function unreadCount(Request $request, MessagingService $messagingService)
    {
        return response()->json([
            'count' => $messagingService->unreadCountFor($request->user()),
        ]);
    }

    public function show(Request $request, Conversation $conversation)
    {
        abort_unless($conversation->participants()->where('user_id', $request->user()->id)->exists(), 403);

        $conversation->load([
            'participants.user:id,name',
            'messages' => fn ($query) => $query->with('sender:id,name')->orderBy('sent_at'),
        ]);

        ConversationParticipant::query()
            ->where('conversation_id', $conversation->id)
            ->where('user_id', $request->user()->id)
            ->update(['last_read_at' => now()]);

        $conversation->unread_count = 0;
        $conversation->include_messages = true;

        return new MessageThreadResource($conversation);
    }

    public function send(Request $request, MessagingService $messagingService, MessageTemplateService $templates)
    {
        $validated = $request->validate([
            'conversation_id' => 'nullable|exists:conversations,id',
            'recipient_user_id' => 'nullable|exists:users,id',
            'body' => 'nullable|string|max:5000',
            'template_id' => 'nullable|exists:message_templates,id',
            'subject' => 'nullable|string|max:255',
            'variables' => 'nullable|array',
        ]);
        abort_unless(! empty($validated['conversation_id']) || ! empty($validated['recipient_user_id']), 422);
        abort_unless(! empty($validated['body']) || ! empty($validated['template_id']), 422, 'body أو template_id مطلوب.');

        $sender = $request->user();
        $conversation = null;
        $recipient = null;

        if (! empty($validated['conversation_id'])) {
            $conversation = Conversation::findOrFail((int) $validated['conversation_id']);
            abort_unless($conversation->participants()->where('user_id', $sender->id)->exists(), 403);
            $recipient = $conversation->users()->where('users.id', '!=', $sender->id)->first();
        } else {
            $recipient = User::findOrFail((int) $validated['recipient_user_id']);
            abort_unless($this->canMessage($sender, $recipient), 403);
            $conversation = $messagingService->findOrCreateDirectConversation($sender, $recipient, $validated['subject'] ?? null);
        }

        $body = (string) ($validated['body'] ?? '');
        if (! empty($validated['template_id'])) {
            $template = MessageTemplate::query()->findOrFail((int) $validated['template_id']);
            abort_unless(
                (int) $template->created_by_user_id === (int) $sender->id || $template->category === 'global',
                403
            );
            $body = $templates->renderTemplate(
                $template,
                $recipient,
                $sender,
                is_array($validated['variables'] ?? null) ? $validated['variables'] : []
            );
        } elseif (str_contains($body, '{{')) {
            $body = $templates->render(
                $body,
                array_merge(
                    $templates->contextFor($recipient, $sender),
                    is_array($validated['variables'] ?? null) ? $validated['variables'] : []
                )
            );
        }

        $message = $messagingService->sendMessage($conversation, $sender, $body);

        $recipientIds = $conversation->participants()
            ->where('user_id', '!=', $sender->id)
            ->pluck('user_id')
            ->all();

        app(NotificationFeedService::class)->pushToUsers(
            $recipientIds,
            'message.received',
            'رسالة جديدة',
            'لديك رسالة جديدة من '.$sender->name,
            ['conversation_id' => $conversation->id]
        );

        return response()->json([
            'conversation_id' => $conversation->id,
            'status' => 'ok',
            'message' => new MessageResource($message),
        ]);
    }

    public function templates(Request $request, MessageTemplateService $templateService)
    {
        $templates = MessageTemplate::query()
            ->where('is_active', true)
            ->where(function ($query) use ($request) {
                $query->where('created_by_user_id', $request->user()->id)->orWhere('category', 'global');
            })
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $templates->map(fn (MessageTemplate $template) => $templateService->present($template))->values(),
            'available_variables' => $templateService->availableVariables(),
        ]);
    }

    public function renderTemplate(Request $request, MessageTemplateService $templateService)
    {
        $validated = $request->validate([
            'template_id' => 'nullable|exists:message_templates,id',
            'body' => 'nullable|string|max:5000',
            'recipient_user_id' => 'nullable|exists:users,id',
            'variables' => 'nullable|array',
        ]);
        abort_unless(! empty($validated['template_id']) || ! empty($validated['body']), 422);

        $sender = $request->user();
        $recipient = ! empty($validated['recipient_user_id'])
            ? User::query()->find((int) $validated['recipient_user_id'])
            : null;

        if (! empty($validated['template_id'])) {
            $template = MessageTemplate::query()->findOrFail((int) $validated['template_id']);
            abort_unless(
                (int) $template->created_by_user_id === (int) $sender->id || $template->category === 'global',
                403
            );
            $rendered = $templateService->renderTemplate(
                $template,
                $recipient,
                $sender,
                is_array($validated['variables'] ?? null) ? $validated['variables'] : []
            );
            $sourceBody = $template->body;
        } else {
            $sourceBody = (string) $validated['body'];
            $rendered = $templateService->render(
                $sourceBody,
                array_merge(
                    $templateService->contextFor($recipient, $sender),
                    is_array($validated['variables'] ?? null) ? $validated['variables'] : []
                )
            );
        }

        return response()->json([
            'data' => [
                'body' => $sourceBody,
                'rendered' => $rendered,
                'variables_used' => $templateService->detectVariables($sourceBody),
                'context' => $templateService->contextFor($recipient, $sender),
            ],
        ]);
    }

    public function broadcast(Request $request, MessagingService $messagingService, BroadcastSegmentService $segments, MessageTemplateService $templates)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'body' => 'nullable|string|max:5000',
            'template_id' => 'nullable|exists:message_templates,id',
            'segment_type' => 'required|in:all_clients,coach_clients,inactive_clients,membership_expiring',
        ]);
        abort_unless(! empty($validated['body']) || ! empty($validated['template_id']), 422, 'body أو template_id مطلوب.');

        $sender = $request->user();
        abort_unless($sender->hasAnyRole(['admin', 'coach']), 403);

        $segmentType = $validated['segment_type'];
        if ($sender->hasRole('coach') && ! $sender->hasRole('admin')) {
            $segmentType = 'coach_clients';
        }

        $recipients = $segments->resolveRecipients($sender, $segmentType);
        abort_if($recipients->isEmpty(), 422, 'لا يوجد مستلمون في هذه الشريحة.');

        $templateId = null;
        $body = (string) ($validated['body'] ?? '');
        if (! empty($validated['template_id'])) {
            $template = MessageTemplate::query()->findOrFail((int) $validated['template_id']);
            abort_unless(
                (int) $template->created_by_user_id === (int) $sender->id || $template->category === 'global',
                403
            );
            $templateId = $template->id;
            $body = $template->body;
        }

        $broadcast = $messagingService->queueBroadcast(
            $sender,
            $recipients,
            $body,
            $validated['title'] ?? null,
            $segmentType,
            [],
            $templateId
        );

        return response()->json([
            'status' => 'queued',
            'message' => 'تم جدولة البث وسيتم التسليم تدريجياً.',
            'broadcast' => new BroadcastResource($broadcast),
        ], 202);
    }

    public function broadcasts(Request $request)
    {
        $user = $request->user();
        abort_unless($user->hasAnyRole(['admin', 'coach']), 403);

        $items = MessageBroadcast::query()
            ->with('sender:id,name')
            ->when(
                $user->hasRole('coach') && ! $user->hasRole('admin'),
                fn ($query) => $query->where('sender_user_id', $user->id)
            )
            ->orderByDesc('id')
            ->paginate(20);

        return BroadcastResource::collection($items);
    }

    public function broadcastShow(Request $request, MessageBroadcast $broadcast)
    {
        $user = $request->user();
        abort_unless($user->hasAnyRole(['admin', 'coach']), 403);
        abort_unless(
            $user->hasRole('admin') || (int) $broadcast->sender_user_id === (int) $user->id,
            403
        );

        $broadcast->load('sender:id,name');

        return new BroadcastResource($broadcast);
    }

    public function broadcastRecipients(Request $request, MessageBroadcast $broadcast)
    {
        $user = $request->user();
        abort_unless($user->hasAnyRole(['admin', 'coach']), 403);
        abort_unless(
            $user->hasRole('admin') || (int) $broadcast->sender_user_id === (int) $user->id,
            403
        );

        $recipients = $broadcast->recipients()
            ->with('recipient:id,name')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->orderBy('id')
            ->paginate(50);

        return BroadcastRecipientResource::collection($recipients);
    }

    private function canMessage(User $sender, User $recipient): bool
    {
        if ($sender->hasRole('admin')) {
            return true;
        }

        if ($sender->hasRole('coach') && $recipient->hasAnyRole(['user', 'client'])) {
            return (int) $recipient->coach_id === (int) $sender->id;
        }

        if ($sender->hasAnyRole(['user', 'client']) && $recipient->hasRole('coach')) {
            return (int) $sender->coach_id === (int) $recipient->id;
        }

        return false;
    }
}
