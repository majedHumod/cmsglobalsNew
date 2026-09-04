<?php

namespace App\Filament\Resources\UserPermissionOverrideResource\Pages;

use App\Filament\Resources\UserPermissionOverrideResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserPermissionOverrides extends ListRecords
{
    protected static string $resource = UserPermissionOverrideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('منح / منع صلاحية'),
        ];
    }
}
