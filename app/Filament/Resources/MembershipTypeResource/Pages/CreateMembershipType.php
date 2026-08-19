<?php

namespace App\Filament\Resources\MembershipTypeResource\Pages;

use App\Filament\Resources\MembershipTypeResource;
use App\Filament\Resources\SubscriptionPlanResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMembershipType extends CreateRecord
{
    protected static string $resource = MembershipTypeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slug'] = MembershipTypeResource::uniqueSlug((string) $data['name']);
        $data['is_active'] = (bool) ($data['is_active'] ?? true);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['price'] = 0;
        $data['duration_days'] = 30;
        $data['features'] = null;
        $data['is_protected'] = false;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return SubscriptionPlanResource::getUrl('create', [
            'membership_type_id' => $this->record->id,
        ]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إنشاء مسار العضوية. أضف الآن خطة اشتراك لهذا المسار.';
    }
}
