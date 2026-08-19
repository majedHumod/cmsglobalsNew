<?php

namespace App\Console\Commands;

use App\Jobs\SendInactiveClientFollowUpJob;
use App\Models\User;
use Illuminate\Console\Command;

class RunFollowUpCampaignsCommand extends Command
{
    protected $signature = 'engagement:follow-up-inactive-clients {--days=7} {--limit=300}';

    protected $description = 'Dispatch follow-up notifications for inactive clients';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $limit = max(1, (int) $this->option('limit'));
        $campaignKey = 'inactive_clients_' . $days . 'd';

        $users = User::query()
            ->clients()
            ->whereDoesntHave('progressCheckIns', fn ($query) => $query->where('checked_in_at', '>=', now()->subDays($days)))
            ->limit($limit)
            ->pluck('id');

        foreach ($users as $userId) {
            SendInactiveClientFollowUpJob::dispatch((int) $userId, $campaignKey);
        }

        $this->info("Dispatched {$users->count()} follow-up jobs.");
        return self::SUCCESS;
    }
}
