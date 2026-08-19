<?php

namespace App\Filament\Resources\ExerciseResource\Pages;

use App\Filament\Resources\ExerciseResource;
use App\Models\Exercise;
use Filament\Resources\Pages\CreateRecord;

class CreateExercise extends CreateRecord
{
    protected static string $resource = ExerciseResource::class;

    public function getTitle(): string
    {
        return 'إضافة حركة جديدة';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = ExerciseResource::mutateExerciseData($data);
        $data['source'] = Exercise::SOURCE_CUSTOM;
        $data['external_id'] = Exercise::makeCustomExternalId((string) ($data['name'] ?? 'exercise'));
        $data['attribution_required'] = false;
        $data['attribution_text'] = null;
        $data['attribution_url'] = null;

        $locale = app()->getLocale() ?: 'ar';
        $instructions = $data['instructions'] ?? null;
        if (is_string($instructions)) {
            $instructions = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $instructions) ?: [])));
            $data['instructions'] = $instructions;
        }

        $data['translations'] = [
            'name' => [$locale => $data['name'] ?? ''],
            'description' => ! empty($data['description'])
                ? [$locale => $data['description']]
                : [],
            'instructions' => ! empty($instructions)
                ? [$locale => $instructions]
                : [],
        ];

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
