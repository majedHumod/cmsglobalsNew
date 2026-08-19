<?php

namespace App\Filament\Resources\WorkoutResource\Pages;

use App\Filament\Resources\WorkoutResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkout extends CreateRecord
{
    protected static string $resource = WorkoutResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return WorkoutResource::mutateWorkoutData($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
