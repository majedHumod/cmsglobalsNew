<?php
//seeder على مستوى كل قاعدة بيانات
namespace App\Console\Commands\Tenants;

use App\Models\Tenant;
use App\Services\Tenant\TenantAuditService;
use App\Services\TenantService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SeederCommend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:seeder {class}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run tenant-specific seeders for all tenants';

    /**
     * Execute the console command.
     */
    public function handle(TenantAuditService $auditService)
    {
        $class = $this->argument('class');
        $tenants = Tenant::query()->get();
        $tenants->each(function ($tenant) use($class, $auditService) {
            $audit = $auditService->auditTenant($tenant);
            if ($audit['database_status'] !== 'present') {
                $this->warn('Skipping ' . $tenant->domain . ': ' . $audit['status_note']);
                return;
            }

            // تبديل الاتصال إلى قاعدة بيانات العميل
            TenantService::switchToTenant($tenant);

            $this->info('🚀 Starting seeder for: ' . $tenant->domain);
            $this->info('---------------------------------------------');

            // تنفيذ المايجريشن
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Tenants\\'.$class,
                '--database' => 'tenant',
                '--force' => true, // مهم في بعض السيرفرات
            ]);

            // طباعة نتائج الأمر
            $this->line(Artisan::output());
        });

        TenantService::switchToDefault();
    }
}
