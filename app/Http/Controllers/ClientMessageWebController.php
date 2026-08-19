<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\User;
use App\Services\MessagingService;
use App\Services\NotificationFeedService;
use Illuminate\Http\Request;

class ClientMessageWebController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'trainee']);
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

        $coach = $user->coach_id ? User::find($user->coach_id) : null;

        return view('client.messages.index', compact('conversations', 'coach'));
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

        return view('client.messages.show', compact('conversation'));
    }

    public function store(Request $request, MessagingService $messagingService, NotificationFeedService $notificationFeedService)
    {
        $user = $request->user();
        abort_unless($user->coach_id, 403);
        $coach = User::findOrFail((int) $user->coach_id);

        $validated = $request->validate([
            'message' => 'required|string|max:5000',
            'subject' => 'nullable|string|max:255',
        ]);

        $conversation = $messagingService->findOrCreateDirectConversation($user, $coach, $validated['subject'] ?? null);
        $messagingService->sendMessage($conversation, $user, $validated['message']);

        $notificationFeedService->pushToUser(
            $coach->id,
            'message.received',
            'رسالة جديدة',
            'رسالة جديدة من ' . $user->name,
            ['conversation_id' => $conversation->id]
        );

        return redirect()->route('client.messages.show', $conversation)->with('success', 'تم إرسال الرسالة.');
    }

    public function send(Request $request, Conversation $conversation, MessagingService $messagingService, NotificationFeedService $notificationFeedService)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $sender = $request->user();
        abort_unless($this->isParticipant($conversation, $sender->id), 403);

        $messagingService->sendMessage($conversation, $sender, $validated['message']);

        $recipientIds = $conversation->participants()
            ->where('user_id', '!=', $sender->id)
            ->pluck('user_id')
            ->all();

        $notificationFeedService->pushToUsers(
            $recipientIds,
            'message.received',
            'رسالة جديدة',
            'رسالة جديدة من ' . $sender->name,
            ['conversation_id' => $conversation->id]
        );

        return redirect()->route('client.messages.show', $conversation)->with('success', 'تم إرسال الرسالة.');
    }

    private function isParticipant(Conversation $conversation, int $userId): bool
    {
        return $conversation->participants()->where('user_id', $userId)->exists();
    }
}
