<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public Message $message)
    {
    }

    public function broadcastOn(): array
    {
        $channels = [new Channel('conversation.' . $this->message->conversation_id)];

        $participantIds = $this->message->conversation->participants()->pluck('user_id')->all();
        foreach ($participantIds as $participantId) {
            $channels[] = new PrivateChannel('user.' . $participantId . '.messages');
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_user_id' => $this->message->sender_user_id,
            'body' => $this->message->body,
            'sent_at' => optional($this->message->sent_at)->toIso8601String(),
        ];
    }
}
