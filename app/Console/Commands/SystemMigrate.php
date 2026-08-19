<?php

namespace App\Console\Commands;

use App\Support\MigrationScope;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SystemMigrate extends Command
{
    protected $signature = 'system:migrate {--path=} {--rollback}';

    protected $description = 'Run only system database migrations';

    public function handle(): int
    {
        try {
            $migrationPath = MigrationScope::system($this->option('path'));

            if ($this->option('rollback')) {
                $this->info('Rolling back system migrations...');

                Artisan::call('migrate:rollback', [
                    '--database' => 'system',
                    '--path' => $migrationPath,
                    '--force' => true,
                ]);
            } else {
                $this->info('Running system migrations...');

                Artisan::call('migrate', [
                    '--database' => 'system',
                    '--path' => $migrationPath,
                    '--force' => true,
                ]);
            }

            $output = trim(Artisan::output());
            if ($output !== '') {
                $this->line($output);
            }

            $this->info('System migration command completed successfully.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('System migration failed: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
