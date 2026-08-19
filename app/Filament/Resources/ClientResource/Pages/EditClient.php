<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use App\Models\ClientProfile;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClient extends EditRecord
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()->label('عودة للمتابعة'),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! (auth()->user()?->hasRole('admin') ?? false)) {
            unset($data['coach_id']);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        // Ensure profile row exists after relationship form save attempt.
        ClientProfile::query()->firstOrCreate(
            ['user_id' => $this->record->id],
            [
                'activity_level' => 'beginner',
                'preferred_contact_method' => 'whatsapp',
                'current_program_week' => 1,
            ]
        );
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
