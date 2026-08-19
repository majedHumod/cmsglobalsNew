<?php

namespace App\Http\Resources\Api;

use App\Services\Communication\CommunicationCatalog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $type = (string) $this->type;
        $payload = is_array($this->payload) ? $this->payload : [];
        $presentation = app(CommunicationCatalog::class)->presentNotification($type, $payload);

        return [
            'id' => $this->id,
            'type' => $type,
            'title' => $this->title,
            'body' => $this->body,
            'payload' => $payload,
            'category' => $presentation['category'],
            'channel' => $presentation['channel'],
            'screen' => $presentation['screen'],
            'priority' => $presentation['priority'],
            'label_ar' => $presentation['label_ar'],
            'action' => $presentation['action'],
            'is_read' => filled($this->read_at),
            'read_at' => optional($this->read_at)->toIso8601String(),
            'created_at' => optional($this->created_at)->toIso8601String(),
        ];
    }
}
