<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BroadcastResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'segment_type' => $this->segment_type,
            'status' => $this->status,
            'channel' => 'broadcast',
            'screen' => 'messages',
            'recipients_count' => (int) $this->recipients_count,
            'delivered_count' => (int) $this->delivered_count,
            'failed_count' => (int) $this->failed_count,
            'pending_count' => max(
                0,
                (int) $this->recipients_count - (int) $this->delivered_count - (int) $this->failed_count
            ),
            'progress_percent' => $this->progressPercent(),
            'sent_at' => optional($this->sent_at)->toIso8601String(),
            'started_at' => optional($this->started_at)->toIso8601String(),
            'completed_at' => optional($this->completed_at)->toIso8601String(),
            'error_message' => $this->error_message,
            'sender' => $this->relationLoaded('sender') ? [
                'id' => $this->sender?->id,
                'name' => $this->sender?->name,
            ] : null,
        ];
    }
}
