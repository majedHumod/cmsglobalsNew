<?php

namespace App\Services;

use App\Models\CoachAvailability;
use App\Models\SessionBooking;
use App\Models\TrainingSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BookingSlotService
{
    /**
     * @return Collection<int, array{time: string, label: string, available: bool}>
     */
    public function slotsForSession(TrainingSession $session, string $date, ?User $client = null): Collection
    {
        $coachId = $session->user_id;
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;

        $availabilities = CoachAvailability::query()
            ->where('user_id', $coachId)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get();

        if ($availabilities->isEmpty()) {
            return collect();
        }

        $durationHours = max(1, (int) $session->duration_hours);
        $slots = collect();

        foreach ($availabilities as $availability) {
            $slotDuration = max(15, (int) $availability->slot_duration_minutes);
            $buffer = max(0, (int) $availability->buffer_minutes);
            $windowStart = Carbon::parse($date . ' ' . $availability->start_time);
            $windowEnd = Carbon::parse($date . ' ' . $availability->end_time);
            $cursor = $windowStart->copy();

            while ($cursor->copy()->addHours($durationHours)->lte($windowEnd)) {
                $time = $cursor->format('H:i');
                $available = $session->isAvailableAt($date, $time);

                $slots->push([
                    'time' => $time,
                    'label' => $time,
                    'available' => $available,
                ]);

                $cursor->addMinutes($slotDuration + $buffer);
            }
        }

        return $slots
            ->unique('time')
            ->sortBy('time')
            ->values();
    }

    public function createBooking(TrainingSession $session, User $user, string $date, string $time, ?string $notes = null): SessionBooking
    {
        abort_unless($session->matchesAudience($user), 403);

        if (! $session->isAvailableAt($date, $time)) {
            abort(422, 'هذا الموعد غير متاح.');
        }

        return SessionBooking::create([
            'training_session_id' => $session->id,
            'user_id' => $user->id,
            'booking_date' => $date,
            'booking_time' => $time,
            'video_meeting_url' => $session->video_meeting_url,
            'payment_amount' => $session->price,
            'notes' => $notes,
            'status' => (float) $session->price > 0 ? 'pending' : 'confirmed',
            'payment_status' => (float) $session->price > 0 ? 'pending' : 'paid',
            'attendance_status' => 'scheduled',
        ]);
    }
}
