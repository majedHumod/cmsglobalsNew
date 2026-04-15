<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ResetDemoTenant extends Command
{
    protected $signature = 'tenants:reset-demo {--domain= : Demo tenant domain (default: from env DEMO_TENANT_DOMAIN)}';

    protected $description = 'Reset demo tenant data by refreshing tenant migrations and re-seeding demo content.';

    public function handle(): int
    {
        $domain = $this->option('domain') ?: env('DEMO_TENANT_DOMAIN', 'demo.cmsglobals.test');
        $tenant = Tenant::on('system')->where('domain', $domain)->first();
        if (!$tenant) {
            $this->error("❌ Demo tenant not found for domain: {$domain}");
            return Command::FAILURE;
        }

        TenantService::switchToTenant($tenant);
        $this->info("🔄 Resetting demo tenant DB: " . $tenant->db_name);

        try {
            Artisan::call('migrate:fresh', [
                '--path' => 'database/migrations/tenants/',
                '--database' => 'tenant',
                '--force' => true,
            ]);
            $this->line(Artisan::output());
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Tenants\\DemoTenantSeeder',
                '--database' => 'tenant',
                '--force' => true,
            ]);
            $this->line(Artisan::output());
        } catch (\Throwable $e) {
            $this->error("❌ Reset failed: {$e->getMessage()}");
            TenantService::switchToDefault();
            return Command::FAILURE;
        }

        TenantService::switchToDefault();
        $this->info("✅ Demo reset completed");
        return Command::SUCCESS;
    }
}

