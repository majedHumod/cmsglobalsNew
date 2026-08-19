<?php

namespace App\Services;

use App\Models\ClientProfile;
use App\Models\User;
use Carbon\Carbon;

class TrainingProgramService
{
    /**
     * Start (or ensure) the training plan after subscription approval.
     */
    public function activateForUser(User $user, ?Carbon $startedAt = null, bool $restartIfExists = false): ClientProfile
    {
        $profile = ClientProfile::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'activity_level' => 'beginner',
                'preferred_contact_method' => 'whatsapp',
                'current_program_week' => 1,
            ]
        );

        if (! TrainingSettings::autoActivatePlanOnSubscription() && ! $restartIfExists) {
            return $profile;
        }

        if ($profile->program_started_at && ! $restartIfExists) {
            $this->syncStoredWeek($user->fresh(['clientProfile']));

            return $profile->fresh();
        }

        $startedAt ??= now();

        $profile->forceFill([
            'program_started_at' => $startedAt,
            'current_program_week' => 1,
        ])->save();

        return $profile->fresh();
    }

    public function resolveAdvanceMode(User $user): string
    {
        $override = $user->clientProfile?->week_advance_mode;

        if (in_array($override, [TrainingSettings::ADVANCE_AUTO, TrainingSettings::ADVANCE_MANUAL], true)) {
            return $override;
        }

        return TrainingSettings::weekAdvanceMode();
    }

    public function resolveProgramWeek(User $user, ?Carbon $now = null): int
    {
        $now ??= now();
        $profile = $user->clientProfile;
        $mode = $this->resolveAdvanceMode($user);

        if ($mode === TrainingSettings::ADVANCE_MANUAL || ! $profile?->program_started_at) {
            return max(1, min(52, (int) ($profile?->current_program_week ?? 1)));
        }

        /** @var WorkoutScheduleService $scheduleService */
        $scheduleService = app(WorkoutScheduleService::class);

        $started = Carbon::parse($profile->program_started_at)->startOfDay();
        $programWeekStart = $scheduleService->weekStartFor($started);
        $currentWeekStart = $scheduleService->weekStartFor($now->copy()->startOfDay());

        if ($currentWeekStart->lt($programWeekStart)) {
            return 1;
        }

        $days = (int) $programWeekStart->diffInDays($currentWeekStart);
        $week = (int) floor($days / 7) + 1;

        return max(1, min(52, $week));
    }

    /**
     * Persist computed week for coach dashboards when auto mode is on.
     */
    public function syncStoredWeek(User $user, ?Carbon $now = null): int
    {
        $user->loadMissing('clientProfile');
        $week = $this->resolveProgramWeek($user, $now);
        $profile = $user->clientProfile;

        if (! $profile) {
            return $week;
        }

        if ($this->resolveAdvanceMode($user) === TrainingSettings::ADVANCE_AUTO
            && (int) $profile->current_program_week !== $week
        ) {
            $profile->forceFill(['current_program_week' => $week])->save();
        }

        return $week;
    }
}
