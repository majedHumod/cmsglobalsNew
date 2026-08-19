<?php

namespace App\Filament\Resources\MealPlanResource\Pages;

use App\Filament\Resources\MealPlanResource;
use App\Models\MealPlan;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditMealPlan extends EditRecord
{
    protected static string $resource = MealPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
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
