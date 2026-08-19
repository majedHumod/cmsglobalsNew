<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return PageResource::mutateAudienceData($data, $this->record);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view')
                ->label('عرض الصفحة')
                ->icon('heroicon-o-eye')
                ->url(fn (): string => route('pages.show', $this->record->slug))
                ->openUrlInNewTab()
                ->visible(fn (): bool => filled($this->record->slug)),
            Actions\Action::make('copyLink')
                ->label('نسخ الرابط')
                ->icon('heroicon-o-clipboard-document')
                ->color('gray')
                ->visible(fn (): bool => filled($this->record->slug))
                ->alpineClickHandler(fn (): string => PageResource::clipboardAlpineHandler(
                    route('pages.show', $this->record->slug)
                )),
            Actions\DeleteAction::make()->label('حذف'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
