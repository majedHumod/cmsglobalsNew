<?php

namespace App\Filament\Resources\Concerns;

use Illuminate\Support\Facades\Schema;

trait RequiresDatabaseTable
{
    /**
     * Table name required for this resource to be usable.
     * Override in the resource when needed.
     */
    protected static function requiredTable(): ?string
    {
        $model = static::getModel();

        return (new $model)->getTable();
    }

    public static function tablesReady(): bool
    {
        $table = static::requiredTable();
        if (! $table) {
            return true;
        }

        try {
            return Schema::hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }
}
