<?php

namespace App\Filament\Resources\MessageTemplateResource\Pages;

use App\Filament\Resources\MessageTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMessageTemplate extends CreateRecord
{
    protected static string $resource = MessageTemplateResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by_user_id'] = auth()->id();
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        if (! (auth()->user()?->hasRole('admin') ?? false)) {
            $data['category'] = $data['category'] === 'global' ? 'general' : ($data['category'] ?? 'general');
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
