<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class EnsureAdminCoachCommand extends Command
{
    protected $signature = 'tenants:ensure-admin-coach
                            {--domain= : Only process the tenant with this exact domain}';

    protected $description = 'Assign the coach role to every user who has admin (for existing tenant DBs)';

    public function handle(): int
    {
        try {
            $tenants = Tenant::on('system')
                ->when($this->option('domain'), fn ($q) => $q->where('domain', $this->option('domain')))
                ->orderBy('id')
                ->get();
        } catch (\Throwable $e) {
            $this->error('System tenant registry unavailable: '.$e->getMessage());

            return self::FAILURE;
        }

        if ($tenants->isEmpty()) {
            $this->warn('No tenants matched. If you use a single-tenant database, assign roles with tinker on that connection instead.');

            return self::SUCCESS;
        }

        $total = 0;

        foreach ($tenants as $tenant) {
            if (empty($tenant->db_name)) {
                $this->warn("Skipping {$tenant->domain}: no db_name.");

                continue;
            }

            $this->info(str_repeat('=', 48));
            $this->info("Tenant: {$tenant->name} ({$tenant->domain})");

            try {
                TenantService::switchToTenant($tenant);
            } catch (\Throwable $e) {
                $this->error("  Could not connect: {$e->getMessage()}");
                TenantService::switchToDefault();

                continue;
            }

            try {
                if (! Schema::hasTable('users') || ! Schema::hasTable('roles') || ! Schema::hasTable('model_has_roles')) {
                    $this->warn('  Skipping: permission tables not present.');

                    continue;
                }

                $count = 0;
                $admins = User::query()->role('admin')->get();
                foreach ($admins as $user) {
                    if (! $user->hasRole('coach')) {
                        $user->assignRole('coach');
                        $this->line("  + coach: {$user->email}");
                        $count++;
                    }
                }
                if ($count === 0) {
                    $this->line('  (all admins already have coach, or no admin users)');
                }
                $total += $count;
            } catch (\Throwable $e) {
                $this->error("  Error: {$e->getMessage()}");
            } finally {
                TenantService::switchToDefault();
            }
        }

        $this->newLine();
        $this->info("Done. Assigned coach to {$total} user(s) across tenant(s).");

        return self::SUCCESS;
    }
}
