<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\NotificationRuleEngine;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class EvaluateClientSignalsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly int $clientId)
    {
    }

    public function handle(NotificationRuleEngine $ruleEngine): void
    {
        $client = User::query()->find($this->clientId);
        if (! $client || ! $client->hasAnyRole(['user', 'client'])) {
            return;
        }

        $ruleEngine->evaluateClientSignals($client);
    }
}
