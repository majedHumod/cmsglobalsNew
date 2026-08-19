<?php

namespace App\Filament\Resources\CoachAvailabilityResource\Pages;

use App\Filament\Resources\CoachAvailabilityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCoachAvailability extends EditRecord
{
    protected static string $resource = CoachAvailabilityResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! (auth()->user()?->hasRole('admin') ?? false)) {
            $data['user_id'] = auth()->id();
        }

        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('حذف'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
