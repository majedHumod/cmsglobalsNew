<?php

namespace App\Services\Tenant;

use App\Models\Billing\BillingContact;
use App\Models\Billing\Invoice;
use App\Models\Billing\Payment;
use App\Models\Billing\Subscription;
use App\Models\Billing\Plan;
use App\Models\Tenant;
use App\Models\TenantDatabasePool;
use App\Services\TenantService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Mail\WelcomeTenantMail;

class TenantProvisioner
{
    /**
     * Provision a new subdomain tenant:
     * - Allocate a prepared tenant DB from the system pool
     * - Optionally run tenant migrations + seeders when the DB is not marked ready
     * - Create subscription + billing contact on system
     */
    public function provisionSubdomainTenant(
        string $slug,
        string $planCode,
        string $contactEmail,
        ?string $contactName = null,
        ?string $provider = null,
        ?string $providerInvoiceId = null,
        ?string $providerOrderNumber = null
    ): Tenant
    {
        $domainRoot = config('app.domain', 'yourdomain.com');
        $domain = "{$slug}.{$domainRoot}";
        [$tenant, $dbName, $shouldPrepareDatabase] = DB::connection('system')->transaction(function () use ($domain, $slug, $contactEmail, $contactName) {
            $tenant = Tenant::on('system')->where('domain', $domain)->lockForUpdate()->first();

            if ($tenant && $tenant->db_name) {
                TenantDatabasePool::on('system')
                    ->where('db_name', $tenant->db_name)
                    ->update([
                        'status' => TenantDatabasePool::STATUS_ALLOCATED,
                        'tenant_id' => $tenant->id,
                        'allocated_at' => now(),
                    ]);

                return [$tenant, $tenant->db_name, false];
            }

            $poolDb = TenantDatabasePool::query()
                ->available()
                ->whereNotIn('db_name', function ($query) {
                    $query->select('db_name')
                        ->from('tenants')
                        ->whereNotNull('db_name');
                })
                ->lockForUpdate()
                ->first();

            if (!$poolDb) {
                throw new \RuntimeException('No available tenant databases in the pool. Register a prepared tenant database first.');
            }

            if (!$tenant) {
                $tenant = new Tenant();
                $tenant->setConnection('system');
            } else {
                $tenant->setConnection('system');
            }

            $tenant->name = $tenant->name ?: ($contactName ?: Str::title(str_replace('-', ' ', $slug)));
            $tenant->slug = $slug;
            $tenant->domain = $domain;
            $tenant->subdomain = $slug;
            $tenant->email = $contactEmail;
            $tenant->status = 'active';
            $tenant->db_name = $poolDb->db_name;
            $tenant->save();

            $poolDb->status = TenantDatabasePool::STATUS_ALLOCATED;
            $poolDb->tenant_id = $tenant->id;
            $poolDb->allocated_at = now();
            $poolDb->save();

            return [$tenant, $poolDb->db_name, !$poolDb->is_ready];
        });

        // Clear any stale domain-level cache before reading tenant content.
        Cache::forget("{$domain}:{$dbName}:active_landing_page");
        Cache::forget("{$domain}:{$dbName}:active_landing_page_full");

        try {
            // 3) Switch to tenant and bring schema up to date.
            TenantService::switchToTenant($tenant);

            // Even "ready" pooled databases can lag behind new tenant migrations.
            Artisan::call('migrate', [
                '--path' => 'database/migrations/tenants/',
                '--database' => 'tenant',
                '--force' => true,
            ]);

            if ($shouldPrepareDatabase) {
                // Seed base data (creates default landing page active)
                Artisan::call('db:seed', [
                    '--class' => 'Database\\Seeders\\Tenants\\BaseTenantSeeder',
                    '--database' => 'tenant',
                    '--force' => true,
                ]);

                TenantDatabasePool::on('system')
                    ->where('db_name', $dbName)
                    ->update(['is_ready' => true]);
            }

            // 4) Create or link the primary admin user on tenant DB using contact email
            $userModel = \App\Models\User::query();
            $user = $userModel->where('email', $contactEmail)->first();
            if (!$user) {
                $user = $userModel->create([
                    'name' => $contactName ?: Str::title(str_replace('-', ' ', $slug)),
                    'email' => $contactEmail,
                    // set random strong password; user will set real password using reset link
                    'password' => Hash::make(Str::random(40)),
                ]);
            }
            // assign admin role if available
            if (method_exists($user, 'assignRole')) {
                try { $user->assignRole('admin'); } catch (\Throwable $e) { /* ignore if roles not ready */ }
            }

            // Always ensure starter public content exists for the tenant.
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Tenants\\DefaultTenantContentSeeder',
                '--database' => 'tenant',
                '--force' => true,
            ]);

            // generate password reset url on tenant domain
            $token = Password::broker()->createToken($user);
            $resetPath = route('password.reset', ['token' => $token, 'email' => $user->email], false);
            $isLocal = app()->environment('local');
            $tenantBase = ($isLocal ? 'http://' : 'https://') . $tenant->domain . ($isLocal ? ':8000' : '');
            $resetUrl = rtrim($tenantBase, '/') . $resetPath;
            $tenantUrl = $tenantBase;
        } catch (\Throwable $e) {
            // If something fails during tenant user creation, fallback to default connection and continue
            $resetUrl = null;
            $tenantUrl = ($this->guessScheme()) . $tenant->domain . ($this->isLocal() ? ':8000' : '');
        } finally {
            TenantService::switchToDefault();
        }

        // 5) Create subscription record on system
        $plan = Plan::where('code', $planCode)->first();
        if ($plan) {
            Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'provider' => $provider,
                'provider_customer_id' => $contactEmail,
                'provider_subscription_id' => $providerOrderNumber,
                'status' => 'active',
                'current_period_start' => now(),
                'current_period_end' => $this->resolvePeriodEnd($plan),
                'cancel_at_period_end' => false,
                'trial_ends_at' => null,
            ]);
        }

        // 6) Create/update billing contact
        BillingContact::updateOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'email' => $contactEmail,
                'name' => $contactName,
                'address' => null,
                'tax_id' => null,
            ]
        );

        if ($providerInvoiceId || $providerOrderNumber) {
            $invoiceQuery = Invoice::query()->where(function ($query) use ($providerInvoiceId, $providerOrderNumber) {
                if ($providerInvoiceId) {
                    $query->where('provider_invoice_id', $providerInvoiceId);
                }

                if ($providerOrderNumber) {
                    $method = $providerInvoiceId ? 'orWhere' : 'where';
                    $query->{$method}('number', $providerOrderNumber);
                }
            });

            $paymentQuery = Payment::query()->where(function ($query) use ($providerInvoiceId, $providerOrderNumber) {
                if ($providerInvoiceId) {
                    $query->where('provider_payment_intent_id', $providerInvoiceId);
                }

                if ($providerOrderNumber) {
                    $method = $providerInvoiceId ? 'orWhere' : 'where';
                    $query->{$method}('method_details->order_number', $providerOrderNumber);
                }
            });

            $invoice = $invoiceQuery->first();
            if ($invoice) {
                $invoice->tenant_id = $tenant->id;
                $invoice->status = 'paid';
                $invoice->amount_paid = $invoice->amount_due;
                $invoice->save();
            }

            $payment = $paymentQuery->first();
            if ($payment) {
                $payment->tenant_id = $tenant->id;
                $payment->status = 'succeeded';
                $payment->paid_at = $payment->paid_at ?: now();
                $payment->save();
            }
        }

        // 7) Send welcome email with password reset link (queued)
        try {
            $planArr = $plan ? $plan->only(['code','name','price','interval','currency']) : null;
            Mail::to($contactEmail)->queue(new WelcomeTenantMail(
                tenantName: $tenant->name ?? $slug,
                tenantDomainUrl: $tenantUrl ?? '',
                contactEmail: $contactEmail,
                plan: $planArr,
                passwordResetUrl: $resetUrl
            ));
        } catch (\Throwable $e) {
            // swallow mail errors to not interrupt provisioning
        }

        return $tenant;
    }

    private function isLocal(): bool
    {
        return app()->environment('local');
    }

    private function guessScheme(): string
    {
        return $this->isLocal() ? 'http://' : 'https://';
    }

    private function resolvePeriodEnd(Plan $plan)
    {
        return $plan->interval === 'yearly'
            ? now()->addYear()
            : now()->addMonth();
    }
}

