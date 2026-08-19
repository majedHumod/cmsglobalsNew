<?php

namespace App\Events;

use App\Models\SessionBooking;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingLifecycleChanged
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public SessionBooking $booking, public string $action)
    {
    }
}
