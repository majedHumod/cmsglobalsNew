<?php

namespace App\Filament\Resources\ExerciseResource\Pages;

use App\Filament\Resources\ExerciseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExercise extends EditRecord
{
    protected static string $resource = ExerciseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('حذف')
                ->visible(fn (): bool => ExerciseResource::canDelete($this->getRecord())),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = ExerciseResource::mutateExerciseData($data);

        /** @var \App\Models\Exercise $record */
        $record = $this->getRecord();
        if ($record->source === \App\Models\Exercise::SOURCE_CUSTOM) {
            $locale = app()->getLocale() ?: 'ar';
            $translations = $record->translations ?? [];
            $translations['name'][$locale] = $data['name'] ?? $record->name;
            if (! empty($data['description'])) {
                $translations['description'][$locale] = $data['description'];
            }
            if (! empty($data['instructions'])) {
                $translations['instructions'][$locale] = $data['instructions'];
            }
            $data['translations'] = $translations;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
