<?php

namespace App\Services;

use App\Models\ChallengeParticipant;
use App\Models\GamificationBadge;
use App\Models\Habit;
use App\Models\HabitLog;
use App\Models\ProgressCheckIn;
use App\Models\User;
use App\Models\UserBadge;
use App\Models\WeeklyChallenge;
use Carbon\Carbon;

class GamificationService
{
    public function bootstrapBadges(): void
    {
        $defaults = [
            ['code' => 'streak_5', 'name' => '5 أيام متتالية', 'description' => 'سلسلة التزام لمدة 5 أيام', 'points' => 40],
            ['code' => 'inspiring', 'name' => 'ملهم', 'description' => 'ملهم · 100 نقطة', 'points' => 100],
            ['code' => 'achiever', 'name' => 'منجز', 'description' => 'منجز · 50 تمرين', 'points' => 50],
            ['code' => 'disciplined', 'name' => 'منضبط', 'description' => 'منضبط · 7 أيام', 'points' => 70],
            ['code' => 'streak_30', 'name' => 'سلسلة 30 يوم', 'description' => '30 يوم متتالية', 'points' => 150],
            ['code' => 'habit_streak_7', 'name' => 'مواظب 7 أيام', 'description' => 'إنجاز سلسلة عادات لمدة 7 أيام', 'points' => 50],
            ['code' => 'checkin_champion', 'name' => 'بطل المتابعة', 'description' => 'إرسال 4 Check-ins خلال 30 يوم', 'points' => 70],
            ['code' => 'challenge_winner', 'name' => 'فائز التحدي', 'description' => 'إكمال تحدي أسبوعي نشط', 'points' => 100],
        ];

        foreach ($defaults as $badge) {
            GamificationBadge::firstOrCreate(['code' => $badge['code']], $badge);
        }
    }

    public function evaluateHabitProgress(Habit $habit): void
    {
        $this->bootstrapBadges();

        $clientId = (int) $habit->client_user_id;
        $completedDates = HabitLog::query()
            ->where('habit_id', $habit->id)
            ->where('is_completed', true)
            ->orderBy('logged_on')
            ->pluck('logged_on')
            ->map(fn ($date) => \Illuminate\Support\Carbon::parse($date)->toDateString())
            ->all();

        $streak = $this->currentStreak($completedDates);
        if ($streak >= 7) {
            $this->awardBadge($clientId, 'habit_streak_7', ['streak' => $streak]);
        }

        $challenge = WeeklyChallenge::query()->current()->where('challenge_type', 'habit_completion')->first();
        if ($challenge) {
            $progress = HabitLog::query()
                ->where('habit_id', $habit->id)
                ->whereBetween('logged_on', [$challenge->starts_on->toDateString(), $challenge->ends_on->toDateString()])
                ->where('is_completed', true)
                ->count();

            $participant = ChallengeParticipant::updateOrCreate(
                ['challenge_id' => $challenge->id, 'user_id' => $clientId],
                ['progress_value' => $progress, 'is_completed' => $progress >= $challenge->target_value]
            );

            if ($participant->is_completed) {
                $this->awardBadge($clientId, 'challenge_winner', ['challenge_id' => $challenge->id]);
            }
        }
    }

    public function evaluateCheckInProgress(ProgressCheckIn $checkIn): void
    {
        $this->bootstrapBadges();

        $count = ProgressCheckIn::query()
            ->where('user_id', $checkIn->user_id)
            ->where('checked_in_at', '>=', now()->subDays(30))
            ->count();

        if ($count >= 4) {
            $this->awardBadge((int) $checkIn->user_id, 'checkin_champion', ['checkins_30_days' => $count]);
        }
    }

    public function leaderboard(User $user): array
    {
        $points = UserBadge::query()
            ->with('badge')
            ->where('user_id', $user->id)
            ->get()
            ->sum(fn ($badge) => (int) optional($badge->badge)->points);

        return [
            'points' => $points,
            'badges_count' => UserBadge::query()->where('user_id', $user->id)->count(),
        ];
    }

    private function awardBadge(int $userId, string $badgeCode, array $meta = []): void
    {
        $badge = GamificationBadge::query()->where('code', $badgeCode)->first();
        if (! $badge) {
            return;
        }

        UserBadge::firstOrCreate(
            ['user_id' => $userId, 'badge_id' => $badge->id],
            ['awarded_at' => now(), 'meta' => $meta]
        );
    }

    /**
     * @param array<int, string> $dates
     */
    private function currentStreak(array $dates): int
    {
        if ($dates === []) {
            return 0;
        }

        $set = array_flip($dates);
        $cursor = now()->toDateString();
        $streak = 0;
        while (isset($set[$cursor])) {
            $streak++;
            $cursor = Carbon::parse($cursor)->subDay()->toDateString();
        }

        return $streak;
    }
}
