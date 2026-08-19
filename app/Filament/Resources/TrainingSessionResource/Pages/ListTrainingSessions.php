<?php

namespace App\Filament\Resources\TrainingSessionResource\Pages;

use App\Filament\Resources\SessionBookingResource;
use App\Filament\Resources\TrainingSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTrainingSessions extends ListRecords
{
    protected static string $resource = TrainingSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('bookings')
                ->label('الاطلاع على الحجوزات')
                ->icon('heroicon-o-calendar-days')
                ->color('gray')
                ->url(SessionBookingResource::getUrl('index')),
            Actions\CreateAction::make()->label('إضافة جلسة'),
        ];
    }
}
