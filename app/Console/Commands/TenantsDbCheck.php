<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TenantsDbCheck extends Command
{
    protected $signature = 'tenants:db-check {--domain=} {--tables=meal_plans,pages,workouts,workout_schedules,landing_pages,faqs,testimonials}';

    protected $description = 'Switch to a tenant by domain and print the actual database in use and row counts for selected tables if they exist';

    public function handle(): int
    {
        $domain = $this->option('domain');
        if (!$domain) {
            $this->error('Please provide --domain=<tenant-domain>');
            return Command::FAILURE;
        }

        $tenant = Tenant::on('system')->where('domain', $domain)->first();
        if (!$tenant) {
            $this->error("Tenant not found for domain: {$domain}");
            return Command::FAILURE;
        }

        TenantService::switchToTenant($tenant);
        $dbName = DB::connection()->getDatabaseName();
        $this->info("Domain: {$domain}");
        $this->info("Using DB: {$dbName}");

        $tables = array_filter(array_map('trim', explode(',', (string)$this->option('tables'))));
        foreach ($tables as $table) {
            try {
                if (Schema::hasTable($table)) {
                    $count = DB::table($table)->count();
                    $this->line("- {$table}: {$count} rows");
                } else {
                    $this->line("- {$table}: (table not found)");
                }
            } catch (\Throwable $e) {
                $this->line("- {$table}: error ({$e->getMessage()})");
            }
        }

        // Reset to system
        \App\Services\TenantService::switchToDefault();
        return Command::SUCCESS;
    }
}

