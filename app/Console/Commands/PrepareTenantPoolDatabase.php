<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\TenantDatabasePool;
use App\Services\TenantService;
use App\Support\MigrationScope;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Throwable;

class PrepareTenantPoolDatabase extends Command
{
    protected $signature = 'tenants:pool-prepare
                            {db? : Tenant database name as created in cPanel}
                            {--all-available : Prepare every available pool database}
                            {--label= : Optional label when registering}
                            {--no-seed : Migrate schema only (skip PoolBaselineSeeder)}
                            {--no-register : Do not upsert the pool row}';

    protected $description = 'Migrate + seed a clean baseline into a pool DB and mark it ready';

    public function handle(): int
    {
        $targets = $this->resolveTargets();
        if ($targets === []) {
            $this->error('Provide {db} or --all-available with at least one available pool row.');

            return self::FAILURE;
        }

        $ok = 0;
        $failed = 0;

        foreach ($targets as $dbName) {
            $this->line(str_repeat('-', 48));
            $this->info("Preparing pool database: {$dbName}");

            try {
                $this->assertDatabaseExists($dbName);
                TenantService::switchToDatabase($dbName);

                Artisan::call('migrate', [
                    '--database' => 'tenant',
                    '--path' => MigrationScope::tenant(),
                    '--force' => true,
                ]);
                $migrateOutput = trim(Artisan::output());
                if ($migrateOutput !== '') {
                    $this->line($migrateOutput);
                }

                if (! $this->option('no-seed')) {
                    Artisan::call('db:seed', [
                        '--class' => 'Database\\Seeders\\Tenants\\PoolBaselineSeeder',
                        '--database' => 'tenant',
                        '--force' => true,
                    ]);
                    $seedOutput = trim(Artisan::output());
                    if ($seedOutput !== '') {
                        $this->line($seedOutput);
                    }
                    $this->info('Baseline seeded (settings, memberships, permissions; no users).');
                }

                TenantService::switchToDefault();

                if (! $this->option('no-register')) {
                    $existing = TenantDatabasePool::query()->where('db_name', $dbName)->first();
                    $allocated = Tenant::on('system')->where('db_name', $dbName)->exists()
                        || ($existing && $existing->status === TenantDatabasePool::STATUS_ALLOCATED);

                    if ($allocated && $existing) {
                        $existing->update(['is_ready' => true]);
                        $this->warn('Database is allocated to a tenant; left status=allocated and set is_ready=1.');
                    } else {
                        TenantDatabasePool::updateOrCreate(
                            ['db_name' => $dbName],
                            [
                                'label' => $this->option('label') ?: ($existing?->label),
                                'status' => TenantDatabasePool::STATUS_AVAILABLE,
                                'is_ready' => true,
                                'tenant_id' => null,
                                'allocated_at' => null,
                            ]
                        );
                        $this->info('Registered pool row ready=1 status=available.');
                    }
                }

                $ok++;
            } catch (Throwable $e) {
                $failed++;
                $this->error("Failed for {$dbName}: ".$e->getMessage());
                try {
                    TenantService::switchToDefault();
                } catch (Throwable) {
                    // ignore
                }
            }
        }

        $this->newLine();
        $this->info("Done. Prepared: {$ok} | Failed: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function resolveTargets(): array
    {
        if ($this->option('all-available')) {
            return TenantDatabasePool::query()
                ->available()
                ->orderBy('id')
                ->pluck('db_name')
                ->map(fn ($name) => (string) $name)
                ->all();
        }

        $db = trim((string) $this->argument('db'));

        return $db !== '' ? [$db] : [];
    }

    private function assertDatabaseExists(string $dbName): void
    {
        $exists = collect(DB::connection('system')->select('SHOW DATABASES'))
            ->pluck('Database')
            ->contains($dbName);

        if (! $exists) {
            throw new \RuntimeException("MySQL database [{$dbName}] was not found. Create it in cPanel first.");
        }
    }
}
