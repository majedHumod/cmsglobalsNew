<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\Platform\TenantAccessService;
use Illuminate\Console\Command;

class SyncTenantAccessCommand extends Command
{
    protected $signature = 'tenants:sync-access';

    protected $description = 'Refresh tenant access_status from subscriptions without deleting any content';

    public function handle(TenantAccessService $access): int
    {
        $counts = ['active' => 0, 'grace' => 0, 'suspended' => 0, 'archived' => 0];

        Tenant::on('system')->orderBy('id')->each(function (Tenant $tenant) use ($access, &$counts) {
            $access->sync($tenant);
            $status = $tenant->access_status ?: 'active';
            $counts[$status] = ($counts[$status] ?? 0) + 1;
        });

        $this->info('Access sync complete (content is never deleted).');
        foreach ($counts as $status => $count) {
            $this->line(" - {$status}: {$count}");
        }

        return self::SUCCESS;
    }
}
