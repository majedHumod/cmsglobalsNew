<?php

namespace App\Filament\Resources\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait ScopesToOwner
{
    public static function mutateOwnerData(array $data, string $ownerKey = 'user_id'): array
    {
        if (! (auth()->user()?->hasRole('admin') ?? false)) {
            $data[$ownerKey] = auth()->id();
        } elseif (empty($data[$ownerKey])) {
            $data[$ownerKey] = auth()->id();
        }

        return $data;
    }

    public static function scopeOwnerQuery(Builder $query, string $ownerKey = 'user_id'): Builder
    {
        if (auth()->user()?->hasRole('coach') && ! auth()->user()?->hasRole('admin')) {
            $query->where($ownerKey, auth()->id());
        }

        return $query;
    }
}
