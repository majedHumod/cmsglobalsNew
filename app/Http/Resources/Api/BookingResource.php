<?php

namespace App\Http\Resources\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $session = $this->trainingSession;
        $coach = $session?->relationLoaded('user') ? $session->user : $session?->user;
        $displayStatus = $this->displayStatus();
        $statusMeta = $this->statusMeta($displayStatus);
        $date = optional($this->booking_date);
        $time = optional($this->booking_time);
        $isUpcoming = (bool) $this->is_upcoming && ! in_array($this->status, ['cancelled', 'completed'], true);
        $canCancel = $this->canBeCancelled() && $isUpcoming;
        $canReschedule = $canCancel;

        $dateLabel = $date
            ? Carbon::parse($date->toDateString())->locale('ar')->translatedFormat('l d F')
            : null;
        $timeLabel = $time
            ? Carbon::parse($time->format('H:i'))->locale('ar')->translatedFormat('g:i A')
            : null;

        $location = $session?->location
            ?: (($session?->session_type === 'online') ? 'أونلاين' : null);

        return [
            'id' => $this->id,
            'training_session_id' => $this->training_session_id,
            'title' => $session?->title,
            'provider_name' => $coach?->name,
            'coach_name' => $coach?->name,
            'location' => $location,
            'session_type' => $session?->session_type,
            'icon_key' => $this->iconKey($session?->session_type, $session?->title),
            'booking_date' => $date?->toDateString(),
            'booking_time' => $time?->format('H:i'),
            'date_label' => $dateLabel,
            'time_label' => $timeLabel,
            'formatted_datetime' => $this->formatted_booking_datetime,
            'status' => $this->status,
            'display_status' => $displayStatus,
            'status_label' => $statusMeta['label'],
            'status_color' => $statusMeta['color'],
            'payment_status' => $this->payment_status,
            'attendance_status' => $this->attendance_status,
            'payment_amount' => $this->payment_amount !== null ? (float) $this->payment_amount : null,
            'notes' => $this->notes,
            'video_meeting_url' => $this->video_meeting_url
                ?: ($session?->video_meeting_url),
            'can_cancel' => $canCancel,
            'can_reschedule' => $canReschedule,
            'is_upcoming' => $isUpcoming,
            'calendar_url' => route('session-bookings.calendar', $this->resource),
            'cancelled_at' => optional($this->cancelled_at)->toIso8601String(),
            'actions' => [
                [
                    'key' => 'cancel',
                    'label' => 'إلغاء الحجز',
                    'enabled' => $canCancel,
                    'method' => 'POST',
                    'endpoint' => '/api/v1/bookings/'.$this->id.'/cancel',
                ],
                [
                    'key' => 'reschedule',
                    'label' => 'إعادة جدولة',
                    'enabled' => $canReschedule,
                    'method' => 'PUT',
                    'endpoint' => '/api/v1/bookings/'.$this->id.'/reschedule',
                ],
            ],
        ];
    }

    private function displayStatus(): string
    {
        if ($this->status === 'cancelled') {
            return 'cancelled';
        }

        if ($this->status === 'completed' || ! $this->is_upcoming) {
            return 'completed';
        }

        return (string) $this->status;
    }

    /**
     * @return array{label: string, color: string}
     */
    private function statusMeta(string $status): array
    {
        return match ($status) {
            'confirmed' => ['label' => 'مؤكد', 'color' => 'green'],
            'pending' => ['label' => 'في الانتظار', 'color' => 'amber'],
            'completed' => ['label' => 'مكتمل', 'color' => 'grey'],
            'cancelled' => ['label' => 'ملغي', 'color' => 'red'],
            default => ['label' => $status, 'color' => 'grey'],
        };
    }

    private function iconKey(?string $sessionType, ?string $title): string
    {
        $t = mb_strtolower((string) $title);

        if (str_contains($t, 'قياس') || str_contains($t, 'قياسات') || str_contains($t, 'body')) {
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
