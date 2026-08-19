<?php

namespace App\Services;

use App\Models\Habit;
use App\Models\HabitLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class HabitInsightsService
{
    /**
     * @param Collection<int, Habit> $habits
     */
    public function summarize(Collection $habits): array
    {
        $totalHabits = $habits->count();
        if ($totalHabits === 0) {
            return [
                'weekly_completion' => 0.0,
                'active_streak' => 0,
                'best_streak' => 0,
                'missed_days' => 0,
                'trend' => 'steady',
            ];
        }

        $weeklyWindowStart = now()->subDays(6)->toDateString();
        $weeklyWindowEnd = now()->toDateString();
        $twoWeeksStart = now()->subDays(13)->toDateString();

        $habitIds = $habits->pluck('id');
        $logs = HabitLog::query()
            ->whereIn('habit_id', $habitIds)
            ->whereBetween('logged_on', [$twoWeeksStart, $weeklyWindowEnd])
            ->get();

        $weeklyLogs = $logs->whereBetween('logged_on', [$weeklyWindowStart, $weeklyWindowEnd]);
        $previousWeekLogs = $logs->whereBetween('logged_on', [now()->subDays(13)->toDateString(), now()->subDays(7)->toDateString()]);

        $weeklyDone = $weeklyLogs->where('is_completed', true)->count();
        $weeklyCompletion = round(($weeklyDone / max(1, $totalHabits * 7)) * 100, 1);

        $previousDone = $previousWeekLogs->where('is_completed', true)->count();
        $trend = $weeklyDone > $previousDone ? 'up' : ($weeklyDone < $previousDone ? 'down' : 'steady');

        $streaks = $habits->map(function (Habit $habit) {
            $habitLogs = HabitLog::query()
                ->where('habit_id', $habit->id)
                ->where('is_completed', true)
                ->orderBy('logged_on')
                ->pluck('logged_on')
                ->map(fn ($date) => Carbon::parse($date)->toDateString())
                ->all();
            return $this->calculateStreaks($habitLogs);
        });

        return [
            'weekly_completion' => $weeklyCompletion,
            'active_streak' => (int) $streaks->max('current'),
            'best_streak' => (int) $streaks->max('best'),
            'missed_days' => (int) max(0, ($totalHabits * 7) - $weeklyDone),
            'trend' => $trend,
        ];
    }

    /**
     * @param array<int, string> $completedDates
     */
    private function calculateStreaks(array $completedDates): array
    {
        if ($completedDates === []) {
            return ['current' => 0, 'best' => 0];
        }

        sort($completedDates);
        $best = 1;
        $currentRun = 1;
        $bestRun = 1;

        for ($i = 1; $i < count($completedDates); $i++) {
            $prev = Carbon::parse($completedDates[$i - 1]);
            $curr = Carbon::parse($completedDates[$i]);
            if ($prev->copy()->addDay()->toDateString() === $curr->toDateString()) {
                $currentRun++;
            } else {
                $currentRun = 1;
            }
            $bestRun = max($bestRun, $currentRun);
        }

        $cursor = now()->toDateString();
        $set = array_flip($completedDates);
        $current = 0;
        while (isset($set[$cursor])) {
            $current++;
            $cursor = Carbon::parse($cursor)->subDay()->toDateString();
        }

        $best = max($best, $bestRun);
        return ['current' => $current, 'best' => $best];
    }
}
