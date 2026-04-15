<?php

namespace App\Console\Commands;

use App\Models\TenantDatabasePool;
use Illuminate\Console\Command;

class RegisterTenantPoolDatabase extends Command
{
    protected $signature = 'tenants:pool-register
                            {db : Full tenant database name as created in cPanel}
                            {--label= : Optional human-friendly label}
                            {--ready=1 : Whether the tenant DB is already migrated/seeded (1 or 0)}';

    protected $description = 'Register a prepared tenant database in the system pool';

    public function handle(): int
    {
        $dbName = trim((string) $this->argument('db'));
        $label = $this->option('label') ?: null;
        $ready = filter_var($this->option('ready'), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        $ready = $ready ?? $this->option('ready') === '1';

        $poolDb = TenantDatabasePool::updateOrCreate(
            ['db_name' => $dbName],
            [
                'label' => $label,
                'status' => TenantDatabasePool::STATUS_AVAILABLE,
                'is_ready' => $ready,
                'tenant_id' => null,
                'allocated_at' => null,
            ]
        );

        $this->info("✅ Registered tenant pool DB: {$poolDb->db_name}");
        $this->line(json_encode([
            'db_name' => $poolDb->db_name,
            'label' => $poolDb->label,
            'status' => $poolDb->status,
            'is_ready' => $poolDb->is_ready,
        ], JSON_UNESCAPED_UNICODE));

        return self::SUCCESS;
    }
}
