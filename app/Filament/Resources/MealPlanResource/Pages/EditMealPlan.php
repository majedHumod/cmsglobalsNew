<?php

namespace App\Filament\Resources\MealPlanResource\Pages;

use App\Filament\Resources\MealPlanResource;
use App\Models\MealPlan;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditMealPlan extends EditRecord
{
    protected static string $resource = MealPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('stockImage')
                ->label('صورة من المخزون')
                ->icon('heroicon-o-photo')
                ->color('gray')
                ->modalHeading('اختيار صورة من مصادر مفتوحة')
                ->visible(fn (): bool => $this->getRecord()->canReplaceImage(auth()->user()))
                ->form(fn (): array => MealPlanResource::stockImageFormSchema($this->getRecord()))
                ->action(function (array $data): void {
                    /** @var MealPlan $record */
                    $record = $this->getRecord();

                    if (! MealPlanResource::applyStockImageToMeal($record, $data)) {
                        Notification::make()->title('تعذر تنزيل الصورة')->danger()->send();

                        return;
                    }

                    Notification::make()->title('تم تحديث صورة الوجبة')->success()->send();
                    $this->refreshFormData(['image']);
                }),
            Actions\Action::make('deleteMeal')
                ->label('حذف')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->disabled(fn (): bool => ! MealPlanResource::canDelete($this->getRecord()))
                ->tooltip(fn (): ?string => $this->getRecord()->isFromLibrary()
                    ? 'وجبة من مكتبة الوجبات ولا يمكن تعديلها أو حذفها'
                    : null)
                ->action(function (): void {
                    /** @var MealPlan $record */
                    $record = $this->getRecord();
                    abort_unless(MealPlanResource::canDelete($record), 403);
                    $record->delete();
                    $this->redirect(MealPlanResource::getUrl('index'));
                }),
        ];
    }

    protected function getFormActions(): array
    {
        if (! MealPlanResource::canEdit($this->getRecord())) {
            return [
                $this->getCancelFormAction()->label('عودة'),
            ];
        }

        return parent::getFormActions();
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        /** @var MealPlan $record */
        $record = $this->getRecord();
        abort_unless(MealPlanResource::canEdit($record), 403, 'وجبة من مكتبة الوجبات ولا يمكن تعديلها.');

        return MealPlanResource::mutateMealPlanData($data);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        abort_unless(MealPlanResource::canEdit($record), 403);

        return parent::handleRecordUpdate($record, $data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
