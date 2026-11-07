<?php

namespace Tests\Support;

use App\Models\Billing\Plan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Runs billing/signup tests against an isolated in-memory SQLite "system" database,
 * so they never touch (or depend on) the real MySQL system database.
 */
abstract class BillingTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.connections.system' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
            'app.domain' => 'etoscoach.com',
            'platform.signup.verify_email' => false,
            'platform.signup.unique_mobile' => false,
            'platform.signup.require_free_database' => false,
            'platform.signup.default_country_code' => '966',
            'services.turnstile.secret_key' => '',
            'services.turnstile.site_key' => '',
            'services.paylink.webhook_secret' => '',
        ]);
        DB::purge('system');

        $this->createSystemSchema();

        Plan::create([
            'code' => 'pro_monthly',
            'name' => 'Pro',
            'price' => 99,
            'interval' => 'monthly',
            'currency' => 'SAR',
            'features' => ['a'],
            'active' => true,
        ]);
    }

    protected function createSystemSchema(): void
    {
        $schema = Schema::connection('system');

        $schema->create('tenants', function ($t) {
            $t->id();
            $t->string('name')->nullable();
            $t->string('slug')->nullable();
            $t->string('join_code')->nullable();
            $t->string('domain')->nullable();
            $t->string('subdomain')->nullable();
            $t->string('email')->nullable();
            $t->string('phone')->nullable();
            $t->string('logo')->nullable();
            $t->string('status')->nullable();
            $t->string('access_status')->nullable();
            $t->string('database_status')->nullable();
            $t->string('schema_status')->nullable();
            $t->string('recommended_action')->nullable();
            $t->string('status_note')->nullable();
            $t->timestamp('last_audited_at')->nullable();
            $t->timestamp('trial_ends_at')->nullable();
            $t->timestamp('subscription_ends_at')->nullable();
            $t->timestamp('grace_ends_at')->nullable();
            $t->timestamp('suspended_at')->nullable();
            $t->timestamp('archived_at')->nullable();
            $t->string('db_name')->nullable();
            $t->timestamps();
        });

        $schema->create('plans', function ($t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('name');
            $t->decimal('price', 10, 2)->default(0);
            $t->string('interval')->default('monthly');
            $t->string('currency', 10)->default('SAR');
            $t->json('features')->nullable();
            $t->boolean('active')->default(true);
            $t->timestamps();
        });

        $schema->create('subscriptions', function ($t) {
            $t->id();
            $t->unsignedBigInteger('tenant_id')->index();
            $t->unsignedBigInteger('plan_id')->index();
            $t->string('provider')->nullable();
            $t->string('provider_customer_id')->nullable();
            $t->string('provider_subscription_id')->nullable();
            $t->string('status')->default('active');
            $t->timestamp('current_period_start')->nullable();
            $t->timestamp('current_period_end')->nullable();
            $t->boolean('cancel_at_period_end')->default(false);
            $t->timestamp('trial_ends_at')->nullable();
            $t->timestamps();
        });

        $schema->create('invoices', function ($t) {
            $t->id();
            $t->unsignedBigInteger('tenant_id')->nullable()->index();
            $t->string('number')->nullable();
            $t->string('provider_invoice_id')->nullable();
            $t->decimal('amount_due', 10, 2)->default(0);
            $t->decimal('amount_paid', 10, 2)->default(0);
            $t->decimal('tax_amount', 10, 2)->default(0);
            $t->decimal('discount_amount', 10, 2)->default(0);
            $t->string('currency', 10)->default('SAR');
            $t->string('status')->default('draft');
            $t->string('hosted_invoice_url')->nullable();
            $t->string('invoice_pdf_url')->nullable();
            $t->timestamp('period_start')->nullable();
            $t->timestamp('period_end')->nullable();
            $t->json('line_items')->nullable();
            $t->timestamps();
        });

        $schema->create('payments', function ($t) {
            $t->id();
            $t->unsignedBigInteger('tenant_id')->nullable()->index();
            $t->string('provider_payment_intent_id')->nullable();
            $t->decimal('amount', 10, 2)->default(0);
            $t->string('currency', 10)->default('SAR');
            $t->string('status')->default('pending');
            $t->timestamp('paid_at')->nullable();
            $t->json('method_details')->nullable();
            $t->string('receipt_url')->nullable();
            $t->timestamps();
        });

        $schema->create('billing_contacts', function ($t) {
            $t->id();
            $t->unsignedBigInteger('tenant_id')->unique();
            $t->string('email')->nullable();
            $t->string('name')->nullable();
            $t->json('address')->nullable();
            $t->string('tax_id')->nullable();
            $t->timestamps();
        });

        $schema->create('events', function ($t) {
            $t->id();
            $t->unsignedBigInteger('tenant_id')->nullable()->index();
            $t->string('provider_event_id')->nullable()->unique();
            $t->string('type')->nullable();
            $t->json('payload')->nullable();
            $t->timestamp('processed_at')->nullable();
            $t->timestamps();
        });

        $schema->create('tenant_database_pools', function ($t) {
            $t->id();
            $t->string('db_name')->unique();
            $t->string('label')->nullable();
            $t->string('status')->default('available');
            $t->boolean('is_ready')->default(false);
            $t->unsignedBigInteger('tenant_id')->nullable();
            $t->timestamp('allocated_at')->nullable();
            $t->timestamps();
        });

        // The real migration, so its unique indexes are exercised too.
        $migration = require base_path('database/migrations/system/2026_11_07_000001_create_signup_reservations_table.php');
        $migration->up();
    }
}
