<?php

namespace Tests\Feature;

use App\Http\Resources\Api\ClientHomeResource;
use App\Services\CoachRiskService;
use App\Services\MealLogService;
use App\Support\MigrationScope;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PhaseThreeCoachClientTest extends TestCase
{
    public function test_phase_three_client_pwa_routes_are_registered(): void
    {
        $this->assertTrue(Route::has('client.habits.index'));
        $this->assertTrue(Route::has('client.progress.create'));
        $this->assertTrue(Route::has('client.progress.store'));
        $this->assertTrue(Route::has('client.messages.index'));
        $this->assertTrue(Route::has('client.messages.send'));
        $this->assertTrue(Route::has('client.nutrition.index'));
        $this->assertTrue(Route::has('client.community.index'));
        $this->assertTrue(Route::has('client.challenges.index'));
    }

    public function test_phase_three_meal_logs_migration_exists_in_tenant_scope(): void
    {
        $this->assertFileExists(base_path(MigrationScope::TENANT_PATH . '/2026_07_04_000001_create_meal_logs_table.php'));
    }

    public function test_coach_risk_service_has_nutrition_threshold(): void
    {
        $this->assertSame(40, CoachRiskService::LOW_NUTRITION_THRESHOLD);
    }

    public function test_meal_log_service_class_exists(): void
    {
        $this->assertTrue(class_exists(MealLogService::class));
        $this->assertTrue(class_exists(\App\Models\MealLog::class));
    }

    public function test_client_home_resource_includes_phase_three_fields(): void
    {
        $resource = new ClientHomeResource([
            'date' => now()->toDateString(),
            'progress_score' => 70.0,
            'weekly_habit_completion' => 60.0,
            'workout_compliance' => 75.0,
            'current_program_week' => 1,
            'next_best_action' => 'سجّل وجبتك',
            'bookings' => collect(),
            'habits' => collect(),
            'today_workouts' => collect(),
            'week_overview' => [],
            'gamification' => ['points' => 50, 'badges_count' => 2],
            'latest_notification' => null,
            'latest_message' => null,
            'last_check_in' => null,
            'check_in_url' => '/client/progress/create',
            'messages_url' => '/client/messages',
            'nutrition_url' => '/client/nutrition',
            'community_url' => '/client/community',
            'nutrition_adherence' => 65.5,
            'membership_days_remaining' => null,
            'renew_url' => null,
        ]);

        $data = $resource->toArray(request());
        $this->assertArrayHasKey('nutrition_adherence', $data);
        $this->assertArrayHasKey('messages_url', $data);
        $this->assertArrayHasKey('nutrition_url', $data);
        $this->assertArrayHasKey('community_url', $data);
        $this->assertSame(65.5, $data['nutrition_adherence']);
    }

    public function test_phase_three_client_views_exist(): void
    {
        $this->assertFileExists(resource_path('views/client/habits/index.blade.php'));
        $this->assertFileExists(resource_path('views/client/progress/create.blade.php'));
        $this->assertFileExists(resource_path('views/client/messages/index.blade.php'));
        $this->assertFileExists(resource_path('views/client/nutrition/index.blade.php'));
        $this->assertFileExists(resource_path('views/client/community/index.blade.php'));
        $this->assertFileExists(resource_path('views/client/challenges/index.blade.php'));
    }

    public function test_web_push_supports_message_received_type(): void
    {
        $source = file_get_contents(app_path('Services/WebPushService.php'));
        $this->assertStringContainsString("'message.received'", $source);
    }

    public function test_phase_three_demo_seeder_class_exists(): void
    {
        $this->assertTrue(class_exists(\Database\Seeders\Tenants\PhaseThreeDemoSeeder::class));
    }
}
