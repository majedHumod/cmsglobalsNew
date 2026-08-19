<?php

namespace App\Filament\Resources\CommunityPostResource\Pages;

use App\Filament\Resources\CommunityPostResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCommunityPost extends EditRecord
{
    protected static string $resource = CommunityPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('حذف'),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['is_visible'] = (bool) ($data['is_visible'] ?? false);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
