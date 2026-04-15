<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SeedTenantDefaultContent extends Command
{
    protected $signature = 'tenants:seed-default-content
                            {--domain= : Tenant domain registered in system.tenants}
                            {--db= : Tenant database name registered in system.tenants}';

    protected $description = 'Seed default landing page and starter content for an existing tenant';

    public function handle(): int
    {
        $domain = $this->option('domain');
        $dbName = $this->option('db');

        if (!$domain && !$dbName) {
            $this->error('Please provide either --domain or --db.');
            return self::FAILURE;
        }

        $tenantQuery = Tenant::on('system')->newQuery();

        if ($domain) {
            $tenantQuery->where('domain', $domain);
        }

        if ($dbName) {
            $tenantQuery->where('db_name', $dbName);
        }

        $tenant = $tenantQuery->first();

        if (!$tenant) {
            $this->error('Tenant not found.');
            return self::FAILURE;
        }

        try {
            TenantService::switchToTenant($tenant);

            $user = User::query()->where('email', $tenant->email)->first();

            if (!$user) {
                $user = User::query()->create([
                    'name' => $tenant->name ?: Str::title(str_replace('-', ' ', $tenant->slug ?? 'tenant')),
                    'email' => $tenant->email ?: (($tenant->slug ?: 'tenant') . '@' . config('app.domain', 'example.com')),
                    'password' => Hash::make(Str::random(40)),
                ]);
            }

            if (method_exists($user, 'assignRole')) {
                try {
                    $user->assignRole('admin');
                } catch (\Throwable $e) {
                    // Ignore if roles are not ready yet.
                }
            }

            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Tenants\\DefaultTenantContentSeeder',
                '--database' => 'tenant',
                '--force' => true,
            ]);

            $this->info("✅ Default content seeded for {$tenant->domain} ({$tenant->db_name})");
            $this->line(Artisan::output());
        } catch (\Throwable $e) {
            $this->error('❌ Failed: ' . $e->getMessage());
            return self::FAILURE;
        } finally {
            TenantService::switchToDefault();
        }

        return self::SUCCESS;
    }
}
