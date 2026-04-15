<?php

namespace App\Console\Commands;

use App\Models\TenantDatabasePool;
use Illuminate\Console\Command;

class ListTenantPoolDatabases extends Command
{
    protected $signature = 'tenants:pool-list';

    protected $description = 'List tenant databases available in the system pool';

    public function handle(): int
    {
        $rows = TenantDatabasePool::query()
            ->orderByRaw("CASE status WHEN 'available' THEN 1 WHEN 'allocated' THEN 2 ELSE 3 END")
            ->orderBy('db_name')
            ->get(['db_name', 'label', 'status', 'is_ready', 'tenant_id', 'allocated_at']);

        if ($rows->isEmpty()) {
            $this->warn('No tenant databases are registered in the pool.');
            return self::SUCCESS;
        }

        $this->table(
            ['DB Name', 'Label', 'Status', 'Ready', 'Tenant ID', 'Allocated At'],
            $rows->map(fn ($row) => [
                $row->db_name,
                $row->label,
                $row->status,
                $row->is_ready ? 'yes' : 'no',
                $row->tenant_id ?: '-',
                $row->allocated_at?->toDateTimeString() ?: '-',
            ])->all()
        );

        return self::SUCCESS;
    }
}
