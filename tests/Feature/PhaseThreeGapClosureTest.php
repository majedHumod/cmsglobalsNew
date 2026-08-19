<?php

namespace Tests\Feature;

use App\Http\Resources\Api\ClientHomeResource;
use App\Jobs\SendInactiveClientFollowUpJob;
use App\Listeners\AwardCheckInGamification;
use App\Listeners\AwardHabitGamification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PhaseThreeGapClosureTest extends TestCase
{
    public function test_phase_three_routes_are_registered(): void
    {
        $this->assertTrue(Route::has('community.index'));
        $this->assertTrue(Route::has('community.store'));
        $this->assertTrue(Route::has('session-bookings.calendar'));
        $this->assertTrue(Route::has('offline'));

        $this->assertTrue(Route::has('api.v1.mobile.bootstrap'));
        $this->assertTrue(Route::has('api.v1.push-subscriptions.store'));
        $this->assertTrue(Route::has('api.v1.community.index'));
        $this->assertTrue(Route::has('api.webhooks.communication'));
    }

    public function test_phase_three_static_pwa_assets_exist(): void
    {
        $this->assertFileExists(public_path('manifest.json'));
        $this->assertFileExists(public_path('sw.js'));
        $this->assertFileExists(resource_path('views/offline.blade.php'));
    }

    public function test_phase_three_gamification_listeners_are_queueable(): void
    {
        $this->assertContains(ShouldQueue::class, class_implements(AwardHabitGamification::class));
        $this->assertContains(ShouldQueue::class, class_implements(AwardCheckInGamification::class));
    }

    public function test_phase_three_follow_up_job_is_dispatchable(): void
    {
        Queue::fake();
        SendInactiveClientFollowUpJob::dispatch(1, 'inactive_clients_7d');
        Queue::assertPushed(SendInactiveClientFollowUpJob::class);
    }

    public function test_client_home_resource_contains_gamification_and_calendar_fields(): void
    {
        $resource = new ClientHomeResource([
            'date' => now()->toDateString(),
            'progress_score' => 80.1,
            'weekly_habit_completion' => 70.0,
            'next_best_action' => 'action',
            'bookings' => collect(),
            'habits' => collect(),
            'gamification' => ['points' => 100, 'badges_count' => 2],
            'latest_notification' => null,
            'last_check_in' => null,
        ]);

        $data = $resource->toArray(request());
        $this->assertArrayHasKey('gamification', $data);
        $this->assertArrayHasKey('bookings', $data);
    }
}
