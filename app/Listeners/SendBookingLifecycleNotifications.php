<?php

namespace App\Listeners;

use App\Events\BookingLifecycleChanged;
use App\Services\NotificationRuleEngine;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendBookingLifecycleNotifications implements ShouldQueue
{
    public function __construct(private readonly NotificationRuleEngine $ruleEngine)
    {
    }

    public function handle(BookingLifecycleChanged $event): void
    {
        $this->ruleEngine->processBookingStatus($event->booking->loadMissing('trainingSession'), $event->action);
    }
}
