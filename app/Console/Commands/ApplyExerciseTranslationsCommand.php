<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\ExerciseTranslationService;
use App\Services\TenantService;
use Illuminate\Console\Command;
use Throwable;

class ApplyExerciseTranslationsCommand extends Command
{
    protected $signature = 'exercises:apply-translations
                            {locale=ar : Locale overlay to apply (e.g. ar, fr)}
                            {--tenant= : Tenant domain}
                            {--all : Apply to all tenants}
                            {--force : Overwrite existing translations for this locale}';

    protected $description = 'Merge exercise translation overlays into tenant exercise.translations JSON (extensible per locale)';

    public function handle(ExerciseTranslationService $translations): int
    {
        $locale = strtolower((string) $this->argument('locale'));
        $all = (bool) $this->option('all');
        $tenantDomain = $this->option('tenant');
        $force = (bool) $this->option('force');

        if (! $all && ! $tenantDomain) {
            $this->error('Provide --tenant=domain or --all.');

            return self::FAILURE;
        }

        if (! in_array($locale, $translations->supportedLocales(), true)) {
            $this->warn("Locale [{$locale}] is not listed in config/exercise_localization.php supported_locales; continuing anyway.");
        }

        try {
            $translations->loadOverlay($locale);
        } catch (Throwable $e) {
            $this->error($e->getMessage());

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
                $stats = $translations->applyOverlay($locale, $force);
                $this->info("Updated: {$stats['updated']} | Skipped: {$stats['skipped']} | Missing: {$stats['missing']}");
            } catch (Throwable $e) {
                $this->error("Failed for {$tenant->domain}: ".$e->getMessage());
            } finally {
                TenantService::switchToDefault();
            }
        }

        $this->newLine();
        $this->info("Done applying [{$locale}] exercise translations.");

        return self::SUCCESS;
    }
}
