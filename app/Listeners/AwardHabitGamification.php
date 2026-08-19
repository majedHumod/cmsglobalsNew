<?php

namespace App\Listeners;

use App\Events\HabitLogRecorded;
use App\Services\GamificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class AwardHabitGamification implements ShouldQueue
{
    public function __construct(private readonly GamificationService $gamificationService)
    {
    }

    public function handle(HabitLogRecorded $event): void
    {
        $this->gamificationService->evaluateHabitProgress($event->habit);
    }
}
