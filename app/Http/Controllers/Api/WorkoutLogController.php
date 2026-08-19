<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\WorkoutTodayResource;
use App\Models\WorkoutSchedule;
use App\Services\ClientHomeService;
use App\Services\WorkoutScheduleService;
use Illuminate\Http\Request;

class WorkoutLogController extends Controller
{
    public function today(Request $request, WorkoutScheduleService $scheduleService)
    {
        $user = $request->user();
        abort_unless($user->hasAnyRole(['user', 'client']), 403);

        $workouts = $scheduleService->todaySchedulesFor($user);

        return response()->json([
            'date' => now()->toDateString(),
            'workouts' => WorkoutTodayResource::collection($workouts),
        ]);
    }

    public function show(Request $request, WorkoutSchedule $workoutSchedule, WorkoutScheduleService $scheduleService)
    {
        $user = $request->user();
        abort_unless($user->hasAnyRole(['user', 'client']), 403);

        $decorated = $scheduleService->detailFor($user, $workoutSchedule);

        return response()->json([
            'workout' => new WorkoutTodayResource($decorated),
            'workout_compliance' => $scheduleService->weeklyComplianceRate($user),
        ]);
    }

    public function complete(Request $request, WorkoutSchedule $workoutSchedule, WorkoutScheduleService $scheduleService, ClientHomeService $homeService)
    {
        $user = $request->user();
        abort_unless($user->hasAnyRole(['user', 'client']), 403);

        $validated = $request->validate([
            'scheduled_on' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
        ]);

        $log = $scheduleService->logWorkout(
            $user,
            $workoutSchedule,
            'completed',
            isset($validated['scheduled_on']) ? \Carbon\Carbon::parse($validated['scheduled_on']) : null,
            $validated['notes'] ?? null
        );

        $decorated = $scheduleService->detailFor(
            $user,
            $workoutSchedule,
            isset($validated['scheduled_on']) ? \Carbon\Carbon::parse($validated['scheduled_on']) : now()
        );

        $overview = $homeService->payloadFor($user->fresh())['progress_overview'] ?? null;

        return response()->json([
            'message' => 'تم تسجيل إنجاز التمرين.',
            'workout' => new WorkoutTodayResource($decorated),
            'workout_compliance' => $scheduleService->weeklyComplianceRate($user),
            'progress_overview' => $overview,
        ]);
    }

    public function skip(Request $request, WorkoutSchedule $workoutSchedule, WorkoutScheduleService $scheduleService, ClientHomeService $homeService)
    {
        $user = $request->user();
        abort_unless($user->hasAnyRole(['user', 'client']), 403);

        $validated = $request->validate([
            'scheduled_on' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
        ]);

        $log = $scheduleService->logWorkout(
            $user,
            $workoutSchedule,
            'skipped',
            isset($validated['scheduled_on']) ? \Carbon\Carbon::parse($validated['scheduled_on']) : null,
            $validated['notes'] ?? null
        );

        $decorated = $scheduleService->detailFor(
            $user,
            $workoutSchedule,
            isset($validated['scheduled_on']) ? \Carbon\Carbon::parse($validated['scheduled_on']) : now()
        );

        $overview = $homeService->payloadFor($user->fresh())['progress_overview'] ?? null;

        return response()->json([
            'message' => 'تم تسجيل تخطي التمرين.',
            'workout' => new WorkoutTodayResource($decorated),
            'workout_compliance' => $scheduleService->weeklyComplianceRate($user),
            'progress_overview' => $overview,
            'status' => $log->status,
        ]);
    }
}
