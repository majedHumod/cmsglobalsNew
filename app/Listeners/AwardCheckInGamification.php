<?php

namespace App\Listeners;

use App\Events\CheckInSubmitted;
use App\Services\GamificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class AwardCheckInGamification implements ShouldQueue
{
    public function __construct(private readonly GamificationService $gamificationService)
    {
    }

    public function handle(CheckInSubmitted $event): void
    {
        $this->gamificationService->evaluateCheckInProgress($event->checkIn);
    }
}
