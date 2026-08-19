<?php

namespace App\Services\Tenant;

use App\Models\Tenant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TenantAuditService
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function auditAll(): Collection
    {
        $tenants = Tenant::query()->orderBy('domain')->get();
        $existingSchemas = collect(DB::connection('system')->select('select schema_name from information_schema.schemata'))
            ->pluck('schema_name')
            ->flip();

        return $tenants->map(fn (Tenant $tenant) => $this->auditTenant($tenant, $existingSchemas));
    }

    /**
     * @param  Collection<string, mixed>|null  $existingSchemas
     * @return array<string, mixed>
     */
    public function auditTenant(Tenant $tenant, ?Collection $existingSchemas = null): array
    {
        $dbName = (string) ($tenant->db_name ?? '');
        $databaseExists = $dbName !== '' && ($existingSchemas?->has($dbName) ?? $this->databaseExists($dbName));

        $result = [
            'tenant_id' => $tenant->id,
            'name' => $tenant->name,
            'domain' => $tenant->domain,
            'db_name' => $tenant->db_name,
            'tenant_status' => $tenant->status,
            'database_status' => $databaseExists ? 'present' : 'missing',
            'schema_status' => 'missing_database',
            'recommended_action' => $this->recommendedAction($tenant, $databaseExists, 'missing_database'),
            'status_note' => $databaseExists
                ? 'Database exists and is ready for schema inspection.'
                : 'Database referenced by this tenant row does not exist on the MySQL server.',
            'last_audited_at' => Carbon::now(),
            'checks' => [],
        ];

        if (!$databaseExists) {
            return $result;
        }

        try {
            $checks = $this->inspectTenantSchema($dbName);
            $schemaStatus = $this->determineSchemaStatus($checks);

            $result['checks'] = $checks;
            $result['schema_status'] = $schemaStatus;
            $result['recommended_action'] = $this->recommendedAction($tenant, true, $schemaStatus);
            $result['status_note'] = $this->statusNoteForSchema($schemaStatus);
        } catch (\Throwable $e) {
            $result['schema_status'] = 'unreachable';
            $result['recommended_action'] = 'restore';
            $result['status_note'] = 'Database exists but schema inspection failed: ' . $e->getMessage();
        } finally {
            DB::purge('tenant');
        }

        return $result;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $audits
     */
    public function syncAuditColumns(Collection $audits): void
    {
        $audits->each(function (array $audit) {
            Tenant::query()
                ->whereKey($audit['tenant_id'])
                ->update([
                    'database_status' => $audit['database_status'],
                    'schema_status' => $audit['schema_status'],
                    'recommended_action' => $audit['recommended_action'],
                    'status_note' => $audit['status_note'],
                    'last_audited_at' => $audit['last_audited_at'],
                ]);
        });
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $audits
     * @return array<string, int>
     */
    public function summarize(Collection $audits): array
    {
        return [
            'total' => $audits->count(),
            'present' => $audits->where('database_status', 'present')->count(),
            'missing' => $audits->where('database_status', 'missing')->count(),
            'ready' => $audits->where('schema_status', 'ready')->count(),
            'partial' => $audits->where('schema_status', 'partial')->count(),
            'unreachable' => $audits->where('schema_status', 'unreachable')->count(),
        ];
    }

    /**
     * @return array<string, bool>
     */
    private function inspectTenantSchema(string $dbName): array
    {
        config(['database.connections.tenant.database' => $dbName]);
        DB::purge('tenant');

        return [
            'users_table' => Schema::connection('tenant')->hasTable('users'),
            'pages_table' => Schema::connection('tenant')->hasTable('pages'),
            'user_memberships_table' => Schema::connection('tenant')->hasTable('user_memberships'),
            'subscription_plans_table' => Schema::connection('tenant')->hasTable('subscription_plans'),
            'users_gender_column' => Schema::connection('tenant')->hasColumn('users', 'gender'),
            'pages_audience_gender_column' => Schema::connection('tenant')->hasColumn('pages', 'audience_gender'),
            'pages_required_membership_types_column' => Schema::connection('tenant')->hasColumn('pages', 'required_membership_types'),
        ];
    }

    private function determineSchemaStatus(array $checks): string
    {
        $minimumReady = $checks['users_table']
            && $checks['pages_table']
            && $checks['user_memberships_table'];

        $latestReady = $checks['subscription_plans_table']
            && $checks['users_gender_column']
            && $checks['pages_audience_gender_column']
            && $checks['pages_required_membership_types_column'];

        if ($minimumReady && $latestReady) {
            return 'ready';
        }

        if ($minimumReady) {
            return 'partial';
        }

        return 'incomplete';
    }

    private function recommendedAction(Tenant $tenant, bool $databaseExists, string $schemaStatus): string
    {
        if (($tenant->domain === null || $tenant->domain === '') || ($tenant->db_name === null || $tenant->db_name === '')) {
            return 'delete';
        }

        if (!$databaseExists) {
            return $tenant->status === 'inactive' ? 'archive' : 'restore';
        }

        if (in_array($schemaStatus, ['partial', 'incomplete', 'unreachable'], true)) {
            return 'restore';
        }

        return 'none';
    }

    private function statusNoteForSchema(string $schemaStatus): string
    {
        return match ($schemaStatus) {
            'ready' => 'Database exists and passes the minimum + latest tenant schema checks.',
            'partial' => 'Database exists and passes minimum checks, but is missing one or more latest tenant schema changes.',
            'incomplete' => 'Database exists, but key tenant tables are missing.',
            'unreachable' => 'Database exists, but schema inspection failed.',
            default => 'Database inspection state is unknown.',
        };
    }

    private function databaseExists(string $dbName): bool
    {
        if ($dbName === '') {
            return false;
        }

        return DB::connection('system')
            ->table('information_schema.schemata')
            ->where('schema_name', $dbName)
            ->exists();
    }
}
