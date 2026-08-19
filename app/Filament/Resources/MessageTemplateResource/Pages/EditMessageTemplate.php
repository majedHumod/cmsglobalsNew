<?php

namespace App\Filament\Resources\MessageTemplateResource\Pages;

use App\Filament\Resources\MessageTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMessageTemplate extends EditRecord
{
    protected static string $resource = MessageTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('حذف'),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        if (! (auth()->user()?->hasRole('admin') ?? false)) {
            unset($data['category']);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
