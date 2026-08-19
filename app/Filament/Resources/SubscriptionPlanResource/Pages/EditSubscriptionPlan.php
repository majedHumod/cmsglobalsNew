<?php

namespace App\Filament\Resources\SubscriptionPlanResource\Pages;

use App\Filament\Resources\SubscriptionPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubscriptionPlan extends EditRecord
{
    protected static string $resource = SubscriptionPlanResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['features'] = SubscriptionPlanResource::featuresForForm($data['features'] ?? []);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return SubscriptionPlanResource::mutatePlanData($data, $this->record);
    }

    protected function afterSave(): void
    {
        SubscriptionPlanResource::forgetHomepagePlansCache();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('حذف')
                ->before(function () {
                    if ($this->record->memberships()->exists()) {
                        \Filament\Notifications\Notification::make()
                            ->title('لا يمكن حذف الخطة لوجود اشتراكات مرتبطة بها.')
                            ->danger()
                            ->send();

                        $this->halt();
                    }
                })
                ->after(fn () => SubscriptionPlanResource::forgetHomepagePlansCache()),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
