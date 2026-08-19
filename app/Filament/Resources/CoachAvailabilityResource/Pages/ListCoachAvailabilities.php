<?php

namespace App\Filament\Resources\CoachAvailabilityResource\Pages;

use App\Filament\Resources\CoachAvailabilityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCoachAvailabilities extends ListRecords
{
    protected static string $resource = CoachAvailabilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('إضافة وقت توفر'),
        ];
    }
}
