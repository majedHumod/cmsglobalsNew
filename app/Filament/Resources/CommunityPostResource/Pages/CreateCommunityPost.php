<?php

namespace App\Filament\Resources\CommunityPostResource\Pages;

use App\Filament\Resources\CommunityPostResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCommunityPost extends CreateRecord
{
    protected static string $resource = CommunityPostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['is_visible'] = (bool) ($data['is_visible'] ?? true);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
