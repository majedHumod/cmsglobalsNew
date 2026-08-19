<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\Tenant\TenantAuditService;
use App\Services\Tenant\TenantDefaultContentService;
use App\Support\MigrationScope;
use App\Services\TenantService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class TenantMigrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:migrate {tenant_domain} {--path=} {--rollback}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migrations for a specific tenant';

    /**
     * Execute the console command.
     */
    public function handle(TenantAuditService $auditService, TenantDefaultContentService $defaultContentService)
    {
        $tenantDomain = $this->argument('tenant_domain');
        $rollback = $this->option('rollback');
        
        try {
            $migrationPath = MigrationScope::tenant($this->option('path'));

            // البحث عن المستأجر في قاعدة البيانات الرئيسية
            $tenant = Tenant::where('domain', $tenantDomain)->first();
            
            if (!$tenant) {
                $this->error("❌ Tenant with domain '{$tenantDomain}' not found!");
                $this->info("💡 Available tenants:");
                $tenants = Tenant::query()->get(['domain', 'name']);
                foreach ($tenants as $t) {
                    $this->line("   - {$t->domain} ({$t->name})");
                }
                return 1;
            }
            
            $this->info("🏢 Found tenant: {$tenant->name} (Domain: {$tenant->domain})");
            $this->info("💾 Database: {$tenant->db_name}");

            $audit = $auditService->auditTenant($tenant);
            if ($audit['database_status'] !== 'present') {
                $this->error("❌ Tenant database is missing for '{$tenant->domain}'.");
                $this->line("Recommended action: {$audit['recommended_action']}");
                $this->line("Note: {$audit['status_note']}");

                return self::FAILURE;
            }
            
            // التبديل إلى قاعدة بيانات المستأجر
            TenantService::switchToTenant($tenant);
            
            $this->info("🔄 Switched to tenant database");
            
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
                    $this->warn('⚠️ Tenant migration completed, but no active landing page exists after seeding.');
                }
            }
            
            // طباعة نتائج الأمر
            if ($rollback) {
                $this->line(Artisan::output());
            }
            
            $this->info("✅ Migration completed successfully!");
            
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
}