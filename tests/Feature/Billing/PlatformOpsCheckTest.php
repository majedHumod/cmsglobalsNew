<?php

namespace Tests\Feature\Billing;

use App\Models\Billing\Invoice;
use App\Models\Billing\SignupReservation;
use App\Models\TenantDatabasePool;
use App\Services\Platform\OpsAlert;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\Support\BillingTestCase;

class PlatformOpsCheckTest extends BillingTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        config([
            'platform.alert_emails' => ['ops@example.com'],
            'platform.signup.min_free_databases' => 2,
            'platform.signup.stuck_provision_minutes' => 15,
        ]);
    }

    public function test_healthy_system_reports_no_problems(): void
    {
        foreach (range(1, 5) as $i) {
            TenantDatabasePool::create(['db_name' => "tenant_pool_{$i}", 'status' => 'available', 'is_ready' => true]);
        }

        $this->artisan('platform:ops-check')
            ->expectsOutputToContain('Ops check passed')
            ->assertSuccessful();
    }

    public function test_low_pool_is_reported(): void
    {
        TenantDatabasePool::create(['db_name' => 'tenant_pool_1', 'status' => 'available', 'is_ready' => true]);

        $this->artisan('platform:ops-check --no-alert')
            ->expectsOutputToContain('على وشك النفاد')
            ->assertSuccessful();
    }

    public function test_paid_signup_without_a_tenant_is_reported_as_stuck(): void
    {
        foreach (range(1, 5) as $i) {
            TenantDatabasePool::create(['db_name' => "tenant_pool_{$i}", 'status' => 'available', 'is_ready' => true]);
        }

        $invoice = Invoice::create([
            'number' => 'signup-stuck-1',
            'provider_invoice_id' => 'TX-STUCK',
            'amount_due' => 99,
            'status' => 'paid',
        ]);
        Invoice::query()->whereKey($invoice->id)->update(['updated_at' => now()->subHour()]);

        $this->artisan('platform:ops-check --no-alert')
            ->expectsOutputToContain('signup-stuck-1')
            ->assertSuccessful();
    }

    public function test_conflicts_are_reported_but_not_as_stuck(): void
    {
        foreach (range(1, 5) as $i) {
            TenantDatabasePool::create(['db_name' => "tenant_pool_{$i}", 'status' => 'available', 'is_ready' => true]);
        }

        $invoice = Invoice::create(['number' => 'signup-conflict-1', 'provider_invoice_id' => 'TX-C', 'amount_due' => 99, 'status' => 'paid']);
        Invoice::query()->whereKey($invoice->id)->update(['updated_at' => now()->subHour()]);
        SignupReservation::create([
            'order_number' => 'signup-conflict-1',
            'slug' => 'taken',
            'email' => 'a@example.com',
            'status' => SignupReservation::CONFLICT,
        ]);

        $this->artisan('platform:ops-check --no-alert')
            ->expectsOutputToContain('دفعات اشتراك بحاجة لمعالجة يدوية')
            ->doesntExpectOutputToContain('اشتراكات مدفوعة لم يتم تجهيزها')
            ->assertSuccessful();
    }

    public function test_ops_alert_goes_to_alert_emails_and_is_deduplicated(): void
    {
        $this->assertSame(['ops@example.com'], OpsAlert::recipients());

        config(['platform.alert_emails' => [], 'platform.owner_emails' => ['owner@example.com']]);
        $this->assertSame(['owner@example.com'], OpsAlert::recipients());

        $this->assertFalse(Cache::has('ops-alert:same-key'));
        OpsAlert::critical('subject', 'body', 'same-key', 60);
        $this->assertTrue(Cache::has('ops-alert:same-key'));
    }
}
