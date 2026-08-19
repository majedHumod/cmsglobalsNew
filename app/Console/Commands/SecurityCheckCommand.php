<?php

namespace App\Console\Commands;

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
