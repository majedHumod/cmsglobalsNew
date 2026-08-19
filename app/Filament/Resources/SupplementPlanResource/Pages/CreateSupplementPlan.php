<?php

namespace App\Filament\Resources\SupplementPlanResource\Pages;

use App\Filament\Resources\SupplementPlanResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSupplementPlan extends CreateRecord
{
    protected static string $resource = SupplementPlanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return SupplementPlanResource::mutateSupplementData($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
