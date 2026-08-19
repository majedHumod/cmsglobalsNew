<?php

namespace App\Services;

use App\Models\ClientMealPlan;
use App\Models\MealLog;
use App\Models\MealPlan;
use App\Models\User;
use Illuminate\Support\Collection;

class MealLogService
{
    public function todayLogsFor(User $user): Collection
    {
        return MealLog::query()
            ->where('user_id', $user->id)
            ->whereDate('logged_on', now()->toDateString())
            ->with('mealPlan:id,name,name_en,nutrition_is_estimated,image,calories,protein,carbs,fats')
            ->orderBy('meal_slot')
            ->get();
    }

    public function availablePlansFor(User $user): Collection
    {
        $assignedIds = ClientMealPlan::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->pluck('meal_plan_id');

        if ($assignedIds->isNotEmpty()) {
            return MealPlan::query()
                ->where('is_active', true)
                ->whereIn('id', $assignedIds)
                ->orderBy('name')
                ->get();
        }

        return MealPlan::query()
            ->where('is_active', true)
            ->visibleTo($user)
            ->orderBy('name')
            ->get();
    }

    public function searchPlansFor(User $user, ?string $term = null, ?string $mealType = null, int $limit = 20): Collection
    {
        $assignedIds = ClientMealPlan::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->pluck('meal_plan_id');

        $query = MealPlan::query()->where('is_active', true);

        if ($assignedIds->isNotEmpty()) {
            $query->whereIn('id', $assignedIds);
        } else {
            $query->visibleTo($user);
        }

        return $query
            ->searchLibrary($term, $mealType)
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    public function weeklyAdherenceRate(User $user): float
    {
        $start = now()->subDays(6)->toDateString();
        $end = now()->toDateString();

        $logs = MealLog::query()
            ->where('user_id', $user->id)
            ->whereBetween('logged_on', [$start, $end])
            ->get();

        if ($logs->isEmpty()) {
            return 0.0;
        }

        $expectedSlots = 7 * 3;
        $scoreSum = $logs->sum('adherence_score');
        $maxScore = $logs->count() * 10;

        if ($maxScore === 0) {
            return 0.0;
        }

        return round(min(100, ($scoreSum / $maxScore) * 100), 1);
    }

    public function logMeal(User $user, array $data): MealLog
    {
        return MealLog::updateOrCreate(
            [
                'user_id' => $user->id,
                'logged_on' => $data['logged_on'] ?? now()->toDateString(),
                'meal_slot' => $data['meal_slot'],
            ],
            [
                'meal_plan_id' => $data['meal_plan_id'] ?? null,
                'adherence_score' => (int) ($data['adherence_score'] ?? 0),
                'notes' => $data['notes'] ?? null,
            ]
        );
    }
}
