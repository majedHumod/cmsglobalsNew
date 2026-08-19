<?php

namespace Tests\Feature;

use App\Http\Resources\Api\ClientHomeResource;
use App\Http\Resources\Api\WorkoutTodayResource;
use App\Services\WorkoutScheduleService;
use App\Support\MigrationScope;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PhaseOneClientDailyTest extends TestCase
{
    public function test_phase_one_client_daily_routes_are_registered(): void
    {
        $this->assertTrue(Route::has('client.home'));
        $this->assertTrue(Route::has('api.workouts.complete'));
        $this->assertTrue(Route::has('api.workouts.skip'));
        $this->assertTrue(Route::has('api.v1.workouts.complete'));
        $this->assertTrue(Route::has('api.v1.workouts.skip'));
    }

    public function test_phase_one_migrations_live_in_tenant_scope(): void
    {
        $tenantPath = base_path(MigrationScope::TENANT_PATH);

        $this->assertFileExists($tenantPath . '/2026_07_03_000001_create_workout_logs_table.php');
        $this->assertFileExists($tenantPath . '/2026_07_03_000002_add_current_program_week_to_client_profiles_table.php');
    }

    public function test_workout_schedule_service_maps_saturday_to_session_one(): void
    {
        $service = app(WorkoutScheduleService::class);
        $saturday = Carbon::parse('2026-07-04'); // Saturday

        $this->assertSame(1, $service->sessionNumberForDate($saturday));
    }

    public function test_workout_schedule_service_maps_friday_to_session_seven(): void
    {
        $service = app(WorkoutScheduleService::class);
        $friday = Carbon::parse('2026-07-03'); // Friday

        $this->assertSame(7, $service->sessionNumberForDate($friday));
    }

    public function test_client_home_resource_includes_workout_fields(): void
    {
        $resource = new ClientHomeResource([
            'date' => now()->toDateString(),
            'progress_score' => 72.5,
            'weekly_habit_completion' => 60.0,
            'workout_compliance' => 80.0,
            'current_program_week' => 2,
            'next_best_action' => 'أنجز تمرين اليوم',
            'bookings' => collect(),
            'habits' => collect(),
            'today_workouts' => collect(),
            'week_overview' => [
                ['session_number' => 1, 'day_label' => 'السبت', 'has_workout' => true, 'is_completed' => false],
            ],
            'gamification' => ['points' => 10, 'badges_count' => 1],
            'latest_notification' => null,
            'last_check_in' => null,
            'check_in_url' => '/clients/1/progress/create',
        ]);

        $data = $resource->toArray(request());
        $this->assertArrayHasKey('today_workouts', $data);
        $this->assertArrayHasKey('week_overview', $data);
        $this->assertArrayHasKey('workout_compliance', $data);
        $this->assertArrayHasKey('current_program_week', $data);
        $this->assertArrayHasKey('check_in_url', $data);
        $this->assertSame(2, $data['current_program_week']);
    }

    public function test_workout_today_resource_contract_includes_status_fields(): void
    {
        $schedule = new \App\Models\WorkoutSchedule([
            'week_number' => 1,
            'session_number' => 1,
            'notes' => 'ملاحظة',
        ]);
        $schedule->id = 5;
        $workout = new \App\Models\Workout([
            'id' => 9,
            'name' => 'تمارين الصدر',
            'duration' => 45,
        ]);
        $schedule->setRelation('workout', $workout);

        $resource = new WorkoutTodayResource([
            'schedule' => $schedule,
            'workout' => $workout,
            'scheduled_on' => now()->toDateString(),
            'session_number' => 1,
            'session_label' => 'السبت',
            'program_week' => 1,
            'log' => null,
            'status' => null,
            'is_completed' => false,
            'is_skipped' => false,
        ]);

        $data = $resource->toArray(request());
        $this->assertSame(5, $data['workout_schedule_id']);
        $this->assertSame('تمارين الصدر', $data['name']);
        $this->assertFalse($data['is_completed']);
    }

    public function test_phase_one_pwa_assets_point_to_client_home(): void
    {
        $manifest = json_decode(file_get_contents(public_path('manifest.json')), true);
        $serviceWorker = file_get_contents(public_path('sw.js'));

        $this->assertSame('/client/home', $manifest['start_url']);
        $this->assertStringContainsString('/client/home', $serviceWorker);
        $this->assertFileExists(resource_path('views/client/home.blade.php'));
        $this->assertFileExists(resource_path('views/layouts/client.blade.php'));
        $this->assertFileExists(resource_path('js/client-home.js'));
    }
}
