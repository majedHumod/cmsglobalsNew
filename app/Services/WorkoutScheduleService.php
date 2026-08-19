<?php

namespace App\Services;

use App\Models\User;
use App\Models\WorkoutLog;
use App\Models\WorkoutSchedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class WorkoutScheduleService
{
    private const DAY_LABELS = [
        0 => 'الأحد',
        1 => 'الاثنين',
        2 => 'الثلاثاء',
        3 => 'الأربعاء',
        4 => 'الخميس',
        5 => 'الجمعة',
        6 => 'السبت',
    ];

    /**
     * Carbon dayOfWeek => session_number (1…7) based on tenant week start.
     *
     * @return array<int, int>
     */
    public function dayToSessionMap(): array
    {
        $startDow = TrainingSettings::weekStartDay() === TrainingSettings::WEEK_START_SUNDAY ? 0 : 6;
        $map = [];

        for ($session = 1; $session <= 7; $session++) {
            $dow = ($startDow + $session - 1) % 7;
            $map[$dow] = $session;
        }

        return $map;
    }

    /**
     * @return array<int, string>
     */
    public function sessionLabels(): array
    {
        $map = array_flip($this->dayToSessionMap());
        $labels = [];

        for ($session = 1; $session <= 7; $session++) {
            $dow = $map[$session] ?? 0;
            $labels[$session] = self::DAY_LABELS[$dow] ?? "الجلسة {$session}";
        }

        return $labels;
    }

    public function sessionNumberForDate(Carbon $date): int
    {
        return $this->dayToSessionMap()[$date->dayOfWeek] ?? 1;
    }

    public function currentProgramWeek(User $user): int
    {
        return app(TrainingProgramService::class)->syncStoredWeek($user);
    }

    public function todaySchedulesFor(User $user, ?Carbon $date = null): Collection
    {
        $date ??= now();
        $programWeek = $this->currentProgramWeek($user);
        $sessionNumber = $this->sessionNumberForDate($date);

        return WorkoutSchedule::query()
            ->with(['workout.exercises'])
            ->active()
            ->visibleTo($user)
            ->where('week_number', $programWeek)
            ->where('session_number', $sessionNumber)
            ->orderBy('id')
            ->get()
            ->map(fn (WorkoutSchedule $schedule) => $this->decorateSchedule($schedule, $user, $date->toDateString()));
    }

    /**
     * Full payload for the home card / workout detail screen.
     *
     * @return array<string, mixed>
     */
    public function detailFor(User $user, WorkoutSchedule $schedule, ?Carbon $date = null): array
    {
        abort_unless($schedule->status && $schedule->matchesAudience($user), 403);

        $date ??= now();
        $scheduledOn = $date->toDateString();

        $programWeek = $this->currentProgramWeek($user);
        if ((int) $schedule->week_number === $programWeek) {
            $weekStart = $this->weekStartFor($date);
            $scheduledOn = $this->dateForSessionInWeek($weekStart, (int) $schedule->session_number)->toDateString();
        }

        $schedule->loadMissing('workout.exercises');

        return $this->decorateSchedule($schedule, $user, $scheduledOn);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function weekOverviewFor(User $user, ?Carbon $anchorDate = null): array
    {
        $anchorDate ??= now();
        $programWeek = $this->currentProgramWeek($user);
        $weekStart = $this->weekStartFor($anchorDate);
        $labels = $this->sessionLabels();

        $schedulesBySession = WorkoutSchedule::query()
            ->with('workout')
            ->active()
            ->visibleTo($user)
            ->where('week_number', $programWeek)
            ->orderBy('session_number')
            ->orderBy('id')
            ->get()
            ->groupBy('session_number');

        $logs = WorkoutLog::query()
            ->where('user_id', $user->id)
            ->whereBetween('scheduled_on', [$weekStart->toDateString(), $weekStart->copy()->addDays(6)->toDateString()])
            ->get()
            ->groupBy(fn (WorkoutLog $log) => $log->scheduled_on->toDateString());

        $overview = [];

        for ($session = 1; $session <= 7; $session++) {
            $dayDate = $this->dateForSessionInWeek($weekStart, $session);
            $daySchedules = $schedulesBySession->get($session, collect());
            $dayLogs = $logs->get($dayDate->toDateString(), collect());

            $workouts = $daySchedules->map(function (WorkoutSchedule $schedule) use ($dayLogs) {
                $log = $dayLogs->firstWhere('workout_schedule_id', $schedule->id);

                return [
                    'workout_schedule_id' => $schedule->id,
                    'workout_name' => $schedule->workout?->name,
                    'status' => $log?->status,
                    'is_completed' => $log?->status === 'completed',
                    'is_skipped' => $log?->status === 'skipped',
                ];
            })->values()->all();

            $completedCount = collect($workouts)->where('is_completed', true)->count();
            $skippedCount = collect($workouts)->where('is_skipped', true)->count();
            $total = count($workouts);
            $allCompleted = $total > 0 && $completedCount === $total;
            $allSkipped = $total > 0 && $skippedCount === $total;

            $status = null;
            if ($allCompleted) {
                $status = 'completed';
            } elseif ($allSkipped) {
                $status = 'skipped';
            } elseif ($completedCount > 0 || $skippedCount > 0) {
                $status = 'partial';
            }

            $primary = $daySchedules->first();

            $overview[] = [
                'session_number' => $session,
                'day_label' => $labels[$session] ?? "الجلسة {$session}",
                'date' => $dayDate->toDateString(),
                'is_today' => $dayDate->isSameDay($anchorDate),
                'has_workout' => $total > 0,
                'workouts_count' => $total,
                'completed_count' => $completedCount,
                'skipped_count' => $skippedCount,
                'workouts' => $workouts,
                'workout_schedule_id' => $primary?->id,
                'workout_name' => $primary?->workout?->name,
                'status' => $status,
                'is_completed' => $allCompleted,
                'is_skipped' => $allSkipped,
            ];
        }

        return $overview;
    }

    public function weeklyComplianceRate(User $user, ?Carbon $anchorDate = null): float
    {
        $anchorDate ??= now();
        $programWeek = $this->currentProgramWeek($user);
        $weekStart = $this->weekStartFor($anchorDate);

        $schedules = WorkoutSchedule::query()
            ->active()
            ->visibleTo($user)
            ->where('week_number', $programWeek)
            ->get();

        if ($schedules->isEmpty()) {
            return 0.0;
        }

        $completed = 0;

        foreach ($schedules as $schedule) {
            $scheduledOn = $this->dateForSessionInWeek($weekStart, (int) $schedule->session_number)->toDateString();
            $log = WorkoutLog::query()
                ->where('user_id', $user->id)
                ->where('workout_schedule_id', $schedule->id)
                ->whereDate('scheduled_on', $scheduledOn)
                ->first();

            if ($log?->status === 'completed') {
                $completed++;
            }
        }

        return round(($completed / $schedules->count()) * 100, 1);
    }

    public function logWorkout(User $user, WorkoutSchedule $schedule, string $status, ?Carbon $scheduledOn = null, ?string $notes = null): WorkoutLog
    {
        abort_unless(in_array($status, ['completed', 'skipped'], true), 422);
        abort_unless($schedule->status && $schedule->matchesAudience($user), 403);

        $scheduledOn ??= now();
        $scheduledDate = $scheduledOn->toDateString();

        return WorkoutLog::updateOrCreate(
            [
                'user_id' => $user->id,
                'workout_schedule_id' => $schedule->id,
                'scheduled_on' => $scheduledDate,
            ],
            [
                'workout_id' => $schedule->workout_id,
                'status' => $status,
                'completed_at' => now(),
                'notes' => $notes,
            ]
        );
    }

    public function complianceRateForClients(Collection $clientIds, int $days = 7): float
    {
        if ($clientIds->isEmpty()) {
            return 0.0;
        }

        $fromDate = now()->subDays($days - 1)->startOfDay()->toDateString();
        $toDate = now()->toDateString();

        $completed = WorkoutLog::query()
            ->whereIn('user_id', $clientIds)
            ->where('status', 'completed')
            ->whereBetween('scheduled_on', [$fromDate, $toDate])
            ->count();

        $skipped = WorkoutLog::query()
            ->whereIn('user_id', $clientIds)
            ->where('status', 'skipped')
            ->whereBetween('scheduled_on', [$fromDate, $toDate])
            ->count();

        $total = $completed + $skipped;
        if ($total === 0) {
            return 0.0;
        }

        return round(($completed / $total) * 100, 1);
    }

    public function complianceRateForClient(User $user, int $days = 7): float
    {
        return $this->complianceRateForClients(collect([$user->id]), $days);
    }

    public function weekStartFor(Carbon $date): Carbon
    {
        $sessionNumber = $this->sessionNumberForDate($date);
        $daysBack = $sessionNumber - 1;

        return $date->copy()->startOfDay()->subDays($daysBack);
    }

    public function dateForSessionInWeek(Carbon $weekStart, int $sessionNumber): Carbon
    {
        return $weekStart->copy()->addDays($sessionNumber - 1);
    }

    /**
     * @return array<string, mixed>
     */
    private function decorateSchedule(WorkoutSchedule $schedule, User $user, string $scheduledOn): array
    {
        $log = WorkoutLog::query()
            ->where('user_id', $user->id)
            ->where('workout_schedule_id', $schedule->id)
            ->whereDate('scheduled_on', $scheduledOn)
            ->first();

        $labels = $this->sessionLabels();

        return [
            'schedule' => $schedule,
            'workout' => $schedule->workout,
            'scheduled_on' => $scheduledOn,
            'session_number' => $schedule->session_number,
            'session_label' => $labels[$schedule->session_number] ?? "الجلسة {$schedule->session_number}",
            'program_week' => $schedule->week_number,
            'log' => $log,
            'status' => $log?->status,
            'is_completed' => $log?->status === 'completed',
            'is_skipped' => $log?->status === 'skipped',
        ];
    }
}
