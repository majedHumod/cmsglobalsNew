<?php

namespace App\Console\Commands;

use App\Services\Tenant\TenantAuditService;
use Illuminate\Console\Command;

class TenantPreflightCommand extends Command
{
    protected $signature = 'tenant:preflight {--fail-on-issue}';

    protected $description = 'Run tenant database/schema preflight checks before bulk migrations';

    public function handle(TenantAuditService $auditService): int
    {
        $audits = $auditService->auditAll();
        $summary = $auditService->summarize($audits);

        $this->info('Tenant preflight summary:');
        $this->line('- total: ' . $summary['total']);
        $this->line('- db present: ' . $summary['present']);
        $this->line('- db missing: ' . $summary['missing']);
        $this->line('- schema ready: ' . $summary['ready']);
        $this->line('- schema partial: ' . $summary['partial']);
        $this->line('- schema unreachable: ' . $summary['unreachable']);

        $problematic = $audits->filter(function (array $audit) {
            return $audit['database_status'] !== 'present' || in_array($audit['schema_status'], ['incomplete', 'unreachable'], true);
        });

        if ($problematic->isNotEmpty()) {
            $this->warn("\nProblematic tenants:");
            foreach ($problematic as $issue) {
                $this->line(sprintf(
                    '- %s (%s): %s | action=%s',
                    $issue['name'],
                    $issue['domain'],
                    $issue['status_note'],
                    $issue['recommended_action']
                ));
            }

            if ((bool) $this->option('fail-on-issue')) {
                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }
}
