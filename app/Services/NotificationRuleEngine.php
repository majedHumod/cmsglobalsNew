<?php

namespace App\Services;

use App\Models\Habit;
use App\Models\NotificationFeed;
use App\Models\ProgressCheckIn;
use App\Models\SessionBooking;
use App\Models\User;
use App\Models\UserMembership;

class NotificationRuleEngine
{
    public function processBookingStatus(SessionBooking $booking, string $action): void
    {
        $type = match ($action) {
            'created' => 'booking.created',
            'confirmed' => 'booking.confirmed',
            'cancelled' => 'booking.cancelled',
            'rescheduled' => 'booking.rescheduled',
            'updated' => 'booking.updated',
            default => 'booking.status_updated',
        };

        $title = match ($action) {
            'created' => 'حجز جلسة جديد',
            'confirmed' => 'تم تأكيد الحجز',
            'cancelled' => 'تم إلغاء الحجز',
            'rescheduled' => 'إعادة جدولة حجز',
            default => 'تحديث حالة الحجز',
        };

        app(NotificationFeedService::class)->pushToUsers(
            array_filter([$booking->user_id, optional($booking->trainingSession)->user_id]),
            $type,
            $title,
            'الحجز رقم #' . $booking->id,
            ['booking_id' => $booking->id, 'action' => $action]
        );
    }

    public function processCheckIn(ProgressCheckIn $checkIn): void
    {
        app(NotificationFeedService::class)->pushToUsers(
            array_filter([$checkIn->user_id, $checkIn->coach_id]),
            'checkin.submitted',
            'تحديث متابعة جديد',
            'تم إرسال Check-in جديد.',
            ['check_in_id' => $checkIn->id]
        );
    }

    public function processMembership(UserMembership $membership): void
    {
        $expiryDateText = $membership->expires_at ? $membership->expires_at->format('Y-m-d') : '-';
        app(NotificationFeedService::class)->pushToUser(
            $membership->user_id,
            'membership.activated',
            'تم تفعيل الاشتراك',
            'تم تفعيل عضويتك حتى ' . $expiryDateText,
            ['membership_id' => $membership->id]
        );
    }

    public function processHabitLog(Habit $habit, User $actor): void
    {
        if ((int) $habit->client_user_id === (int) $actor->id) {
            return;
        }

        app(NotificationFeedService::class)->pushToUser(
            $habit->client_user_id,
            'habit.coach_updated',
            'تحديث عادة',
            'تم تحديث عادة بواسطة المدرب.',
            ['habit_id' => $habit->id]
        );
    }

    public function evaluateClientSignals(User $client): void
    {
        $today = now()->toDateString();
        $lastCheckIn = ProgressCheckIn::query()->where('user_id', $client->id)->latest('checked_in_at')->first();

        if (! $lastCheckIn || $lastCheckIn->checked_in_at < now()->subDays(14)) {
            $this->pushOnceDaily($client->id, 'checkin.missing_14', 'تذكير Check-in', 'لم يتم إرسال Check-in منذ 14 يوم.');
        } elseif ($lastCheckIn->checked_in_at < now()->subDays(7)) {
            $this->pushOnceDaily($client->id, 'checkin.missing_7', 'تذكير Check-in', 'لم يتم إرسال Check-in منذ 7 أيام.');
        }

        if ($client->membership_expires_at) {
            $days = now()->startOfDay()->diffInDays($client->membership_expires_at->startOfDay(), false);
            if (in_array($days, [7, 3, 0], true)) {
                $renewUrl = $this->renewUrlFor($client);
                $this->pushOnceDaily(
                    $client->id,
                    'membership.expiring_' . $days,
                    'اقتراب انتهاء العضوية',
                    'العضوية تنتهي خلال ' . $days . ' يوم.',
                    array_filter(['renew_url' => $renewUrl, 'days_remaining' => $days])
                );
            }
        }

        $activeHabits = Habit::query()->active()->where('client_user_id', $client->id)->pluck('id');
        if ($activeHabits->isNotEmpty()) {
            $loggedCount = \App\Models\HabitLog::query()
                ->whereIn('habit_id', $activeHabits)
                ->whereBetween('logged_on', [now()->subDays(2)->toDateString(), $today])
                ->count();
            if ($loggedCount === 0) {
                $this->pushOnceDaily($client->id, 'habit.missed_streak', 'تذكير العادات', 'لم يتم تسجيل أي عادة خلال آخر يومين.');
            }
        }

        $this->evaluateCoachClientAtRisk($client);
    }

    public function evaluateCoachClientAtRisk(User $client): void
    {
        if (! $client->coach_id) {
            return;
        }

        $coachRiskService = app(CoachRiskService::class);
        if (! $coachRiskService->isHighRisk($client)) {
            return;
        }

        $this->pushOnceDaily(
            $client->coach_id,
            'coach.client_at_risk',
            'عميل يحتاج متابعة',
            'العميل ' . $client->name . ' متأخر في Check-in ومنخفض الالتزام بالتمارين.',
            [
                'client_id' => $client->id,
                'workspace_url' => route('coach.workspace', ['filter' => 'low_compliance']),
            ]
        );
    }

    private function renewUrlFor(User $client): ?string
    {
        $membership = UserMembership::query()
            ->where('user_id', $client->id)
            ->where('is_active', true)
            ->latest('expires_at')
            ->first();

        if (! $membership) {
            return route('subscription-plans.public');
        }

        return route('subscription-plans.renew', $membership);
    }

    private function pushOnceDaily(int $userId, string $type, string $title, string $body, array $payload = []): void
    {
        $exists = NotificationFeed::query()
            ->where('user_id', $userId)
            ->where('type', $type)
            ->whereDate('created_at', now()->toDateString())
            ->exists();

        if (! $exists) {
            app(NotificationFeedService::class)->pushToUser($userId, $type, $title, $body, $payload);
        }
    }
}
