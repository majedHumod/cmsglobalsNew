<?php

namespace App\Filament\Resources\WorkoutResource\Pages;

use App\Filament\Resources\WorkoutResource;
use App\Filament\Resources\WorkoutScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkouts extends ListRecords
{
    protected static string $resource = WorkoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('weeklySchedule')
                ->label('الجدول الأسبوعي')
                ->icon('heroicon-o-calendar')
                ->color('gray')
                ->url(WorkoutScheduleResource::getUrl('index')),
            Actions\CreateAction::make()->label('إضافة تمرين'),
        ];
    }
}
