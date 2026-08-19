<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageThreadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $latest = null;
        if ($this->relationLoaded('messages') && $this->messages->isNotEmpty()) {
            $latest = $this->messages->sortByDesc(fn ($m) => optional($m->sent_at)->timestamp)->first();
        }

        $payload = [
            'id' => $this->id,
            'subject' => $this->subject,
            'channel' => 'dm',
            'screen' => 'messages',
            'last_message_at' => optional($this->last_message_at)->toIso8601String(),
            'participants' => $this->participants->map(fn ($participant) => [
                'id' => $participant->user_id,
                'name' => optional($participant->user)->name,
                'last_read_at' => optional($participant->last_read_at)->toIso8601String(),
            ]),
            'latest_message' => $latest ? new MessageResource($latest) : null,
            'unread_count' => (int) ($this->unread_count ?? 0),
        ];

        if ($this->include_messages && $this->relationLoaded('messages')) {
            $payload['messages'] = MessageResource::collection(
                $this->messages->sortBy(fn ($m) => optional($m->sent_at)->timestamp)->values()
            );
        }

        return $payload;
    }
}
