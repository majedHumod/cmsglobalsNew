<?php

namespace Tests\Unit;

use App\Services\WhatsAppOtpService;
use Tests\TestCase;

class WhatsAppOtpDebugGuardTest extends TestCase
{
    public function test_debug_code_is_allowed_outside_production(): void
    {
        $this->app['env'] = 'local';
        config(['services.whatsapp.otp_debug' => true]);

        $this->assertTrue(app(WhatsAppOtpService::class)->shouldExposeDebugCode());
    }

    public function test_debug_code_is_never_exposed_in_production(): void
    {
        $this->app['env'] = 'production';
        config(['services.whatsapp.otp_debug' => true]);

        $this->assertFalse(app(WhatsAppOtpService::class)->shouldExposeDebugCode());
    }
}
