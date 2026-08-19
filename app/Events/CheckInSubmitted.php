<?php

namespace App\Events;

use App\Models\ProgressCheckIn;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CheckInSubmitted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public ProgressCheckIn $checkIn)
    {
    }
}
