<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Reset demo tenant daily at 3 AM
        $schedule->command('tenants:reset-demo')->dailyAt('03:00');
        $schedule->command('billing:reconcile-paylink --limit=20')->everyMinute()->withoutOverlapping();
        $schedule->command('notifications:evaluate-rules --limit=300')->hourly()->withoutOverlapping();
        $schedule->command('memberships:expire-stale')->dailyAt('02:00')->withoutOverlapping();
        $schedule->command('engagement:follow-up-inactive-clients --days=7 --limit=300')->twiceDaily(9, 18)->withoutOverlapping();
        $schedule->command('system:health-check --fail-on-warning')->hourly()->withoutOverlapping();
        $schedule->command('performance:baseline --json')->dailyAt('02:30')->withoutOverlapping();
        $schedule->command('security:check-secrets --fail-on-missing')->dailyAt('01:30');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}