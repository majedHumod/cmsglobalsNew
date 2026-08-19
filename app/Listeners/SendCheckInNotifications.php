<?php

namespace App\Listeners;

use App\Events\CheckInSubmitted;
use App\Services\NotificationRuleEngine;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendCheckInNotifications implements ShouldQueue
{
    public function __construct(private readonly NotificationRuleEngine $ruleEngine)
    {
    }

    public function handle(CheckInSubmitted $event): void
    {
        $this->ruleEngine->processCheckIn($event->checkIn);
    }
}
