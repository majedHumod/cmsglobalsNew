<?php

namespace App\Filament\Resources\CoachAvailabilityResource\Pages;

use App\Filament\Resources\CoachAvailabilityResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCoachAvailability extends CreateRecord
{
    protected static string $resource = CoachAvailabilityResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! (auth()->user()?->hasRole('admin') ?? false)) {
            $data['user_id'] = auth()->id();
        } elseif (empty($data['user_id'])) {
            $data['user_id'] = auth()->id();
        }

        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
