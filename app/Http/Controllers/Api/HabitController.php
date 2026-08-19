<?php

namespace App\Http\Controllers\Api;

use App\Events\HabitLogRecorded;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\HabitResource;
use App\Models\ChallengeParticipant;
use App\Models\GamificationBadge;
use App\Models\Habit;
use App\Models\HabitLog;
use App\Models\User;
use App\Models\UserBadge;
use App\Models\WeeklyChallenge;
use App\Services\GamificationService;
use App\Services\HabitInsightsService;
use Illuminate\Http\Request;

class HabitController extends Controller
{
    public function today(Request $request)
    {
        $user = $request->user();
        $clientId = (int) $request->query('client_id', $user->id);
        $client = User::findOrFail($clientId);
        abort_unless($this->canAccessClient($user, $client), 403);

        $today = now()->toDateString();
        $weekStart = now()->copy()->startOfWeek(\Carbon\Carbon::SATURDAY)->toDateString();

        $habits = Habit::query()
            ->active()
            ->where('client_user_id', $client->id)
            ->with(['logs' => fn ($query) => $query->whereBetween('logged_on', [$weekStart, $today])])
            ->orderBy('id')
            ->get();

        $insights = app(HabitInsightsService::class)->summarize($habits);
        $gamification = app(GamificationService::class)->leaderboard($client);
        $streak = (int) ($insights['active_streak'] ?? 0);

        return response()->json([
            'date' => $today,
            'client_id' => $client->id,
            'screen' => [
                'title' => 'عاداتي',
                'subtitle' => 'الالتزام اليومي، نتائج تدوم',
                'section_title' => 'عادات اليوم',
                'view_all_label' => 'عرض كل العادات',
                'badges_title' => 'شاراتي',
                'view_all_badges_label' => 'عرض جميع الشارات',
                'challenge_title' => 'تحدي الشهر',
            ],
            'habits' => HabitResource::collection($habits),
            'insights' => array_merge($insights, [
                'weekly_completion_label' => 'الالتزام الأسبوعي',
                'points_label' => 'نقاط التحفيز',
                'streak_label' => $streak > 0 ? "{$streak} أيام متتالية" : 'ابدأ سلسلتك اليوم',
                'streak_days' => $streak,
            ]),
            'weekly_completion' => $insights['weekly_completion'] ?? 0,
            'gamification' => array_merge($gamification, [
                'points_label' => 'نقاط التحفيز',
            ]),
            'active_challenge' => $this->challengePayload($client),
            'badges' => $this->badgesPayload($client),
        ]);
    }

    public function log(Request $request, Habit $habit)
    {
        $validated = $request->validate([
            'logged_on' => 'nullable|date',
            'value' => 'nullable|integer|min:0|max:100000',
            'is_completed' => 'nullable|boolean',
        ]);

        $user = $request->user();
        abort_unless($this->canAccessClient($user, $habit->client), 403);
        if ($user->hasAnyRole(['user', 'client'])) {
            abort_unless((int) $habit->client_user_id === (int) $user->id, 403);
        }

        $loggedOn = $validated['logged_on'] ?? now()->toDateString();
        $value = (int) ($validated['value'] ?? $habit->target_value);
        $isCompleted = (bool) ($validated['is_completed'] ?? ($value >= $habit->target_value));

        $log = HabitLog::updateOrCreate(
            ['habit_id' => $habit->id, 'logged_on' => $loggedOn],
            [
                'user_id' => $user->id,
                'value' => $value,
                'is_completed' => $isCompleted,
            ]
        );

        event(new HabitLogRecorded($habit, $user));

        $weekStart = now()->copy()->startOfWeek(\Carbon\Carbon::SATURDAY)->toDateString();
        $habit->load(['logs' => fn ($query) => $query->whereBetween('logged_on', [$weekStart, now()->toDateString()])]);
        $habits = Habit::query()
            ->active()
            ->where('client_user_id', $habit->client_user_id)
            ->with(['logs' => fn ($query) => $query->whereBetween('logged_on', [$weekStart, now()->toDateString()])])
            ->orderBy('id')
            ->get();

        return response()->json([
            'status' => 'ok',
            'message' => $isCompleted ? 'تم تسجيل العادة.' : 'تم حفظ القيمة.',
            'log' => $log,
            'habit' => new HabitResource($habit),
            'insights' => app(HabitInsightsService::class)->summarize($habits),
            'gamification' => app(GamificationService::class)->leaderboard($user),
        ]);
    }

    private function challengePayload(User $client): ?array
    {
        $activeChallenge = WeeklyChallenge::query()->current()->first();
        if (! $activeChallenge) {
            return null;
        }

        $participant = ChallengeParticipant::query()
            ->where('challenge_id', $activeChallenge->id)
            ->where('user_id', $client->id)
            ->first();

        $progress = (float) ($participant?->progress_value ?? 0);
        $target = (float) $activeChallenge->target_value;
        $daysRemaining = max(0, (int) now()->startOfDay()->diffInDays($activeChallenge->ends_on->copy()->startOfDay(), false));

        return [
            'id' => $activeChallenge->id,
            'title' => $activeChallenge->title,
            'challenge_type' => $activeChallenge->challenge_type,
            'target_value' => $activeChallenge->target_value,
            'progress_value' => $progress,
            'progress_label' => ((int) $progress).'/'.((int) $target),
            'progress_percent' => $target > 0 ? min(100, round(($progress / $target) * 100)) : 0,
            'starts_on' => optional($activeChallenge->starts_on)->toDateString(),
            'ends_on' => optional($activeChallenge->ends_on)->toDateString(),
            'days_remaining' => $daysRemaining,
            'days_remaining_label' => "{$daysRemaining} يوم متبقي",
            'is_completed' => (bool) ($participant?->is_completed ?? false),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function badgesPayload(User $client): array
    {
        app(GamificationService::class)->bootstrapBadges();

        $earned = UserBadge::query()
            ->with('badge')
            ->where('user_id', $client->id)
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
                    'subtitle' => $userBadge
                        ? ($badge->points > 0 ? "{$badge->name} · {$badge->points} نقطة" : $badge->name)
                        : ($badge->description ?: $badge->name),
                ];
            })
            ->values()
            ->all();
    }

    private function canAccessClient(User $currentUser, User $client): bool
    {
        if ($currentUser->hasRole('admin')) {
            return true;
        }
        if ((int) $currentUser->id === (int) $client->id) {
            return true;
        }

        return $currentUser->isCoachOf($client);
    }
}
