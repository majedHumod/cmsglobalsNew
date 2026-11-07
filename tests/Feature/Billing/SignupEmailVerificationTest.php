<?php

namespace Tests\Feature\Billing;

use App\Http\Controllers\Billing\SignupEmailVerificationController;
use App\Models\Tenant;
use Illuminate\Support\Facades\Mail;
use Tests\Support\BillingTestCase;

class SignupEmailVerificationTest extends BillingTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        config(['mail.default' => 'array']);
    }

    private function sentCode(): string
    {
        $messages = Mail::mailer('array')->getSymfonyTransport()->messages();
        $this->assertNotEmpty($messages, 'No email was sent.');

        $email = $messages->last()->getOriginalMessage();
        $body = (string) $email->getTextBody().' '.(string) $email->getHtmlBody();
        $this->assertSame(1, preg_match('/(\d{6})/', $body, $m), 'Verification code not found in: '.$body);

        return $m[1];
    }

    public function test_code_is_emailed_and_verifies_the_address(): void
    {
        $this->postJson('/subscribe/email-code', ['email' => 'Owner@Example.com'])->assertOk();

        $this->assertFalse(SignupEmailVerificationController::isVerified('owner@example.com'));

        $this->postJson('/subscribe/email-verify', ['email' => 'owner@example.com', 'code' => $this->sentCode()])
            ->assertOk()
            ->assertJsonPath('verified', true);

        $this->assertTrue(SignupEmailVerificationController::isVerified('OWNER@example.com'));
    }

    public function test_wrong_code_is_rejected_and_attempts_are_limited(): void
    {
        $this->postJson('/subscribe/email-code', ['email' => 'owner@example.com'])->assertOk();
        $real = $this->sentCode();
        $wrong = $real === '000000' ? '111111' : '000000';

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/subscribe/email-verify', ['email' => 'owner@example.com', 'code' => $wrong])
                ->assertStatus(422)->assertJsonPath('code', 'invalid_code');
        }

        // After 5 failures even the right code no longer works: a new code must be requested.
        $this->postJson('/subscribe/email-verify', ['email' => 'owner@example.com', 'code' => $real])
            ->assertStatus(422)->assertJsonPath('code', 'too_many_attempts');
        $this->assertFalse(SignupEmailVerificationController::isVerified('owner@example.com'));
    }

    public function test_resend_is_rate_limited_per_address(): void
    {
        $this->postJson('/subscribe/email-code', ['email' => 'owner@example.com'])->assertOk();
        $this->postJson('/subscribe/email-code', ['email' => 'owner@example.com'])
            ->assertStatus(429)->assertJsonPath('code', 'cooldown');
    }

    public function test_address_that_already_owns_a_club_is_told_to_log_in_and_gets_no_code(): void
    {
        Tenant::create(['name' => 'Club', 'subdomain' => 'club', 'email' => 'owner@example.com']);

        $this->postJson('/subscribe/email-code', ['email' => 'owner@example.com'])
            ->assertStatus(422)
            ->assertJsonPath('code', 'email_in_use')
            ->assertJsonStructure(['login_url']);

        $this->assertEmpty(Mail::mailer('array')->getSymfonyTransport()->messages());
    }

    public function test_honeypot_blocks_bots_from_triggering_emails(): void
    {
        $this->postJson('/subscribe/email-code', ['email' => 'owner@example.com', 'website' => 'spam'])
            ->assertStatus(422);

        $this->assertEmpty(Mail::mailer('array')->getSymfonyTransport()->messages());
    }
}
