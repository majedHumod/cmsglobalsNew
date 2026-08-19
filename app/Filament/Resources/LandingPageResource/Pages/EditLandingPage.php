<?php

namespace App\Filament\Resources\LandingPageResource\Pages;

use App\Filament\Resources\LandingPageResource;
use App\Models\LandingPage;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLandingPage extends EditRecord
{
    protected static string $resource = LandingPageResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return LandingPageResource::syncActiveState($this->record, $data);
    }

    protected function afterSave(): void
    {
        LandingPage::clearCache();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('حذف')
                ->after(fn () => LandingPage::clearCache()),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
