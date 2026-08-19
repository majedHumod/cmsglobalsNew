<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'sender_user_id' => $this->sender_user_id,
            'sender_name' => optional($this->sender)->name,
            'body' => $this->body,
            'channel' => 'dm',
            'sent_at' => optional($this->sent_at)->toIso8601String(),
        ];
    }
}
