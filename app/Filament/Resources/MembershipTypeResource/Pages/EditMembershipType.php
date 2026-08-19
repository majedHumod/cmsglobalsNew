<?php

namespace App\Filament\Resources\MembershipTypeResource\Pages;

use App\Filament\Resources\MembershipTypeResource;
use App\Filament\Resources\SubscriptionPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMembershipType extends EditRecord
{
    protected static string $resource = MembershipTypeResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($this->record->is_protected) {
            $this->halt();
        }

        if (($data['name'] ?? null) && $data['name'] !== $this->record->name) {
            $data['slug'] = MembershipTypeResource::uniqueSlug((string) $data['name'], $this->record->id);
        }

        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('addPlan')
                ->label('إضافة خطة اشتراك')
                ->url(SubscriptionPlanResource::getUrl('create', [
                    'membership_type_id' => $this->record->id,
                ])),
            Actions\DeleteAction::make()
                ->label('حذف')
                ->visible(fn (): bool => $this->record->canBeDeleted()),
        ];
    }

    public function mount(int | string $record): void
    {
        parent::mount($record);

        if ($this->record->is_protected) {
            abort(403, 'لا يمكن تعديل هذا النوع من العضوية لأنه محمي من النظام.');
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
