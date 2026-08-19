<?php

namespace Tests\Feature;

use App\Http\Resources\Api\ClientHomeResource;
use App\Http\Resources\Api\CoachWorkspaceResource;
use App\Services\CoachRiskService;
use App\Services\HabitInsightsService;
use App\Services\MealLogService;
use App\Services\WorkoutScheduleService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PhaseTwoCoachOperationsTest extends TestCase
{
    public function test_phase_two_coach_workspace_routes_are_registered(): void
    {
        $this->assertTrue(Route::has('coach.workspace'));
        $this->assertTrue(Route::has('api.coach.workspace'));
        $this->assertTrue(Route::has('api.v1.coach.workspace'));
        $this->assertTrue(Route::has('coach.clients.index'));
        $this->assertTrue(Route::has('coach.clients.remind'));
    }

    public function test_phase_two_client_notification_routes_are_registered(): void
    {
        $this->assertTrue(Route::has('client.notifications.index'));
        $this->assertTrue(Route::has('client.notifications.unread-count'));
        $this->assertTrue(Route::has('client.notifications.read'));
        $this->assertTrue(Route::has('client.notifications.read-all'));
        $this->assertTrue(Route::has('api.notifications.unread-count'));
        $this->assertTrue(Route::has('api.v1.notifications.unread-count'));
        $this->assertTrue(Route::has('api.notifications.read'));
        $this->assertTrue(Route::has('api.v1.notifications.read'));
    }

    public function test_phase_two_client_booking_routes_are_registered(): void
    {
        $this->assertTrue(Route::has('client.bookings.index'));
        $this->assertTrue(Route::has('client.bookings.create'));
        $this->assertTrue(Route::has('client.bookings.store'));
        $this->assertTrue(Route::has('client.bookings.cancel'));
        $this->assertTrue(Route::has('client.bookings.reschedule'));
        $this->assertTrue(Route::has('client.training-sessions.slots'));
    }

    public function test_phase_two_membership_renewal_routes_and_command_exist(): void
    {
        $this->assertTrue(Route::has('subscription-plans.renew'));
        $this->assertTrue(Route::has('billing.webhooks.stripe'));
        $this->assertContains('memberships:expire-stale', array_keys(Artisan::all()));
    }

    public function test_coach_risk_service_threshold_constants(): void
    {
        $this->assertSame(14, CoachRiskService::CHECKIN_OVERDUE_DAYS);
        $this->assertSame(50, CoachRiskService::LOW_COMPLIANCE_THRESHOLD);
        $this->assertSame(40, CoachRiskService::LOW_HABITS_THRESHOLD);
        $this->assertSame(7, CoachRiskService::EXPIRING_SOON_DAYS);
    }

    public function test_coach_risk_service_priority_from_score(): void
    {
        $workoutMock = $this->createMock(WorkoutScheduleService::class);
        $habitMock = $this->createMock(HabitInsightsService::class);
        $mealMock = $this->createMock(MealLogService::class);
        $service = new CoachRiskService($workoutMock, $habitMock, $mealMock);

        $method = new \ReflectionMethod(CoachRiskService::class, 'priorityFromScore');
        $method->setAccessible(true);

        $this->assertSame('high', $method->invoke($service, 65));
        $this->assertSame('medium', $method->invoke($service, 35));
        $this->assertSame('low', $method->invoke($service, 15));
    }

    public function test_coach_workspace_resource_contract(): void
    {
        $resource = new CoachWorkspaceResource([
            'summary' => ['clients' => 5, 'atRiskCount' => 2],
            'at_risk_clients' => [['user_id' => 1, 'risk_score' => 65]],
            'clients' => [],
            'availabilities' => [],
        ]);

        $data = $resource->toArray(request());
        $this->assertArrayHasKey('summary', $data);
        $this->assertArrayHasKey('at_risk_clients', $data);
        $this->assertSame(2, $data['summary']['atRiskCount']);
    }

    public function test_client_home_resource_includes_membership_and_notification_fields(): void
    {
        $notification = new \App\Models\NotificationFeed([
            'type' => 'membership.expiring_7',
            'title' => 'اشتراكك ينتهي قريباً',
            'body' => 'متبقي 5 أيام',
        ]);
        $notification->id = 1;

        $resource = new ClientHomeResource([
            'date' => now()->toDateString(),
            'progress_score' => 55.0,
            'weekly_habit_completion' => 40.0,
            'workout_compliance' => 45.0,
            'current_program_week' => 1,
            'next_best_action' => 'جدّد اشتراكك',
            'bookings' => collect(),
            'habits' => collect(),
            'today_workouts' => collect(),
            'week_overview' => [],
            'gamification' => ['points' => 0, 'badges_count' => 0],
            'latest_notification' => $notification,
            'last_check_in' => null,
            'check_in_url' => '/clients/1/progress/create',
            'membership_days_remaining' => 5,
            'renew_url' => '/subscription-memberships/1/renew',
        ]);

        $data = $resource->toArray(request());
        $this->assertArrayHasKey('membership_days_remaining', $data);
        $this->assertArrayHasKey('renew_url', $data);
        $this->assertArrayHasKey('latest_notification', $data);
        $this->assertSame(5, $data['membership_days_remaining']);
    }

    public function test_phase_two_web_push_and_service_worker_handlers_exist(): void
    {
        $this->assertFileExists(app_path('Services/WebPushService.php'));
        $this->assertFileExists(app_path('Services/BookingSlotService.php'));
        $this->assertFileExists(app_path('Services/MembershipRenewalService.php'));

        $serviceWorker = file_get_contents(public_path('sw.js'));
        $this->assertStringContainsString("addEventListener('push'", $serviceWorker);
        $this->assertStringContainsString("addEventListener('notificationclick'", $serviceWorker);
    }

    public function test_notification_rule_engine_exposes_coach_at_risk_evaluation(): void
    {
        $this->assertTrue(method_exists(\App\Services\NotificationRuleEngine::class, 'evaluateCoachClientAtRisk'));
        $this->assertFileExists(resource_path('views/coach/workspace/index.blade.php'));
        $this->assertFileExists(resource_path('views/client/notifications/index.blade.php'));
        $this->assertFileExists(resource_path('views/client/bookings/index.blade.php'));
    }
}
