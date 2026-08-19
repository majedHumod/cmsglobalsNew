<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Support\MigrationScope;
use App\Services\TenantService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class MigrateLandingPage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'landing-page:migrate {tenant_domain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate landing page table for a specific tenant';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantDomain = $this->argument('tenant_domain');
        
        try {
            // البحث عن المستأجر
            $tenant = Tenant::on('system')->where('domain', $tenantDomain)->first();
            
            if (!$tenant) {
                $this->error("❌ Tenant with domain '{$tenantDomain}' not found!");
                $this->info("💡 Available tenants:");
                $tenants = Tenant::on('system')->get(['domain', 'name']);
                foreach ($tenants as $t) {
                    $this->line("   - {$t->domain} ({$t->name})");
                }
                return 1;
            }
            
            $this->info("🏢 Found tenant: {$tenant->name} (Domain: {$tenant->domain})");
            
            // التبديل إلى قاعدة بيانات المستأجر
            TenantService::switchToTenant($tenant);
            
            $this->info("🔄 Switched to tenant database: {$tenant->db_name}");
            
            // التحقق من وجود جدول الصفحات الرئيسية
            if (!Schema::hasTable('landing_pages')) {
                $this->info("📝 Creating landing_pages table...");
                Artisan::call('migrate', [
                    '--path' => MigrationScope::tenant('database/migrations/tenants/2025_06_20_create_landing_pages_table.php'),
                    '--database' => 'tenant',
                    '--force' => true,
                ]);
                $this->info(Artisan::output());
            } else {
                $this->info("✅ landing_pages table already exists");
            }
            
            $this->info("✅ Landing page table migrated successfully!");
            
            // العودة إلى قاعدة البيانات الرئيسية
            TenantService::switchToDefault();
            
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            
            // التأكد من العودة إلى قاعدة البيانات الرئيسية في حالة الخطأ
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