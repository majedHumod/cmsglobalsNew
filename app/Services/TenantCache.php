<?php

namespace App\Services;

class TenantCache
{
    public static function key(string $base): string
    {
        $tenant = TenantService::getTenant();
        $domain = $tenant?->domain ?? 'system';
        $dbName = $tenant?->db_name ?? 'system';

        return $domain . ':' . $dbName . ':' . $base;
    }
}

