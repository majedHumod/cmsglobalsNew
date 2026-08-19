<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Support\MigrationScope;
use App\Services\TenantService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class CreatePermissionTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:create-tables {tenant_domain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create all permission related tables for a tenant';

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
            
            // تشغيل المايجريشن الخاص بإنشاء جداول الصلاحيات
            $this->info("🚀 Creating permission tables...");
            
            // التحقق من وجود جدول الصلاحيات
            if (!Schema::hasTable('permissions')) {
                $this->info("📝 Creating base permission tables...");
                Artisan::call('migrate', [
                    '--path' => MigrationScope::tenant('database/migrations/tenants/2025_03_07_015809_create_permission_tables.php'),
                    '--database' => 'tenant',
                    '--force' => true,
                ]);
                $this->info(Artisan::output());
            } else {
                $this->info("✅ Base permission tables already exist");
            }
            
            // إنشاء جداول الصلاحيات المتقدمة
            $this->info("📝 Creating advanced permission tables...");
            Artisan::call('migrate', [
                '--path' => MigrationScope::tenant('database/migrations/tenants/2025_01_19_create_advanced_permissions_tables.php'),
                '--database' => 'tenant',
                '--force' => true,
            ]);
            $this->info(Artisan::output());
            
            // إضافة الأعمدة المطلوبة لجدول الصلاحيات
            $this->info("📝 Adding required columns to permissions table...");
            Artisan::call('migrate', [
                '--path' => MigrationScope::tenant('database/migrations/tenants/2025_06_10_add_columns_to_permissions_table.php'),
                '--database' => 'tenant',
                '--force' => true,
            ]);
            $this->info(Artisan::output());
            
            // إنشاء الصلاحيات الأساسية
            $this->info("🌱 Seeding permissions...");
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Tenants\\PermissionsSeeder',
                '--database' => 'tenant',
                '--force' => true,
            ]);
            $this->info(Artisan::output());
            
            $this->info("✅ Permission tables created successfully!");
            
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