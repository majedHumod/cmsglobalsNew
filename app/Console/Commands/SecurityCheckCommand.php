<?php

namespace App\Console\Commands;

use App\Services\Platform\OpsAlert;
use Illuminate\Console\Command;

class SecurityCheckCommand extends Command
{
    protected $signature = 'security:check-secrets {--fail-on-missing}';

    protected $description = 'Validate critical security-related environment secrets';

    public function handle(): int
    {
        $required = [
            'APP_KEY',
            'STRIPE_SECRET_KEY',
            'VAPID_PUBLIC_KEY',
        ];

        $missing = [];
        foreach ($required as $key) {
            if (trim((string) env($key, '')) === '') {
                $missing[] = $key;
            }
        }

        if (app()->isProduction() && trim((string) config('services.communication_webhook.secret', '')) === '') {
            $missing[] = 'COMMUNICATION_WEBHOOK_SECRET (required in production only)';
        }

        if (app()->isProduction()) {
            if (trim((string) config('services.paylink.webhook_secret', '')) === '') {
                $missing[] = 'PAYLINK_WEBHOOK_SECRET (required in production only)';
            }
            if (trim((string) config('services.paylink.api_id', '')) === '' || trim((string) config('services.paylink.secret_key', '')) === '') {
                $missing[] = 'PAYLINK_API_ID / PAYLINK_SECRET_KEY (required in production only)';
            }
            if ((bool) config('app.debug')) {
                $missing[] = 'APP_DEBUG must be false in production';
            }
            if (OpsAlert::recipients() === []) {
                $missing[] = 'PLATFORM_ALERT_EMAILS or PLATFORM_OWNER_EMAILS (needed to receive ops alerts)';
            }
            if (trim((string) config('services.turnstile.secret_key', '')) === '' && ! config('platform.signup.verify_email')) {
                $missing[] = 'TURNSTILE_SECRET_KEY (or enable PLATFORM_SIGNUP_VERIFY_EMAIL) to protect /subscribe from bots';
            }
        }

        if ($missing === []) {
            if (! app()->isProduction() && trim((string) config('services.communication_webhook.secret', '')) === '') {
                $this->info('Security check passed. COMMUNICATION_WEBHOOK_SECRET is optional outside production.');
            } else {
                $this->info('Security check passed: all required secrets are configured.');
            }
            return self::SUCCESS;
        }

        $this->warn('Missing security secrets:');
        foreach ($missing as $key) {
            $this->line("- {$key}");
        }

        if ((bool) $this->option('fail-on-missing')) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
