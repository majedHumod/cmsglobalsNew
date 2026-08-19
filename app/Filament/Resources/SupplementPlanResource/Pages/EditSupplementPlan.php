<?php

namespace App\Filament\Resources\SupplementPlanResource\Pages;

use App\Filament\Resources\SupplementPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSupplementPlan extends EditRecord
{
    protected static string $resource = SupplementPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('حذف'),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return SupplementPlanResource::mutateSupplementData($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
