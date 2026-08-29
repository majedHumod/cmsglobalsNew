<?php

namespace App\Console\Commands;

use App\Mail\WelcomeTenantMail;
use App\Models\Billing\Plan;
use App\Models\Billing\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class ResendWelcomeMailCommand extends Command
{
    protected $signature = 'tenants:resend-welcome
                            {slug : Tenant subdomain e.g. mjd}
                            {--email= : Override recipient email}';

    protected $description = 'Resend the Arabic welcome email with a fresh password reset link';

    public function handle(): int
    {
        $slug = strtolower(trim((string) $this->argument('slug')));
        $tenant = Tenant::on('system')
            ->where('subdomain', $slug)
            ->orWhere('domain', $slug.'.'.config('app.domain', 'etoscoach.com'))
            ->first();

        if (! $tenant) {
            $this->error("Tenant not found: {$slug}");

            return self::FAILURE;
        }

        $email = strtolower(trim((string) ($this->option('email') ?: $tenant->email)));
        if ($email === '') {
            $this->error('No recipient email found. Pass --email=');

            return self::FAILURE;
        }

        TenantService::switchToTenant($tenant);

        try {
            $user = User::query()->where('email', $email)->first();
            if (! $user) {
                $this->error("No user with email {$email} on tenant DB.");

                return self::FAILURE;
            }

            $token = Password::broker()->createToken($user);
            $resetPath = route('password.reset', ['token' => $token, 'email' => $user->email], false);
            $isLocal = app()->environment('local');
            $tenantBase = ($isLocal ? 'http://' : 'https://').$tenant->domain.($isLocal ? ':8000' : '');
            $resetUrl = rtrim($tenantBase, '/').$resetPath;

            $subscription = Subscription::query()
                ->where('tenant_id', $tenant->id)
                ->where('status', 'active')
                ->latest('id')
                ->first();
            $plan = $subscription ? Plan::query()->find($subscription->plan_id) : null;

            Mail::to($email)->send(new WelcomeTenantMail(
                tenantName: (string) ($tenant->name ?: $slug),
                tenantDomainUrl: rtrim($tenantBase, '/'),
                contactEmail: $email,
                plan: $plan?->only(['code', 'name', 'price', 'interval', 'currency']),
                passwordResetUrl: $resetUrl,
                contactName: (string) $user->name,
            ));
        } finally {
            TenantService::switchToDefault();
        }

        $this->info("Welcome email sent to {$email} for {$tenant->domain}");

        return self::SUCCESS;
    }
}
