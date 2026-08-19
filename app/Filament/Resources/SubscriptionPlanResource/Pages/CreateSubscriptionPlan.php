<?php

namespace App\Filament\Resources\SubscriptionPlanResource\Pages;

use App\Filament\Resources\SubscriptionPlanResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSubscriptionPlan extends CreateRecord
{
    protected static string $resource = SubscriptionPlanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return SubscriptionPlanResource::mutatePlanData($data);
    }

    protected function afterCreate(): void
    {
        SubscriptionPlanResource::forgetHomepagePlansCache();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إنشاء خطة الاشتراك بنجاح.';
    }
}
