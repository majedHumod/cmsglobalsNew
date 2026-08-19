<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\RepdbExerciseImportService;
use App\Services\TenantService;
use Illuminate\Console\Command;
use Throwable;

class ImportRepdbExercisesCommand extends Command
{
    protected $signature = 'exercises:import-repdb
                            {--tenant= : Tenant domain to import into}
                            {--all : Import into all tenants}
                            {--path= : Local dataset directory or zip path}
                            {--force : Update existing exercise rows and overwrite images}';

    protected $description = 'Import RepDB free-tier exercises + flat images into tenant library (skips paid animation samples)';

    public function handle(RepdbExerciseImportService $importService): int
    {
        $all = (bool) $this->option('all');
        $tenantDomain = $this->option('tenant');
        $force = (bool) $this->option('force');
        $path = $this->option('path');

        if (! $all && ! $tenantDomain) {
            $this->error('Provide --tenant=domain or --all.');

            return self::FAILURE;
        }

        try {
            $this->info('Resolving RepDB dataset…');
            $datasetRoot = $importService->resolveDatasetRoot($path ?: null);
            $this->info("Dataset root: {$datasetRoot}");

            $imageCount = $importService->syncImages($datasetRoot, $force);
            $this->info("Images synced to public storage: {$imageCount}");

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
                    $stats = $importService->importExercises($datasetRoot, $force);
                    $this->info("Created: {$stats['created']} | Updated: {$stats['updated']} | Skipped: {$stats['skipped']}");
                } catch (Throwable $e) {
                    $this->error("Failed for {$tenant->domain}: ".$e->getMessage());
                } finally {
                    TenantService::switchToDefault();
                }
            }

            $this->newLine();
            $this->info('Done. Remember to show RepDB attribution wherever these images are displayed.');

            return self::SUCCESS;
        } catch (Throwable $e) {
            TenantService::switchToDefault();
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
