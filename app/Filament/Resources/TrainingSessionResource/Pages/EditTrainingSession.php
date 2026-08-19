<?php

namespace App\Filament\Resources\TrainingSessionResource\Pages;

use App\Filament\Resources\TrainingSessionResource;
use App\Models\TrainingSession;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTrainingSession extends EditRecord
{
    protected static string $resource = TrainingSessionResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return TrainingSessionResource::mutateSessionData($data);
    }

    protected function afterSave(): void
    {
        TrainingSession::clearCache();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view')
                ->label('عرض الجلسة')
                ->icon('heroicon-o-eye')
                ->url(fn (): string => route('training-sessions.show', $this->record))
                ->openUrlInNewTab(),
            Actions\DeleteAction::make()
                ->label('حذف')
                ->after(fn () => TrainingSession::clearCache()),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
