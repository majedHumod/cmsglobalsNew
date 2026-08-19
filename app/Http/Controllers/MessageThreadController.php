<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\MessageTemplate;
use App\Models\User;
use App\Services\NotificationFeedService;
use App\Services\MessagingService;
use Illuminate\Http\Request;

class MessageThreadController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|coach|user|client']);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $conversations = Conversation::query()
            ->whereHas('participants', fn ($query) => $query->where('user_id', $user->id))
            ->with([
                'participants.user:id,name',
                'messages' => fn ($query) => $query->latest('sent_at')->limit(1),
            ])
            ->orderByDesc('last_message_at')
            ->paginate(20);

        $participantState = ConversationParticipant::query()
            ->whereIn('conversation_id', $conversations->pluck('id'))
            ->where('user_id', $user->id)
            ->pluck('last_read_at', 'conversation_id');

        $conversations->getCollection()->transform(function (Conversation $conversation) use ($participantState) {
            $lastRead = $participantState->get($conversation->id);
            $unreadCount = $conversation->messages()
                ->when($lastRead, fn ($query) => $query->where('sent_at', '>', $lastRead))
                ->when(! $lastRead, fn ($query) => $query)
                ->where('sender_user_id', '!=', auth()->id())
                ->count();
            $conversation->setAttribute('unread_count', $unreadCount);
            return $conversation;
        });

        $potentialUsers = User::query()
            ->where('id', '!=', $user->id)
            ->when($user->hasRole('coach'), fn ($query) => $query->clients()->where('coach_id', $user->id))
            ->when($user->hasAnyRole(['user', 'client']), fn ($query) => $query->where('id', $user->coach_id))
            ->orderBy('name')
            ->get(['id', 'name']);

        $templates = MessageTemplate::query()
            ->where('is_active', true)
            ->where(function ($query) use ($user) {
                $query->where('created_by_user_id', $user->id)->orWhere('category', 'global');
            })
            ->orderBy('name')
            ->get();

        return view('messages.index', compact('conversations', 'potentialUsers', 'templates'));
    }

    public function show(Request $request, Conversation $conversation)
    {
        $user = $request->user();
        abort_unless($this->isParticipant($conversation, $user->id), 403);

        $conversation->load([
            'participants.user:id,name',
            'messages.sender:id,name',
        ]);

        ConversationParticipant::query()
            ->where('conversation_id', $conversation->id)
            ->where('user_id', $user->id)
            ->update(['last_read_at' => now()]);

        $templates = MessageTemplate::query()
            ->where('is_active', true)
            ->where(function ($query) use ($user) {
                $query->where('created_by_user_id', $user->id)->orWhere('category', 'global');
            })
            ->orderBy('name')
            ->get();

        return view('messages.show', compact('conversation', 'templates'));
    }

    public function store(Request $request, MessagingService $messagingService)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $currentUser = $request->user();
        $targetUser = User::findOrFail((int) $validated['user_id']);
        abort_unless($this->canStartThread($currentUser, $targetUser), 403);

        $conversation = $messagingService->findOrCreateDirectConversation($currentUser, $targetUser, $validated['subject'] ?? null);
        $messagingService->sendMessage($conversation, $currentUser, $validated['message']);

        app(NotificationFeedService::class)->pushToUser(
            $targetUser->id,
            'message.received',
            'رسالة جديدة',
            'لديك رسالة جديدة من ' . $currentUser->name,
            ['conversation_id' => $conversation->id]
        );

        return redirect()->route('messages.show', $conversation)->with('success', 'تم إرسال الرسالة بنجاح.');
    }

    public function send(Request $request, Conversation $conversation, MessagingService $messagingService)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $sender = $request->user();
        abort_unless($this->isParticipant($conversation, $sender->id), 403);

        $messagingService->sendMessage($conversation, $sender, $validated['message']);

        $participantIds = $conversation->participants()->pluck('user_id')->all();
        $recipientIds = array_values(array_filter($participantIds, fn ($id) => (int) $id !== (int) $sender->id));

        app(NotificationFeedService::class)->pushToUsers(
            $recipientIds,
            'message.received',
            'رسالة جديدة',
            'لديك رسالة جديدة من ' . $sender->name,
            ['conversation_id' => $conversation->id]
        );

        return redirect()->route('messages.show', $conversation)->with('success', 'تم إرسال الرسالة.');
    }

    public function broadcast(Request $request, MessagingService $messagingService, \App\Services\BroadcastSegmentService $segments)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'body' => 'required|string|max:5000',
            'segment_type' => 'required|in:all_clients,coach_clients,inactive_clients,membership_expiring',
        ]);

        $sender = $request->user();
        abort_unless($sender->hasAnyRole(['admin', 'coach']), 403);

        $segmentType = $validated['segment_type'];
        if ($sender->hasRole('coach') && ! $sender->hasRole('admin')) {
            $segmentType = 'coach_clients';
        }

        $recipients = $segments->resolveRecipients($sender, $segmentType);
        if ($recipients->isEmpty()) {
            return redirect()->route('messages.index')->with('error', 'لا يوجد مستلمون في هذه الشريحة.');
        }

        $broadcast = $messagingService->queueBroadcast(
            $sender,
            $recipients,
            $validated['body'],
            $validated['title'] ?? null,
            $segmentType,
            []
        );

        return redirect()->route('messages.index')
            ->with('success', 'تمت جدولة البث #'.$broadcast->id.' إلى '.$broadcast->recipients_count.' عميل (التسليم في الخلفية).');
    }

    public function templatesStore(Request $request)
    {
        abort_unless($request->user()->hasAnyRole(['admin', 'coach']), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'category' => 'nullable|string|max:80',
            'body' => 'required|string|max:5000',
        ]);

        MessageTemplate::create([
            'created_by_user_id' => $request->user()->id,
            'name' => $validated['name'],
            'category' => $validated['category'] ?: 'coach',
            'body' => $validated['body'],
            'is_active' => true,
        ]);

        return redirect()->route('messages.index')->with('success', 'تم حفظ القالب.');
    }

    private function isParticipant(Conversation $conversation, int $userId): bool
    {
        return $conversation->participants()->where('user_id', $userId)->exists();
    }

    private function canStartThread(User $currentUser, User $targetUser): bool
    {
        if ($currentUser->hasRole('admin')) {
            return true;
        }

        if ($currentUser->id === $targetUser->id) {
            return false;
        }

        if ($currentUser->hasRole('coach') && $targetUser->hasAnyRole(['user', 'client'])) {
            return (int) $targetUser->coach_id === (int) $currentUser->id;
        }

        if ($currentUser->hasAnyRole(['user', 'client']) && $targetUser->hasRole('coach')) {
            return (int) $currentUser->coach_id === (int) $targetUser->id;
        }

        return false;
    }
}
