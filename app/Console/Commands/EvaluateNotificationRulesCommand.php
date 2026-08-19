<?php

namespace App\Console\Commands;

use App\Jobs\EvaluateClientSignalsJob;
use App\Models\User;
use Illuminate\Console\Command;

class EvaluateNotificationRulesCommand extends Command
{
    protected $signature = 'notifications:evaluate-rules {--limit=200}';

    protected $description = 'Evaluate notification automation rules for clients';

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));

        User::query()
            ->clients()
            ->limit($limit)
            ->pluck('id')
            ->each(fn ($id) => EvaluateClientSignalsJob::dispatch((int) $id));

        $this->info('Notification rules evaluation jobs dispatched.');
        return self::SUCCESS;
    }
}
