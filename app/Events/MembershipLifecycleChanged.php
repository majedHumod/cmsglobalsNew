<?php

namespace App\Events;

use App\Models\UserMembership;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MembershipLifecycleChanged
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public UserMembership $membership)
    {
    }
}
