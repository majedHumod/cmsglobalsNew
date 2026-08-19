<?php

namespace App\Filament\Resources\NutritionDiscountResource\Pages;

use App\Filament\Resources\NutritionDiscountResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNutritionDiscount extends CreateRecord
{
    protected static string $resource = NutritionDiscountResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return NutritionDiscountResource::mutateDiscountData($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
