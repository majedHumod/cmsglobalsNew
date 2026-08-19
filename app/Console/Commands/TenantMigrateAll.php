<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\Tenant\TenantAuditService;
use App\Services\Tenant\TenantDefaultContentService;
use App\Support\MigrationScope;
use App\Services\TenantService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class TenantMigrateAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:migrate-all {--path=} {--rollback} {--fail-on-issue} {--skip-preflight}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migrations for all tenants';

    /**
     * Execute the console command.
     */
    public function handle(TenantAuditService $auditService, TenantDefaultContentService $defaultContentService)
    {
        $rollback = $this->option('rollback');
        
        try {
            $migrationPath = MigrationScope::tenant($this->option('path'));
            $tenants = Tenant::query()->get();
            $failOnIssue = (bool) $this->option('fail-on-issue');
            $migrated = 0;
            $skipped = [];

            if (! $this->option('skip-preflight')) {
                $preflightExit = $this->runPreflight($auditService, $failOnIssue);
                if ($preflightExit !== self::SUCCESS) {
                    return $preflightExit;
                }
            }
            
            if ($tenants->isEmpty()) {
                $this->info("📭 No tenants found.");
                return 0;
            }
            
            $this->info("🏢 Found " . $tenants->count() . " tenant(s)");
            
            foreach ($tenants as $tenant) {
                $this->info("\n" . str_repeat("=", 50));
                $this->info("🚀 Processing tenant: {$tenant->name} ({$tenant->domain})");
                $this->info("💾 Database: {$tenant->db_name}");

                $audit = $auditService->auditTenant($tenant);
                if ($audit['database_status'] !== 'present') {
                    $message = "Skipping {$tenant->domain}: {$audit['status_note']} (recommended: {$audit['recommended_action']})";
                    $this->warn($message);
                    $skipped[] = $message;

                    if ($failOnIssue) {
                        $this->error('Aborting because --fail-on-issue was provided.');
                        return self::FAILURE;
                    }

                    continue;
                }
                
                // التبديل إلى قاعدة بيانات المستأجر
                TenantService::switchToTenant($tenant);
                
                if ($rollback) {
                    $this->info("🔄 Rolling back migrations...");
                    Artisan::call('migrate:rollback', [
                        '--database' => 'tenant',
                        '--path' => $migrationPath,
                        '--force' => true,
                    ]);
                } else {
                    $this->info("🚀 Running migrations...");
                    Artisan::call('migrate', [
                        '--database' => 'tenant',
                        '--path' => $migrationPath,
                        '--force' => true,
                    ]);
                    $migrationOutput = Artisan::output();

                    $seedResult = $defaultContentService->seedDefaultPublicContent();
                    if (trim($migrationOutput) !== '') {
                        $this->line($migrationOutput);
                    }
                    if (trim($seedResult['output']) !== '') {
                        $this->line($seedResult['output']);
                    }

                    if ($seedResult['has_active_landing_page']) {
                        $this->info('✅ Default landing page is available for this tenant.');
                    } else {
                        $this->warn('⚠️ No active landing page exists after seeding this tenant.');
                    }
                }
                
                // طباعة نتائج الأمر
                if ($rollback) {
                    $output = Artisan::output();
                    if (trim($output)) {
                        $this->line($output);
                    }
                }
                
                $this->info("✅ Completed for {$tenant->name}");
                TenantService::switchToDefault();
                $migrated++;
            }
            
            $this->info("\n🎉 Tenant migration run finished.");
            $this->line("Migrated tenants: {$migrated}");
            $this->line('Skipped tenants: ' . count($skipped));

            if ($skipped !== []) {
                $this->newLine();
                $this->line('Skipped details:');
                foreach ($skipped as $message) {
                    $this->line(" - {$message}");
                }
            }
            
            // العودة إلى قاعدة البيانات الرئيسية
            TenantService::switchToDefault();
            
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            
            // التأكد من العودة إلى قاعدة البيانات الرئيسية
            try {
                TenantService::switchToDefault();
            } catch (\Exception $switchError) {
                $this->error("❌ Failed to switch back to default database: " . $switchError->getMessage());
            }
            
            return 1;
        }
        
        return 0;
    }

    private function runPreflight(TenantAuditService $auditService, bool $failOnIssue): int
    {
        $this->info("🔎 Running tenant preflight checks...");
        $audits = $auditService->auditAll();
        $summary = $auditService->summarize($audits);

        $this->line('Preflight summary: '
            . "total={$summary['total']}, present={$summary['present']}, missing={$summary['missing']}, "
            . "ready={$summary['ready']}, partial={$summary['partial']}, unreachable={$summary['unreachable']}");

        $criticalIssues = $audits->filter(fn (array $audit) => $audit['database_status'] !== 'present');
        if ($criticalIssues->isNotEmpty()) {
            foreach ($criticalIssues as $issue) {
                $this->warn(" - {$issue['domain']}: {$issue['status_note']} (recommended: {$issue['recommended_action']})");
            }

            if ($failOnIssue) {
                $this->error('Aborting because preflight found critical issues and --fail-on-issue was provided.');
                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }
}