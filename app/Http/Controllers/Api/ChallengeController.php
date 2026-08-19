<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChallengeParticipant;
use App\Models\GamificationBadge;
use App\Models\UserBadge;
use App\Models\WeeklyChallenge;
use App\Services\GamificationService;
use Illuminate\Http\Request;

class ChallengeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($user->hasAnyRole(['user', 'client']), 403);

        app(GamificationService::class)->bootstrapBadges();

        $activeChallenge = WeeklyChallenge::query()->current()->first();
        $upcoming = WeeklyChallenge::query()
            ->where('is_active', true)
            ->whereDate('starts_on', '>', now()->toDateString())
            ->orderBy('starts_on')
            ->limit(3)
            ->get();

        $recent = WeeklyChallenge::query()
            ->whereDate('ends_on', '<', now()->toDateString())
            ->orderByDesc('ends_on')
            ->limit(3)
            ->get();

        $gamification = app(GamificationService::class)->leaderboard($user);
        $badgesCount = (int) ($gamification['badges_count'] ?? 0);
        $points = (int) ($gamification['points'] ?? 0);

        return response()->json([
            'date' => now()->toDateString(),
            'screen' => [
                'title' => 'التحديات',
                'subtitle' => 'شارك في التحديات واربح',
                'points_label' => 'نقاط التحفيز',
                'badges_section_title' => 'شاراتك',
                'active_section_title' => 'التحدي النشط',
                'empty_challenge_label' => 'لا يوجد تحدي نشط حالياً.',
                'view_community_label' => 'انضم للمجتمع',
            ],
            'gamification' => [
                'points' => $points,
                'points_label' => 'نقاط التحفيز',
                'badges_count' => $badgesCount,
                'badges_count_label' => $badgesCount.' شارات مكتسبة',
            ],
            'active_challenge' => $this->challengePayload($activeChallenge, $user),
            'upcoming_challenges' => $upcoming->map(fn (WeeklyChallenge $c) => $this->challengePayload($c, $user))->values(),
            'recent_challenges' => $recent->map(fn (WeeklyChallenge $c) => $this->challengePayload($c, $user))->values(),
            'badges' => $this->badgesPayload($user),
        ]);
    }

    public function join(Request $request, WeeklyChallenge $challenge)
    {
        $user = $request->user();
        abort_unless($user->hasAnyRole(['user', 'client']), 403);
        abort_unless($challenge->is_active, 422, 'التحدي غير نشط.');
        abort_unless(
            $challenge->starts_on->lte(now()->startOfDay()) && $challenge->ends_on->gte(now()->startOfDay()),
            422,
            'التحدي خارج الفترة الحالية.'
        );

        $participant = ChallengeParticipant::firstOrCreate(
            ['challenge_id' => $challenge->id, 'user_id' => $user->id],
            ['progress_value' => 0, 'is_completed' => false]
        );

        return response()->json([
            'status' => 'ok',
            'message' => 'تم الانضمام للتحدي.',
            'active_challenge' => $this->challengePayload($challenge->fresh(), $user),
            'participant' => [
                'id' => $participant->id,
                'progress_value' => (float) $participant->progress_value,
                'is_completed' => (bool) $participant->is_completed,
            ],
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function challengePayload(?WeeklyChallenge $challenge, $user): ?array
    {
        if (! $challenge) {
            return null;
        }

        $participant = ChallengeParticipant::query()
            ->where('challenge_id', $challenge->id)
            ->where('user_id', $user->id)
            ->first();

        $progress = (float) ($participant?->progress_value ?? 0);
        $target = (float) $challenge->target_value;
        $progressPercent = $target > 0 ? min(100, round(($progress / $target) * 100)) : 0;
        $daysRemaining = max(0, (int) now()->startOfDay()->diffInDays($challenge->ends_on->copy()->startOfDay(), false));

        return [
            'id' => $challenge->id,
            'title' => $challenge->title,
            'challenge_type' => $challenge->challenge_type,
            'challenge_type_label' => $this->typeLabel($challenge->challenge_type),
            'target_value' => $challenge->target_value,
            'starts_on' => optional($challenge->starts_on)->toDateString(),
            'ends_on' => optional($challenge->ends_on)->toDateString(),
            'ends_on_label' => 'ينتهي '.optional($challenge->ends_on)->format('d/m/Y'),
            'days_remaining' => $daysRemaining,
            'days_remaining_label' => $daysRemaining > 0 ? "{$daysRemaining} يوم متبقي" : 'ينتهي اليوم',
            'progress_value' => $progress,
            'progress_label' => ((int) $progress).'/'.((int) $target),
            'progress_percent' => $progressPercent,
            'is_completed' => (bool) ($participant?->is_completed ?? false),
            'is_participating' => $participant !== null,
            'status_label' => match (true) {
                (bool) ($participant?->is_completed) => 'مكتمل',
                $participant !== null => 'جاري',
                default => 'متاح للانضمام',
            },
            'actions' => [
                'join_url' => url('/api/v1/challenges/'.$challenge->id.'/join'),
                'can_join' => $participant === null
                    && $challenge->is_active
                    && $challenge->starts_on->lte(now()->startOfDay())
                    && $challenge->ends_on->gte(now()->startOfDay()),
                'join_label' => 'انضم للتحدي',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function badgesPayload($user): array
    {
        $earned = UserBadge::query()
            ->with('badge')
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('badge_id');

        return GamificationBadge::query()
            ->orderBy('id')
            ->get()
            ->map(function (GamificationBadge $badge) use ($earned) {
                $userBadge = $earned->get($badge->id);

                return [
                    'id' => $badge->id,
                    'code' => $badge->code,
                    'name' => $badge->name,
                    'description' => $badge->description,
                    'points' => (int) $badge->points,
                    'is_earned' => $userBadge !== null,
                    'is_locked' => $userBadge === null,
                    'awarded_at' => optional($userBadge?->awarded_at)->toIso8601String(),
                    'awarded_on_label' => optional($userBadge?->awarded_at)->format('d/m/Y'),
                    'subtitle' => $userBadge
                        ? ($badge->points > 0 ? "{$badge->name} · {$badge->points} نقطة" : $badge->name)
                        : ($badge->description ?: $badge->name),
                ];
            })
            ->values()
            ->all();
    }

    private function typeLabel(?string $type): string
    {
        return match ($type) {
            'habit_completion' => 'عادات',
            'workout_completion' => 'تمارين',
            'steps' => 'خطوات',
            default => 'تحدي',
        };
    }
}
