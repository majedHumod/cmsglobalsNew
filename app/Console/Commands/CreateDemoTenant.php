<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateDemoTenant extends Command
{
    protected $signature = 'tenants:create-demo {--domain= : Demo tenant domain (e.g. demo.cmsglobals.test)} {--db= : Database name for demo (default: tenant_demo)} {--name= : Display name (default: Demo Tenant)} {--readwrite : Allow writes (by default demo is intended read-only)}';

    protected $description = 'Provision a demo tenant with its own database, run tenant migrations, and seed demo data.';

    public function handle(): int
    {
        $domain = $this->option('domain') ?: env('DEMO_TENANT_DOMAIN', 'demo.cmsglobals.test');
        $dbName = $this->option('db') ?: 'tenant_demo';
        $name = $this->option('name') ?: 'Demo Tenant';

        $this->info("🚀 Creating demo tenant:");
        $this->line(" - Domain: {$domain}");
        $this->line(" - DB: {$dbName}");

        // Ensure system connection is default to create DB + insert row
        DB::setDefaultConnection('system');

        // Create database if not exists
        try {
            DB::statement("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $this->info("✅ Database ensured: {$dbName}");
        } catch (\Throwable $e) {
            $this->error("❌ Failed to create database {$dbName}: {$e->getMessage()}");
            return Command::FAILURE;
        }

        // Upsert tenant row in system DB
        $existing = Tenant::on('system')->where('domain', $domain)->first();
        if ($existing) {
            $this->warn("ℹ️  Tenant with domain {$domain} already exists. Updating DB name to {$dbName}.");
            $existing->db_name = $dbName;
            $existing->save();
            $tenant = $existing;
        } else {
            $tenant = new Tenant();
            $tenant->setConnection('system');
            $tenant->name = $name;
            $tenant->slug = Str::slug($name);
            $tenant->domain = $domain;
            $tenant->subdomain = explode('.', $domain)[0];
            $tenant->email = 'demo@' . ($tenant->subdomain ?: 'demo') . '.com';
            $tenant->phone = null;
            $tenant->logo = null;
            $tenant->status = 'active';
            $tenant->trial_ends_at = now()->addDays(14);
            $tenant->db_name = $dbName;
            $tenant->save();
            $this->info("✅ Tenant row inserted into system.tenants");
        }

        // Switch to tenant connection
        TenantService::switchToTenant($tenant);
        $this->info("🔄 Switched to tenant DB: " . DB::connection()->getDatabaseName());

        // Run tenant migrations (ensure permissions base tables first)
        try {
            // Ensure critical order:
            // 1) Workouts (to satisfy FKs in schedules)
            try {
                Artisan::call('migrate', [
                    '--path' => 'database/migrations/tenants/2025_01_20_create_workouts_table.php',
                    '--database' => 'tenant',
                    '--force' => true,
                ]);
            } catch (\Throwable $e) {
                $this->warn("⚠️ Skipping workouts migration: {$e->getMessage()}");
            }
            // 2) Spatie base permissions first if needed
            Artisan::call('migrate', [
                '--path' => 'database/migrations/tenants/2025_03_07_015809_create_permission_tables.php',
                '--database' => 'tenant',
                '--force' => true,
            ]);
            // Then run the rest
            Artisan::call('migrate', [
                '--path' => 'database/migrations/tenants/',
                '--database' => 'tenant',
                '--force' => true,
            ]);
            $this->line(Artisan::output());
            $this->info("✅ Tenant migrations complete");
        } catch (\Throwable $e) {
            $this->warn("⚠️ Tenant migrations failed, attempting fresh migrate: {$e->getMessage()}");
            try {
                Artisan::call('migrate:fresh', [
                    '--path' => 'database/migrations/tenants/',
                    '--database' => 'tenant',
                    '--force' => true,
                ]);
                $this->line(Artisan::output());
                $this->info("✅ Tenant fresh migrations complete");
            } catch (\Throwable $e2) {
                $this->error("❌ Tenant fresh migrations failed: {$e2->getMessage()}");
                TenantService::switchToDefault();
                return Command::FAILURE;
            }
        }

        // Seed base + demo data
        try {
            // Base seeders (site settings, permissions, memberships, faqs)
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Tenants\\BaseTenantSeeder',
                '--database' => 'tenant',
                '--force' => true,
            ]);
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Tenants\\DemoTenantSeeder',
                '--database' => 'tenant',
                '--force' => true,
            ]);
            $this->line(Artisan::output());
            $this->info("✅ Demo seed completed");
        } catch (\Throwable $e) {
            $this->error("❌ Demo seed failed: {$e->getMessage()}");
            TenantService::switchToDefault();
            return Command::FAILURE;
        }

        TenantService::switchToDefault();
        $this->info("🎉 Demo tenant is ready at https://{$domain}");
        $this->warn("ℹ️ Remember to point DNS/host and web server to this app for {$domain}.");
        return Command::SUCCESS;
    }
}

