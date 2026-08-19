<?php

namespace App\Filament\Resources\WorkoutScheduleResource\Pages;

use App\Filament\Resources\WorkoutScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWorkoutSchedule extends EditRecord
{
    protected static string $resource = WorkoutScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('حذف'),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return WorkoutScheduleResource::mutateScheduleData($data);
    }

    protected function getRedirectUrl(): string
    {
        $week = (int) ($this->record->week_number ?? 1);

        return $this->getResource()::getUrl('index', ['week' => $week]);
    }
}
