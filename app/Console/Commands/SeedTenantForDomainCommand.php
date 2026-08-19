<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SeedTenantForDomainCommand extends Command
{
    protected $signature = 'tenants:seed-for
                            {domain : The tenant domain (e.g. app1.cmsglobals.test)}
                            {seeder : The seeder class short name under Database\\Seeders\\Tenants\\}';

    protected $description = 'Run a tenant-specific seeder for a single domain only.';

    public function handle(): int
    {
        $domain      = $this->argument('domain');
        $seederShort = $this->argument('seeder');
        $seederClass = 'Database\\Seeders\\Tenants\\' . $seederShort;

        $tenant = Tenant::on('system')->where('domain', $domain)->first();

        if (!$tenant) {
            $this->error("❌ No tenant found for domain: {$domain}");
            $this->line('Use `php artisan tenants:audit` to list available tenants.');
            return Command::FAILURE;
        }

        $this->info("🔄 Switching to tenant: {$tenant->name} (DB: {$tenant->db_name})");
        TenantService::switchToTenant($tenant);

        $this->info("🌱 Running seeder: {$seederClass}");

        try {
            Artisan::call('db:seed', [
                '--class'    => $seederClass,
                '--database' => 'tenant',
                '--force'    => true,
            ]);
            $this->line(Artisan::output());
            $this->info("✅ Seeder completed successfully for {$domain}");
        } catch (\Throwable $e) {
            $this->error("❌ Seeder failed: {$e->getMessage()}");
            TenantService::switchToDefault();
            return Command::FAILURE;
        }

        TenantService::switchToDefault();
        return Command::SUCCESS;
    }
}
