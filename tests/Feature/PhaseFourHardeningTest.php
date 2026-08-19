<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PhaseFourHardeningTest extends TestCase
{
    public function test_webhook_route_has_security_middlewares(): void
    {
        $route = Route::getRoutes()->getByName('api.webhooks.communication');
        $this->assertNotNull($route);

        $middleware = $route->gatherMiddleware();
        $this->assertContains('verify_webhook_signature', $middleware);
        $this->assertContains('throttle:120,1', $middleware);
    }

    public function test_phase_four_commands_are_registered(): void
    {
        $commands = array_keys(Artisan::all());

        $this->assertContains('performance:baseline', $commands);
        $this->assertContains('security:check-secrets', $commands);
        $this->assertContains('system:health-check', $commands);
        $this->assertContains('tenant:preflight', $commands);
    }

    public function test_tenant_migrate_all_supports_skip_preflight_option(): void
    {
        $command = Artisan::all()['tenant:migrate-all'] ?? null;
        $this->assertNotNull($command);
        $this->assertTrue($command->getDefinition()->hasOption('skip-preflight'));
    }
}
