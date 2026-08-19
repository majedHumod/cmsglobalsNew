<?php

namespace Tests\Feature;

use App\Support\MigrationScope;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PhaseOneGapClosureTest extends TestCase
{
    public function test_phase_one_web_routes_are_registered(): void
    {
        $this->assertTrue(Route::has('messages.index'));
        $this->assertTrue(Route::has('messages.show'));
        $this->assertTrue(Route::has('messages.send'));

        $this->assertTrue(Route::has('notifications.index'));
        $this->assertTrue(Route::has('notifications.read'));
        $this->assertTrue(Route::has('notifications.read-all'));

        $this->assertTrue(Route::has('habits.index'));
        $this->assertTrue(Route::has('habits.log'));
    }

    public function test_phase_one_api_routes_are_registered(): void
    {
        $this->assertTrue(Route::has('api.client.home'));
        $this->assertTrue(Route::has('api.messages.threads'));
        $this->assertTrue(Route::has('api.messages.send'));
        $this->assertTrue(Route::has('api.habits.today'));
        $this->assertTrue(Route::has('api.notifications.index'));
    }

    public function test_phase_one_migrations_live_in_tenant_scope(): void
    {
        $tenantPath = base_path(MigrationScope::TENANT_PATH);

        $this->assertFileExists($tenantPath . '/2026_04_24_000001_create_messaging_tables.php');
        $this->assertFileExists($tenantPath . '/2026_04_24_000002_create_notifications_feed_table.php');
        $this->assertFileExists($tenantPath . '/2026_04_24_000003_create_habit_tracking_tables.php');
    }
}
