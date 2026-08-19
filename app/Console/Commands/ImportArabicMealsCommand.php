<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\ArabicMealImportService;
use App\Services\TenantService;
use Illuminate\Console\Command;
use Throwable;

class ImportArabicMealsCommand extends Command
{
    protected $signature = 'meals:import-arabic-library
                            {--tenant= : Tenant domain to import into}
                            {--all : Import into all tenants}
                            {--count=300 : Number of meals to generate}
                            {--force : Update existing library meals}
                            {--no-images : Skip downloading Pexels images}';

    protected $description = 'Import ~300 healthy Gulf/Arabic meals with USDA-estimated macros and Pexels images';

    public function handle(ArabicMealImportService $importService): int
    {
        $all = (bool) $this->option('all');
        $tenantDomain = $this->option('tenant');
        $force = (bool) $this->option('force');
        $withImages = ! (bool) $this->option('no-images');
        $count = max(1, (int) $this->option('count'));

        if (! $all && ! $tenantDomain) {
            $this->error('Provide --tenant=domain or --all.');

            return self::FAILURE;
        }

        $tenants = $all
            ? Tenant::query()->orderBy('id')->get()
            : Tenant::query()->where('domain', $tenantDomain)->get();

        if ($tenants->isEmpty()) {
            $this->error('No matching tenants found.');

            return self::FAILURE;
        }

        foreach ($tenants as $tenant) {
            $this->line(str_repeat('-', 48));
            $this->info("Tenant: {$tenant->name} ({$tenant->domain})");

            try {
                TenantService::switchToTenant($tenant);
                $stats = $importService->import($count, $force, $withImages);
                $this->info("Created: {$stats['created']} | Updated: {$stats['updated']} | Skipped: {$stats['skipped']} | Images: {$stats['images']}");
            } catch (Throwable $e) {
                $this->error("Failed for {$tenant->domain}: ".$e->getMessage());
            } finally {
                TenantService::switchToDefault();
            }
        }

        $this->newLine();
        $this->info('Nutrition values are estimates from USDA ingredient data. Show the disclaimer to admins and clients.');
        $this->info('Images are sourced from Pexels (free license — hostable on your site).');

        return self::SUCCESS;
    }
}
