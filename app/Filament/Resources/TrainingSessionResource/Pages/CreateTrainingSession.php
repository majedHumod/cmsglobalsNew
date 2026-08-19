<?php

namespace App\Filament\Resources\TrainingSessionResource\Pages;

use App\Filament\Resources\TrainingSessionResource;
use App\Models\TrainingSession;
use Filament\Resources\Pages\CreateRecord;

class CreateTrainingSession extends CreateRecord
{
    protected static string $resource = TrainingSessionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return TrainingSessionResource::mutateSessionData($data);
    }

    protected function afterCreate(): void
    {
        TrainingSession::clearCache();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
