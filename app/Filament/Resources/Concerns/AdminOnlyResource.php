<?php

namespace App\Filament\Resources\Concerns;

trait AdminOnlyResource
{
    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }
}
