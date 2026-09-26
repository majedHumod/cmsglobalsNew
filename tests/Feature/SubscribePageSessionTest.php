<?php

namespace Tests\Feature;

use Tests\TestCase;

class SubscribePageSessionTest extends TestCase
{
    public function test_subscribe_ignores_stale_session_user_on_system_connection(): void
    {
        $response = $this->withSession([
            'login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d' => 2,
        ])->get('/subscribe');

        $response->assertOk();
    }
}
