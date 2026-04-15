<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Plans offered by the platform
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g., subdomain_basic, subdomain_pro
            $table->string('name');
            $table->decimal('price', 10, 2)->default(0);
            $table->enum('interval', ['monthly', 'yearly'])->default('monthly');
            $table->string('currency', 10)->default('SAR');
            $table->json('features')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Subscriptions for each tenant
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('plan_id')->index();
            $table->string('provider')->nullable(); // stripe/paddle/...
            $table->string('provider_customer_id')->nullable();
            $table->string('provider_subscription_id')->nullable();
            $table->string('status')->default('active'); // active, trialing, past_due, canceled...
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->boolean('cancel_at_period_end')->default(false);
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
        });

        // Invoices per tenant
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('number')->nullable();
            $table->string('provider_invoice_id')->nullable();
            $table->decimal('amount_due', 10, 2)->default(0);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->string('currency', 10)->default('SAR');
            $table->string('status')->default('draft'); // draft, open, paid, uncollectible, void
            $table->string('hosted_invoice_url')->nullable();
            $table->string('invoice_pdf_url')->nullable();
            $table->timestamp('period_start')->nullable();
            $table->timestamp('period_end')->nullable();
            $table->json('line_items')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
        });

        // Payments per tenant
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('provider_payment_intent_id')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('currency', 10)->default('SAR');
            $table->string('status')->default('pending'); // pending, succeeded, failed, refunded
            $table->timestamp('paid_at')->nullable();
            $table->json('method_details')->nullable();
            $table->string('receipt_url')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
        });

        // Billing contacts (optional)
        Schema::create('billing_contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->unique();
            $table->string('email')->nullable();
            $table->string('name')->nullable();
            $table->json('address')->nullable();
            $table->string('tax_id')->nullable();
            $table->timestamps();
        });

        // Provider events (webhooks) for auditing/idempotency
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->string('provider_event_id')->nullable()->unique();
            $table->string('type')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
        Schema::dropIfExists('billing_contacts');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plans');
    }
};

