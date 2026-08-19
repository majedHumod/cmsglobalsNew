<?php

namespace App\Filament\Resources\NutritionDiscountResource\Pages;

use App\Filament\Resources\NutritionDiscountResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNutritionDiscount extends EditRecord
{
    protected static string $resource = NutritionDiscountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('حذف'),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return NutritionDiscountResource::mutateDiscountData($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
