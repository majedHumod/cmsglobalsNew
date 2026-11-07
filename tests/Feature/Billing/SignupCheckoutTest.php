<?php

namespace Tests\Feature\Billing;

use App\Http\Controllers\Billing\SignupEmailVerificationController;
use App\Models\Billing\BillingContact;
use App\Models\Billing\Plan;
use App\Models\Billing\Subscription;
use App\Models\Tenant;
use App\Models\TenantDatabasePool;
use App\Services\Billing\PaylinkService;
use App\Services\Platform\PlatformAccountCookie;
use Illuminate\Support\Facades\Cache;
use Mockery\MockInterface;
use Tests\Support\BillingTestCase;

class SignupCheckoutTest extends BillingTestCase
{
    private int $invoiceCounter = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    private function fakePaylink(?int &$calls = null): void
    {
        $counter = &$this->invoiceCounter;
        $this->partialMock(PaylinkService::class, function (MockInterface $mock) use (&$counter, &$calls) {
            $mock->shouldReceive('createInvoice')->andReturnUsing(function () use (&$counter, &$calls) {
                $counter++;
                $calls = ($calls ?? 0) + 1;

                return [
                    'transactionNo' => 'TX'.$counter,
                    'url' => 'https://paylink.test/pay/TX'.$counter,
                    'mobileUrl' => null,
                    'checkUrl' => null,
                ];
            });
        });
    }

    private function neverCallPaylink(): void
    {
        $this->partialMock(PaylinkService::class, function (MockInterface $mock) {
            $mock->shouldNotReceive('createInvoice');
        });
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function checkout(array $overrides = [])
    {
        return $this->postJson('/checkout/session', array_merge([
            'plan_code' => 'pro_monthly',
            'subdomain' => 'mjd-club',
            'email' => 'Owner@Example.com',
            'name' => 'Owner',
            'mobile' => '0501234567',
            'intent' => 'new',
        ], $overrides));
    }

    private function makeTenant(array $attrs = []): Tenant
    {
        return Tenant::create($attrs + [
            'name' => 'Existing Club',
            'slug' => 'existing',
            'subdomain' => 'existing',
            'domain' => 'existing.etoscoach.com',
            'email' => 'existing@example.com',
            'status' => 'active',
            'access_status' => 'active',
            'db_name' => 'tenant_existing',
        ]);
    }

    /**
     * Acts as a logged-in club owner (platform cookie is not encrypted by Laravel; JSON requests need withCredentials).
     */
    private function actingAsOwnerOf(Tenant $tenant): static
    {
        $value = app(PlatformAccountCookie::class)
            ->put($tenant, (string) $tenant->email, 'Owner')
            ->getValue();

        return $this->withCredentials()->withUnencryptedCookie(config('platform.cookie'), $value);
    }

    public function test_new_signup_reserves_name_and_email_and_stores_normalised_contact(): void
    {
        $this->fakePaylink();

        $response = $this->checkout();

        $response->assertOk()->assertJsonPath('redirect_url', 'https://paylink.test/pay/TX1');

        $this->assertDatabaseHas('signup_reservations', [
            'order_number' => $response->json('order_number'),
            'slug' => 'mjd-club',
            'email' => 'owner@example.com',
            'mobile_e164' => '+966501234567',
            'status' => 'pending',
            'active_slug' => 'mjd-club',
            'active_email' => 'owner@example.com',
        ], 'system');

        $this->assertDatabaseHas('invoices', ['number' => $response->json('order_number'), 'status' => 'pending'], 'system');
    }

    public function test_email_that_already_owns_a_club_is_rejected_before_paylink(): void
    {
        $this->neverCallPaylink();
        $this->makeTenant(['email' => 'owner@example.com']);

        $this->checkout(['email' => 'OWNER@example.com'])
            ->assertStatus(422)
            ->assertJsonPath('code', 'email_in_use')
            ->assertJsonStructure(['login_url']);
    }

    public function test_email_registered_only_as_billing_contact_is_rejected(): void
    {
        $this->neverCallPaylink();
        $tenant = $this->makeTenant(['email' => 'someone-else@example.com']);
        BillingContact::create(['tenant_id' => $tenant->id, 'email' => 'Owner@Example.com', 'name' => 'x']);

        $this->checkout()->assertStatus(422)->assertJsonPath('code', 'email_in_use');
    }

    public function test_subdomain_held_by_another_pending_checkout_is_rejected(): void
    {
        $this->fakePaylink();

        $this->checkout(['email' => 'first@example.com'])->assertOk();

        $this->checkout(['email' => 'second@example.com'])
            ->assertStatus(422)
            ->assertJsonPath('code', 'slug_pending');
    }

    public function test_expired_reservation_frees_the_subdomain(): void
    {
        $this->fakePaylink();

        $first = $this->checkout(['email' => 'first@example.com'])->assertOk();
        $this->travel(3)->hours();

        $this->checkout(['email' => 'second@example.com'])->assertOk();

        $this->assertDatabaseHas('signup_reservations', [
            'order_number' => $first->json('order_number'),
            'status' => 'released',
            'active_slug' => null,
        ], 'system');
    }

    public function test_same_buyer_restarting_an_abandoned_checkout_supersedes_the_old_attempt(): void
    {
        $this->fakePaylink();

        $first = $this->checkout(['subdomain' => 'first-try'])->assertOk();
        $second = $this->checkout(['subdomain' => 'second-try'])->assertOk();

        $this->assertDatabaseHas('signup_reservations', ['order_number' => $first->json('order_number'), 'status' => 'released'], 'system');
        $this->assertDatabaseHas('signup_reservations', ['order_number' => $second->json('order_number'), 'status' => 'pending'], 'system');
    }

    public function test_invalid_mobile_is_rejected(): void
    {
        $this->neverCallPaylink();

        $this->checkout(['mobile' => '1234567890'])
            ->assertStatus(422)
            ->assertJsonStructure(['errors' => ['mobile']]);
    }

    public function test_duplicate_mobile_is_allowed_by_default_but_blocked_when_configured(): void
    {
        $this->fakePaylink();
        $this->makeTenant(['phone' => '+966501234567']);

        $this->checkout()->assertOk();

        config(['platform.signup.unique_mobile' => true]);
        $this->checkout(['email' => 'other@example.com', 'subdomain' => 'other-club'])
            ->assertStatus(422)
            ->assertJsonPath('code', 'mobile_in_use');
    }

    public function test_email_must_be_verified_when_verification_is_enabled(): void
    {
        $this->fakePaylink();
        config(['platform.signup.verify_email' => true]);

        $this->checkout()->assertStatus(422)->assertJsonPath('code', 'email_unverified');

        Cache::put(SignupEmailVerificationController::verifiedCacheKey('owner@example.com'), true, 600);

        $this->checkout()->assertOk();
    }

    public function test_signups_are_refused_when_no_tenant_database_is_left(): void
    {
        $this->neverCallPaylink();
        config(['platform.signup.require_free_database' => true]);

        $this->checkout()->assertStatus(503)->assertJsonPath('code', 'capacity');
    }

    public function test_pool_capacity_counts_databases_already_reserved_by_pending_checkouts(): void
    {
        $this->fakePaylink();
        config(['platform.signup.require_free_database' => true]);
        TenantDatabasePool::create(['db_name' => 'tenant_pool_1', 'status' => 'available', 'is_ready' => true]);

        $this->checkout(['email' => 'first@example.com', 'subdomain' => 'first-club'])->assertOk();

        // The only free database is already held by the first buyer.
        $this->checkout(['email' => 'second@example.com', 'subdomain' => 'second-club'])
            ->assertStatus(503)
            ->assertJsonPath('code', 'capacity');
    }

    public function test_logged_in_customer_cannot_start_a_second_club_by_accident(): void
    {
        $this->neverCallPaylink();
        $tenant = $this->makeTenant();

        $this->actingAsOwnerOf($tenant)
            ->postJson('/checkout/session', [
                'plan_code' => 'pro_monthly',
                'subdomain' => 'brand-new',
                'email' => 'existing@example.com',
                'mobile' => '0501234567',
                'intent' => 'new',
            ])
            ->assertStatus(422)
            ->assertJsonPath('code', 'already_has_club');
    }

    public function test_renewal_cannot_be_redirected_to_a_different_subdomain(): void
    {
        $this->neverCallPaylink();
        $tenant = $this->makeTenant(['access_status' => 'suspended']);
        $plan = Plan::first();
        Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'current_period_end' => now()->subDays(30),
        ]);

        $this->actingAsOwnerOf($tenant)
            ->postJson('/checkout/session', [
                'plan_code' => 'pro_monthly',
                'subdomain' => 'someone-elses-name',
                'email' => 'existing@example.com',
                'mobile' => '0501234567',
                'intent' => 'renew',
            ])
            ->assertStatus(422)
            ->assertJsonPath('code', 'slug_mismatch');
    }

    public function test_renewal_requires_a_session(): void
    {
        $this->neverCallPaylink();

        $this->checkout(['intent' => 'renew'])
            ->assertStatus(401)
            ->assertJsonPath('code', 'login_required');
    }

    public function test_lapsed_customer_can_renew_without_a_reservation(): void
    {
        $this->fakePaylink();
        $tenant = $this->makeTenant(['access_status' => 'suspended']);
        Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => Plan::first()->id,
            'status' => 'active',
            'current_period_end' => now()->subDays(30),
        ]);

        $response = $this->actingAsOwnerOf($tenant)
            ->postJson('/checkout/session', [
                'plan_code' => 'pro_monthly',
                'subdomain' => 'existing',
                'email' => 'existing@example.com',
                'mobile' => '0501234567',
                'intent' => 'renew',
            ])
            ->assertOk();

        $this->assertStringStartsWith('renew-', $response->json('order_number'));
        $this->assertDatabaseMissing('signup_reservations', ['order_number' => $response->json('order_number')], 'system');
    }

    public function test_honeypot_blocks_bots(): void
    {
        $this->neverCallPaylink();

        $this->checkout(['website' => 'http://spam.example'])->assertStatus(422);
    }

    public function test_subscribe_page_renders_with_explicit_intent(): void
    {
        $this->get('/subscribe')
            ->assertOk()
            ->assertSee('name="intent" value="new"', false)
            ->assertSee('name="website"', false);
    }
}
