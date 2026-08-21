<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\TenantDatabasePool;
use App\Services\Tenant\TenantAuditService;
use App\Services\TenantService;
use App\Support\MigrationScope;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Throwable;

/**
 * Phase B: bring existing tenant / pool schemas up to date without wiping data.
 */
class SyncTenantSchemasCommand extends Command
{
    protected $signature = 'tenants:sync-schemas
                            {--allocated : Migrate all allocated tenant databases}
                            {--pool-available : Migrate available pool databases (schema only)}
                            {--fail-on-issue : Abort on first failure}';

    protected $description = 'Run pending tenant migrations on allocated tenants and/or available pool DBs (no data wipe)';

    public function handle(TenantAuditService $auditService): int
    {
        $doAllocated = (bool) $this->option('allocated');
        $doPool = (bool) $this->option('pool-available');

        if (! $doAllocated && ! $doPool) {
            $doAllocated = true;
            $doPool = true;
            $this->info('No scope flag given — syncing allocated tenants and available pool DBs.');
        }

        $failOnIssue = (bool) $this->option('fail-on-issue');
        $ok = 0;
        $failed = 0;
        $skipped = 0;

        if ($doAllocated) {
            $this->info('Syncing allocated tenant schemas…');
            $audits = $auditService->auditAll();

            foreach ($audits as $audit) {
                $domain = $audit['domain'];
                if ($audit['database_status'] !== 'present') {
                    $this->warn("Skip {$domain}: {$audit['status_note']}");
                    $skipped++;
                    continue;
                }

                $tenant = Tenant::on('system')->where('domain', $domain)->first();
                if (! $tenant) {
                    $skipped++;
                    continue;
                }

                try {
                    TenantService::switchToTenant($tenant);
                    Artisan::call('migrate', [
                        '--database' => 'tenant',
                        '--path' => MigrationScope::tenant(),
                        '--force' => true,
                    ]);
                    $out = trim(Artisan::output());
                    $this->line($out !== '' ? "{$domain}: {$out}" : "{$domain}: Nothing to migrate.");
                    TenantService::switchToDefault();
                    $ok++;
                } catch (Throwable $e) {
                    $failed++;
                    $this->error("{$domain}: ".$e->getMessage());
                    TenantService::switchToDefault();
                    if ($failOnIssue) {
                        return self::FAILURE;
                    }
                }
            }
        }

        if ($doPool) {
            $this->info('Syncing available pool schemas…');
            $poolDbs = TenantDatabasePool::query()->available()->orderBy('id')->get();

            foreach ($poolDbs as $pool) {
                try {
                    TenantService::switchToDatabase($pool->db_name);
                    Artisan::call('migrate', [
                        '--database' => 'tenant',
                        '--path' => MigrationScope::tenant(),
                        '--force' => true,
                    ]);
                    $out = trim(Artisan::output());
                    $this->line($out !== '' ? "{$pool->db_name}: {$out}" : "{$pool->db_name}: Nothing to migrate.");
                    TenantService::switchToDefault();
                    $ok++;
                } catch (Throwable $e) {
                    $failed++;
                    $this->error("{$pool->db_name}: ".$e->getMessage());
                    try {
                        TenantService::switchToDefault();
                    } catch (Throwable) {
                        // ignore
                    }
                    if ($failOnIssue) {
                        return self::FAILURE;
                    }
                }
            }
        }

        $this->newLine();
        $this->info("Schema sync finished. OK={$ok} Failed={$failed} Skipped={$skipped}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
