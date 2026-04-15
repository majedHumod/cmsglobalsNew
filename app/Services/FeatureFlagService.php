<?php

namespace App\Services;

use App\Models\SiteSetting;

class FeatureFlagService
{
    public static function enabled(string $key, bool $default = false): bool
    {
        try {
            // Reuse SiteSetting as per-tenant storage
            $value = SiteSetting::get($key, $default ? '1' : '0');
            return filter_var($value, FILTER_VALIDATE_BOOL) || $value === '1';
        } catch (\Throwable $e) {
            return $default;
        }
    }
}

