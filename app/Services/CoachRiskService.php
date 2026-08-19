<?php

namespace App\Services;

use App\Models\Habit;
use App\Models\ProgressCheckIn;
use App\Models\User;
use App\Filament\Resources\ClientResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CoachRiskService
{
    public const CHECKIN_OVERDUE_DAYS = 14;

    public const LOW_COMPLIANCE_THRESHOLD = 50;

    public const LOW_HABITS_THRESHOLD = 40;

    public const LOW_NUTRITION_THRESHOLD = 40;

    public const EXPIRING_SOON_DAYS = 7;

    public function __construct(
        protected WorkoutScheduleService $workoutScheduleService,
        protected HabitInsightsService $habitInsightsService,
        protected MealLogService $mealLogService,
    ) {}

    /**
     * Admin can view all clients, or narrow to one coach.
     * Coach (non-admin) is always scoped to their own clients.
     */
    public function resolveCoachScope(User $viewer, ?int $coachId = null): ?int
    {
        if ($viewer->hasRole('admin')) {
            return $coachId ?: null;
        }

        if ($viewer->hasRole('coach')) {
            return (int) $viewer->id;
        }

        return null;
    }

    public function clientsQuery(User $viewer, ?string $filter = null, ?int $coachId = null): Builder
    {
        $scopedCoachId = $this->resolveCoachScope($viewer, $coachId);

        $query = User::query()
            ->clients()
            ->with([
                'coach:id,name',
                'clientProfile',
                'progressCheckIns' => fn ($q) => $q->latest('checked_in_at')->limit(1),
            ])
            ->when($scopedCoachId, fn (Builder $q) => $q->where('coach_id', $scopedCoachId));

        return match ($filter) {
            'checkin_overdue' => $query->whereDoesntHave(
                'progressCheckIns',
                fn (Builder $q) => $q->where('checked_in_at', '>=', now()->subDays(self::CHECKIN_OVERDUE_DAYS))
            ),
            'low_compliance' => $query->whereIn('id', $this->lowComplianceClientIds($viewer, $coachId)),
            'low_nutrition' => $query->whereIn('id', $this->lowNutritionClientIds($viewer, $coachId)),
            'expiring' => $query->whereNotNull('membership_expires_at')
                ->where('membership_expires_at', '<=', now()->addDays(self::EXPIRING_SOON_DAYS))
                ->where('membership_expires_at', '>=', now()),
            default => $query,
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function assessClient(User $client): array
    {
        $reasons = [];
        $score = 0;

        $lastCheckIn = $client->relationLoaded('progressCheckIns')
            ? $client->progressCheckIns->first()
            : ProgressCheckIn::query()->where('user_id', $client->id)->latest('checked_in_at')->first();

        if (! $lastCheckIn || $lastCheckIn->checked_in_at < now()->subDays(self::CHECKIN_OVERDUE_DAYS)) {
            $reasons[] = 'checkin_overdue';
            $score += 35;
        }

        $workoutRate = $this->workoutScheduleService->complianceRateForClient($client);
        if ($workoutRate < self::LOW_COMPLIANCE_THRESHOLD) {
            $reasons[] = 'low_compliance';
            $score += 30;
        }

        $habits = Habit::query()->active()->where('client_user_id', $client->id)->get();
        $habitRate = (float) ($this->habitInsightsService->summarize($habits)['weekly_completion'] ?? 0);
        if ($habits->isNotEmpty() && $habitRate < self::LOW_HABITS_THRESHOLD) {
            $reasons[] = 'low_habits';
            $score += 20;
        }

        $nutritionRate = $this->mealLogService->weeklyAdherenceRate($client);
        if ($nutritionRate < self::LOW_NUTRITION_THRESHOLD) {
            $reasons[] = 'low_nutrition';
            $score += 15;
        }

        if ($client->membership_expires_at
            && $client->membership_expires_at->isFuture()
            && $client->membership_expires_at->lte(now()->addDays(self::EXPIRING_SOON_DAYS))) {
            $reasons[] = 'expiring_soon';
            $score += 15;
        }

        $client->loadMissing('coach:id,name');

        return [
            'user_id' => $client->id,
            'name' => $client->name,
            'email' => $client->email,
            'coach_id' => $client->coach_id,
            'coach_name' => $client->coach?->name ?? 'غير معيّن',
            'risk_score' => min(100, $score),
            'risk_reasons' => $reasons,
            'priority' => $this->priorityFromScore($score),
            'workout_completion_rate' => round((float) $workoutRate, 0),
            'habit_weekly_completion' => round((float) $habitRate, 0),
            'nutrition_adherence' => round((float) $nutritionRate, 0),
            'last_check_in_at' => $lastCheckIn?->checked_in_at?->toIso8601String(),
            'last_check_in_label' => $lastCheckIn?->checked_in_at?->format('d/m/Y') ?? 'لا يوجد',
            'membership_expires_at' => $client->membership_expires_at?->toDateString(),
            'membership_expires_label' => $client->membership_expires_at?->format('d/m/Y') ?? '—',
            'profile_url' => rescue(
                fn () => ClientResource::getUrl('view', ['record' => $client]),
                route('coach.clients.show', $client),
                report: false
            ),
            'edit_url' => rescue(
                fn () => ClientResource::getUrl('edit', ['record' => $client]),
                route('coach.clients.show', $client),
                report: false
            ),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function atRiskClients(User $viewer, int $limit = 20, ?string $filter = null, ?int $coachId = null): Collection
    {
        return $this->clientsQuery($viewer, $filter, $coachId)
            ->orderBy('name')
            ->get()
            ->map(fn (User $client) => $this->assessClient($client))
            ->filter(fn (array $assessment) => $assessment['risk_score'] > 0)
            ->sortByDesc('risk_score')
            ->take($limit)
            ->values();
    }

    public function summaryFor(User $viewer, ?int $coachId = null): array
    {
        $scopedCoachId = $this->resolveCoachScope($viewer, $coachId);

        $baseQuery = User::query()
            ->clients()
            ->when($scopedCoachId, fn (Builder $q) => $q->where('coach_id', $scopedCoachId));

        $clientIds = (clone $baseQuery)->pluck('id');

        return [
            'clients' => $clientIds->count(),
            'clientsNeedingCheckIn' => (clone $baseQuery)
                ->whereDoesntHave(
                    'progressCheckIns',
                    fn (Builder $q) => $q->where('checked_in_at', '>=', now()->subDays(self::CHECKIN_OVERDUE_DAYS))
                )
                ->count(),
            'clientsLowWorkoutCompliance' => $this->lowComplianceClientIds($viewer, $coachId)->count(),
            'clientsLowNutrition' => $this->lowNutritionClientIds($viewer, $coachId)->count(),
            'clientsExpiringSoon' => (clone $baseQuery)
                ->whereNotNull('membership_expires_at')
                ->where('membership_expires_at', '<=', now()->addDays(self::EXPIRING_SOON_DAYS))
                ->where('membership_expires_at', '>=', now())
                ->count(),
            'atRiskCount' => $this->atRiskClients($viewer, 100, null, $coachId)->count(),
            'workout_completion_rate' => $this->workoutScheduleService->complianceRateForClients($clientIds),
            'unassigned_clients' => User::query()->clients()->whereNull('coach_id')->count(),
        ];
    }

    public function isAtRisk(User $client): bool
    {
        return $this->assessClient($client)['risk_score'] > 0;
    }

    public function isHighRisk(User $client): bool
    {
        $assessment = $this->assessClient($client);

        return in_array('checkin_overdue', $assessment['risk_reasons'], true)
            && in_array('low_compliance', $assessment['risk_reasons'], true);
    }

    /**
     * @return Collection<int, int>
     */
    private function lowNutritionClientIds(User $viewer, ?int $coachId = null): Collection
    {
        $scopedCoachId = $this->resolveCoachScope($viewer, $coachId);

        return User::query()
            ->clients()
            ->when($scopedCoachId, fn (Builder $q) => $q->where('coach_id', $scopedCoachId))
            ->get()
            ->filter(fn (User $client) => $this->mealLogService->weeklyAdherenceRate($client) < self::LOW_NUTRITION_THRESHOLD)
            ->pluck('id');
    }

    /**
     * @return Collection<int, int>
     */
    private function lowComplianceClientIds(User $viewer, ?int $coachId = null): Collection
    {
        $scopedCoachId = $this->resolveCoachScope($viewer, $coachId);

        return User::query()
            ->clients()
            ->when($scopedCoachId, fn (Builder $q) => $q->where('coach_id', $scopedCoachId))
            ->get()
            ->filter(fn (User $client) => $this->workoutScheduleService->complianceRateForClient($client) < self::LOW_COMPLIANCE_THRESHOLD)
            ->pluck('id');
    }

    private function priorityFromScore(int $score): string
    {
        return match (true) {
            $score >= 60 => 'high',
            $score >= 30 => 'medium',
            default => 'low',
        };
    }
}
