<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BroadcastRecipientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'broadcast_id' => $this->broadcast_id,
            'recipient_user_id' => $this->recipient_user_id,
            'recipient_name' => optional($this->recipient)->name,
            'conversation_id' => $this->conversation_id,
            'message_id' => $this->message_id,
            'status' => $this->status,
            'delivered_at' => optional($this->delivered_at)->toIso8601String(),
            'read_at' => optional($this->read_at)->toIso8601String(),
            'error_message' => $this->error_message,
        ];
    }
}
