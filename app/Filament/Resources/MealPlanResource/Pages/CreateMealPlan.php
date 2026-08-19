<?php

namespace App\Filament\Resources\MealPlanResource\Pages;

use App\Filament\Resources\MealPlanResource;
use App\Models\MealPlan;
use Filament\Resources\Pages\CreateRecord;

class CreateMealPlan extends CreateRecord
{
    protected static string $resource = MealPlanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = MealPlanResource::mutateMealPlanData($data);
        $data['source'] = MealPlan::SOURCE_CUSTOM;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
