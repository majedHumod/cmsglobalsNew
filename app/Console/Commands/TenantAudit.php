<?php

namespace App\Console\Commands;

use App\Services\Tenant\TenantAuditService;
use Illuminate\Console\Command;

class TenantAudit extends Command
{
    protected $signature = 'tenants:audit
                            {--sync-system : Persist audit markers into system.tenants}
                            {--only-issues : Show only tenants with missing or incomplete states}';

    protected $description = 'Audit tenant rows against actual tenant databases and schema readiness';

    public function handle(TenantAuditService $auditService): int
    {
        try {
            $audits = $auditService->auditAll();

            if ($this->option('sync-system')) {
                $auditService->syncAuditColumns($audits);
                $this->info('Synced audit markers into system.tenants.');
            }

            $rows = $audits
                ->when(
                    $this->option('only-issues'),
                    fn ($collection) => $collection->filter(
                        fn (array $audit) => $audit['database_status'] !== 'present' || $audit['schema_status'] !== 'ready'
                    )
                )
                ->map(fn (array $audit) => [
                    'domain' => $audit['domain'],
                    'db_name' => $audit['db_name'],
                    'status' => $audit['tenant_status'],
                    'database' => $audit['database_status'],
                    'schema' => $audit['schema_status'],
                    'action' => $audit['recommended_action'],
                ])
                ->values()
                ->all();

            if ($rows === []) {
                $this->info('No tenant issues found.');
            } else {
                $this->table(
                    ['Domain', 'Database', 'Tenant Status', 'DB State', 'Schema State', 'Recommended Action'],
                    $rows
                );
            }

            $summary = $auditService->summarize($audits);
            $this->newLine();
            $this->line('Summary:');
            $this->line(" - total tenants: {$summary['total']}");
            $this->line(" - databases present: {$summary['present']}");
            $this->line(" - databases missing: {$summary['missing']}");
            $this->line(" - schema ready: {$summary['ready']}");
            $this->line(" - schema partial: {$summary['partial']}");
            $this->line(" - schema unreachable: {$summary['unreachable']}");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Tenant audit failed: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
