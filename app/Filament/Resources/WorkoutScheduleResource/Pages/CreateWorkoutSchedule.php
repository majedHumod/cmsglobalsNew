<?php

namespace App\Filament\Resources\WorkoutScheduleResource\Pages;

use App\Filament\Resources\WorkoutScheduleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkoutSchedule extends CreateRecord
{
    protected static string $resource = WorkoutScheduleResource::class;

    public function mount(): void
    {
        parent::mount();

        $coachId = request()->query('coach');
        if (! (auth()->user()?->hasRole('admin') ?? false)) {
            $coachId = auth()->id();
        }

        $this->form->fill([
            'week_number' => max(1, min(52, (int) request()->integer('week', 1))),
            'session_number' => max(1, min(7, (int) request()->integer('session', 1))),
            'user_id' => filled($coachId) ? (int) $coachId : auth()->id(),
            'status' => true,
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return WorkoutScheduleResource::mutateScheduleData($data);
    }

    protected function getRedirectUrl(): string
    {
        $week = (int) ($this->record->week_number ?? 1);
        $params = ['week' => $week];

        if ((auth()->user()?->hasRole('admin') ?? false) && $this->record->user_id) {
            $params['coach'] = $this->record->user_id;
        }

        return $this->getResource()::getUrl('index', $params);
    }
}
