<?php

namespace App\Listeners;

use App\Events\HabitLogRecorded;
use App\Services\NotificationRuleEngine;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendHabitLogNotifications implements ShouldQueue
{
    public function __construct(private readonly NotificationRuleEngine $ruleEngine)
    {
    }

    public function handle(HabitLogRecorded $event): void
    {
        $this->ruleEngine->processHabitLog($event->habit, $event->actor);
    }
}
