<?php

namespace App\Filament\Resources\NutritionDiscountResource\Pages;

use App\Filament\Resources\NutritionDiscountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNutritionDiscounts extends ListRecords
{
    protected static string $resource = NutritionDiscountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('إضافة خصم'),
        ];
    }
}
