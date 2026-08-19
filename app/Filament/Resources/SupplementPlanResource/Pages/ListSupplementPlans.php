<?php

namespace App\Filament\Resources\SupplementPlanResource\Pages;

use App\Filament\Resources\SupplementPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSupplementPlans extends ListRecords
{
    protected static string $resource = SupplementPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('إضافة مكمل'),
        ];
    }
}
