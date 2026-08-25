<?php

use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('tenants:reset-demo')->dailyAt('03:00');
Schedule::command('tenants:sync-access')->dailyAt('04:15')->withoutOverlapping();
Schedule::command('billing:reconcile-paylink --limit=20')->everyMinute()->withoutOverlapping();
Schedule::command('notifications:evaluate-rules --limit=300')->hourly()->withoutOverlapping();
Schedule::command('engagement:follow-up-inactive-clients --days=7 --limit=300')->twiceDaily(9, 18)->withoutOverlapping();
Schedule::command('system:health-check --fail-on-warning')->hourly()->withoutOverlapping();
Schedule::command('performance:baseline --json')->dailyAt('02:30')->withoutOverlapping();
Schedule::command('security:check-secrets --fail-on-missing')->dailyAt('01:30');
