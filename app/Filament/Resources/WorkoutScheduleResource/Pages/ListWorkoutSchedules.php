<?php

namespace App\Filament\Resources\WorkoutScheduleResource\Pages;

use App\Filament\Resources\WorkoutScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkoutSchedules extends ListRecords
{
    protected static string $resource = WorkoutScheduleResource::class;

    protected static ?string $title = 'قائمة مواعيد التمارين';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('weeklyBoard')
                ->label('عرض الجدول')
                ->icon('heroicon-o-calendar-days')
                ->color('gray')
                ->url(WorkoutScheduleResource::getUrl('index')),
            Actions\CreateAction::make()->label('إضافة موعد تمرين'),
        ];
    }
}
