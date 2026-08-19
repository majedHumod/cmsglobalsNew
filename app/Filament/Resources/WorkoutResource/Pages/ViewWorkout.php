<?php

namespace App\Filament\Resources\WorkoutResource\Pages;

use App\Filament\Resources\WorkoutResource;
use App\Models\Workout;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewWorkout extends ViewRecord
{
    protected static string $resource = WorkoutResource::class;

    public function getTitle(): string
    {
        return 'تفاصيل التمرين: '.$this->getRecord()->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('تعديل')
                ->visible(fn (): bool => WorkoutResource::canEdit($this->getRecord())),
            Actions\DeleteAction::make()
                ->label('حذف')
                ->visible(fn (): bool => WorkoutResource::canDelete($this->getRecord())),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('بيانات التمرين')
                    ->schema([
                        Infolists\Components\ImageEntry::make('image')
                            ->label('الصورة')
                            ->disk('public')
                            ->hidden(fn (Workout $record): bool => blank($record->image)),
                        Infolists\Components\TextEntry::make('name')->label('الاسم'),
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('المدرب')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('description')
                            ->label('الوصف')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('duration')
                            ->label('المدة')
                            ->formatStateUsing(fn ($state): string => $state ? $state.' دقيقة' : '—'),
                        Infolists\Components\TextEntry::make('difficulty')
                            ->label('الصعوبة')
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'easy' => 'سهل',
                                'hard' => 'صعب',
                                default => 'متوسط',
                            }),
                        Infolists\Components\TextEntry::make('equipment_label')
                            ->label('المعدات')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('exercises_count')
                            ->label('عدد الحركات')
                            ->state(fn (Workout $record): int => $record->exercises()->count()),
                        Infolists\Components\IconEntry::make('status')
                            ->label('نشط')
                            ->boolean(),
                        Infolists\Components\TextEntry::make('video_url')
                            ->label('رابط الفيديو')
                            ->url(fn (?string $state): ?string => $state)
                            ->openUrlInNewTab()
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }
}
