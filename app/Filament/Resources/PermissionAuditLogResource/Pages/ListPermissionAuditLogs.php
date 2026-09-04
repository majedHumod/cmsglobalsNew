<?php

namespace App\Filament\Resources\PermissionAuditLogResource\Pages;

use App\Filament\Resources\PermissionAuditLogResource;
use Filament\Resources\Pages\ListRecords;

class ListPermissionAuditLogs extends ListRecords
{
    protected static string $resource = PermissionAuditLogResource::class;
}
