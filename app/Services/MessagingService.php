<?php

namespace App\Services;

use App\Events\MessageSent;
use App\Jobs\ProcessMessageBroadcastJob;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\MessageBroadcast;
use App\Models\MessageBroadcastRecipient;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class MessagingService
{
    public function sendMessage(Conversation $conversation, User $sender, string $body): Message
    {
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_user_id' => $sender->id,
            'body' => trim($body),
            'sent_at' => now(),
        ]);

        $conversation->update(['last_message_at' => now()]);
        ConversationParticipant::query()
            ->where('conversation_id', $conversation->id)
            ->where('user_id', $sender->id)
            ->update(['last_read_at' => now()]);

        event(new MessageSent($message->load('conversation')));

        return $message;
    }

    public function findOrCreateDirectConversation(User $firstUser, User $secondUser, ?string $subject = null): Conversation
    {
        $existing = Conversation::query()
            ->whereHas('participants', fn ($query) => $query->where('user_id', $firstUser->id))
            ->whereHas('participants', fn ($query) => $query->where('user_id', $secondUser->id))
            ->withCount('participants')
            ->get()
            ->first(fn ($thread) => (int) $thread->participants_count === 2);

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($firstUser, $secondUser, $subject) {
            $conversation = Conversation::create([
                'created_by_user_id' => $firstUser->id,
                'subject' => $subject ?: null,
                'last_message_at' => now(),
            ]);

            ConversationParticipant::insert([
                [
                    'conversation_id' => $conversation->id,
                    'user_id' => $firstUser->id,
                    'last_read_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'conversation_id' => $conversation->id,
                    'user_id' => $secondUser->id,
                    'last_read_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            return $conversation;
        });
    }

    /**
     * Queue a broadcast for async delivery (preferred path).
     *
     * @param  Collection<int, User>|iterable<int, User>  $recipients
     */
    public function queueBroadcast(
        User $sender,
        iterable $recipients,
        string $body,
        ?string $title = null,
        string $segmentType = 'all_clients',
        array $segmentFilters = [],
        ?int $templateId = null
    ): MessageBroadcast {
        $tenant = TenantService::getTenant();
        if (! $tenant) {
            throw new \RuntimeException('لا يمكن جدولة البث بدون سياق مستأجر.');
        }

        $recipientModels = collect($recipients)->unique('id')->values();

        $broadcast = MessageBroadcast::create([
            'sender_user_id' => $sender->id,
            'template_id' => $templateId,
            'title' => $title,
            'body' => trim($body),
            'segment_type' => $segmentType,
            'segment_filters' => $segmentFilters,
            'recipients_count' => $recipientModels->count(),
            'status' => MessageBroadcast::STATUS_QUEUED,
            'delivered_count' => 0,
            'failed_count' => 0,
            'sent_at' => now(),
        ]);

        $now = now();
        $rows = [];
        foreach ($recipientModels as $recipient) {
            $rows[] = [
                'broadcast_id' => $broadcast->id,
                'recipient_user_id' => $recipient->id,
                'conversation_id' => null,
                'message_id' => null,
                'status' => MessageBroadcastRecipient::STATUS_PENDING,
                'delivered_at' => null,
                'read_at' => null,
                'error_message' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            MessageBroadcastRecipient::insert($chunk);
        }

        ProcessMessageBroadcastJob::dispatch((int) $tenant->id, (int) $broadcast->id);

        return $broadcast->fresh();
    }

    /**
     * Legacy synchronous broadcast (kept for compatibility). Prefer queueBroadcast().
     *
     * @param  array<int, User>|iterable<int, User>  $recipients
     */
    public function broadcast(
        User $sender,
        iterable $recipients,
        string $body,
        ?string $title = null,
        string $segmentType = 'all_clients',
        array $segmentFilters = [],
        ?int $templateId = null
    ): MessageBroadcast {
        return $this->queueBroadcast($sender, $recipients, $body, $title, $segmentType, $segmentFilters, $templateId);
    }

    public function deliverBroadcastRecipient(MessageBroadcastRecipient $row): MessageBroadcastRecipient
    {
        $broadcast = MessageBroadcast::query()->with('template')->findOrFail($row->broadcast_id);
        $sender = User::query()->findOrFail($broadcast->sender_user_id);
        $recipient = User::query()->findOrFail($row->recipient_user_id);

        try {
            $body = $broadcast->body;
            if ($broadcast->template) {
                $body = app(MessageTemplateService::class)->renderTemplate(
                    $broadcast->template,
                    $recipient,
                    $sender
                );
            } elseif (str_contains($body, '{{')) {
                $body = app(MessageTemplateService::class)->render(
                    $body,
                    app(MessageTemplateService::class)->contextFor($recipient, $sender)
                );
            }

            $conversation = $this->findOrCreateDirectConversation($sender, $recipient, $broadcast->title);
            $message = $this->sendMessage($conversation, $sender, $body);

            app(NotificationFeedService::class)->pushToUser(
                (int) $recipient->id,
                'message.broadcast',
                $broadcast->title ?: 'رسالة جماعية',
                'تم استلام رسالة جماعية جديدة.',
                [
                    'broadcast_id' => $broadcast->id,
                    'conversation_id' => $conversation->id,
                    'message_id' => $message->id,
                ],
                true
            );

            $row->update([
                'status' => MessageBroadcastRecipient::STATUS_DELIVERED,
                'conversation_id' => $conversation->id,
                'message_id' => $message->id,
                'delivered_at' => now(),
                'error_message' => null,
            ]);
        } catch (Throwable $e) {
            $row->update([
                'status' => MessageBroadcastRecipient::STATUS_FAILED,
                'error_message' => mb_substr($e->getMessage(), 0, 1000),
            ]);
        }

        $this->refreshBroadcastCounters($broadcast->fresh());

        return $row->fresh();
    }

    public function refreshBroadcastCounters(MessageBroadcast $broadcast): MessageBroadcast
    {
        $delivered = MessageBroadcastRecipient::query()
            ->where('broadcast_id', $broadcast->id)
            ->where('status', MessageBroadcastRecipient::STATUS_DELIVERED)
            ->count();

        $failed = MessageBroadcastRecipient::query()
            ->where('broadcast_id', $broadcast->id)
            ->where('status', MessageBroadcastRecipient::STATUS_FAILED)
            ->count();

        $pending = MessageBroadcastRecipient::query()
            ->where('broadcast_id', $broadcast->id)
            ->where('status', MessageBroadcastRecipient::STATUS_PENDING)
            ->count();

        $status = MessageBroadcast::STATUS_PROCESSING;
        $completedAt = null;

        if ($pending === 0) {
            $completedAt = now();
            $status = match (true) {
                $failed === 0 && $delivered > 0 => MessageBroadcast::STATUS_COMPLETED,
                $delivered === 0 && $failed > 0 => MessageBroadcast::STATUS_FAILED,
                $delivered > 0 && $failed > 0 => MessageBroadcast::STATUS_PARTIAL,
                default => MessageBroadcast::STATUS_COMPLETED,
            };
        }

        $broadcast->update([
            'delivered_count' => $delivered,
            'failed_count' => $failed,
            'status' => $status,
            'completed_at' => $completedAt,
            'started_at' => $broadcast->started_at ?: now(),
        ]);

        return $broadcast->fresh();
    }

    public function unreadCountFor(User $user): int
    {
        return (int) Message::query()
            ->join('conversation_participants as cp', 'cp.conversation_id', '=', 'messages.conversation_id')
            ->where('cp.user_id', $user->id)
            ->where('messages.sender_user_id', '!=', $user->id)
            ->where(function ($query) {
                $query->whereNull('cp.last_read_at')
                    ->orWhereColumn('messages.sent_at', '>', 'cp.last_read_at');
            })
            ->count();
    }

    public function unreadCountForConversation(int $conversationId, int $userId, $lastReadAt = null): int
    {
        return Message::query()
            ->where('conversation_id', $conversationId)
            ->where('sender_user_id', '!=', $userId)
            ->when(
                $lastReadAt,
                fn ($query) => $query->where('sent_at', '>', $lastReadAt),
                fn ($query) => $query
            )
            ->count();
    }
}
