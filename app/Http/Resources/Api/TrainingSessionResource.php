<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainingSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $coach = $this->relationLoaded('user') ? $this->user : $this->user;
        $price = $this->price !== null ? (float) $this->price : 0;
        $isOnline = ($this->session_type === 'online')
            || filled($this->video_meeting_url);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description ?? null,
            'price' => $price,
            'price_label' => $price > 0 ? number_format($price, 0).' ر.س' : 'مجاني',
            'is_free' => $price <= 0,
            'duration_hours' => $this->duration_hours ?? null,
            'duration_label' => $this->duration_hours
                ? ((int) $this->duration_hours).' ساعة'
                : null,
            'session_type' => $this->session_type,
            'location' => $this->location ?: ($isOnline ? 'أونلاين' : null),
            'provider_name' => $coach?->name,
            'coach_name' => $coach?->name,
            'coach_id' => $this->user_id,
            'video_meeting_url' => $this->video_meeting_url ?? null,
            'icon_key' => $this->iconKey($this->session_type, $this->title),
            'capacity' => $this->capacity,
            'slots_endpoint' => '/api/v1/bookings/sessions/'.$this->id.'/slots',
        ];
    }

    private function iconKey(?string $sessionType, ?string $title): string
    {
        $t = mb_strtolower((string) $title);

        if (str_contains($t, 'قياس') || str_contains($t, 'قياسات')) {
            return 'scale';
        }

        if (str_contains($t, 'تغذية') || str_contains($t, 'nutrition')) {
            return 'nutrition';
        }

        if ($sessionType === 'online' || str_contains($t, 'أونلاين') || str_contains($t, 'online')) {
            return 'online';
        }

        return 'training';
    }
}
