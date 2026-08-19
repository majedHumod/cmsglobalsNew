<?php

namespace App\Listeners;

use App\Events\MembershipLifecycleChanged;
use App\Services\NotificationRuleEngine;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendMembershipLifecycleNotifications implements ShouldQueue
{
    public function __construct(private readonly NotificationRuleEngine $ruleEngine)
    {
    }

    public function handle(MembershipLifecycleChanged $event): void
    {
        $this->ruleEngine->processMembership($event->membership);
    }
}
