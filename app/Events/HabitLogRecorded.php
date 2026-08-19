<?php

namespace App\Events;

use App\Models\Habit;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HabitLogRecorded
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Habit $habit, public User $actor)
    {
    }
}
