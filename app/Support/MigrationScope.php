<?php

namespace App\Support;

use InvalidArgumentException;

class MigrationScope
{
    public const SYSTEM_PATH = 'database/migrations/system';
    public const TENANT_PATH = 'database/migrations/tenants';

    public static function system(?string $path = null): string
    {
        return self::normalizeWithinScope($path, self::SYSTEM_PATH);
    }

    public static function tenant(?string $path = null): string
    {
        return self::normalizeWithinScope($path, self::TENANT_PATH);
    }

    private static function normalizeWithinScope(?string $path, string $scope): string
    {
        $normalizedPath = str_replace('\\', '/', trim($path ?: $scope));
        $normalizedScope = str_replace('\\', '/', trim($scope));

        if ($normalizedPath === $normalizedScope) {
            return $normalizedScope;
        }

        if (str_contains($normalizedPath, '..')) {
            throw new InvalidArgumentException("Migration path [{$path}] cannot traverse directories.");
        }

        if (!str_starts_with($normalizedPath, $normalizedScope . '/')) {
            throw new InvalidArgumentException("Migration path [{$path}] must stay inside [{$normalizedScope}].");
        }

        return $normalizedPath;
    }
}
