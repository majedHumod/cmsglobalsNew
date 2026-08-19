<?php

namespace App\Console\Commands\Tenants;

use App\Models\Tenant;
use App\Services\Tenant\TenantAuditService;
use App\Services\Tenant\TenantDefaultContentService;
use App\Support\MigrationScope;
use App\Services\TenantService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class MigrateCommend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run tenant-specific migrations for all tenants';

    /**
     * Execute the console command.
     */
    public function handle(TenantAuditService $auditService, TenantDefaultContentService $defaultContentService)
    {
        $tenants = Tenant::query()->get();

        $tenants->each(function ($tenant) use ($auditService, $defaultContentService) {
            $audit = $auditService->auditTenant($tenant);
            if ($audit['database_status'] !== 'present') {
                $this->warn('Skipping ' . $tenant->domain . ': ' . $audit['status_note']);
                return;
            }

            // تبديل الاتصال إلى قاعدة بيانات العميل
            TenantService::switchToTenant($tenant);

            $this->info('🚀 Starting migration for: ' . $tenant->domain);
            $this->info('---------------------------------------------');

            // تنفيذ المايجريشن
           //Artisan::call('migrate:rollback', [ في حال الرغبة في الرول باك
            Artisan::call('migrate', [
                '--path' => MigrationScope::tenant(),
                '--database' => 'tenant',
                '--force' => true, // مهم في بعض السيرفرات
            ]);
            $migrationOutput = Artisan::output();

            $seedResult = $defaultContentService->seedDefaultPublicContent();

            // طباعة نتائج الأمر
            $this->line($migrationOutput);
            if (trim($seedResult['output']) !== '') {
                $this->line($seedResult['output']);
            }
        });

        TenantService::switchToDefault();
    }
}
