<?php

namespace App\Filament\Resources\ExerciseResource\Pages;

use App\Filament\Resources\ExerciseResource;
use App\Models\Exercise;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewExercise extends ViewRecord
{
    protected static string $resource = ExerciseResource::class;

    public function getTitle(): string
    {
        /** @var Exercise $record */
        $record = $this->getRecord();

        return 'تفاصيل الحركة: '.($record->localized_name ?: $record->name);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('تعديل')
                ->visible(fn (): bool => ExerciseResource::canEdit($this->getRecord())),
            Actions\DeleteAction::make()
                ->label('حذف')
                ->visible(fn (): bool => ExerciseResource::canDelete($this->getRecord())),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('بيانات الحركة')
                    ->schema([
                        Infolists\Components\ImageEntry::make('image_start_path')
                            ->label('الصورة')
                            ->disk('public')
                            ->hidden(fn (Exercise $record): bool => blank($record->image_start_path)),
                        Infolists\Components\TextEntry::make('name')
                            ->label('الاسم')
                            ->formatStateUsing(fn (Exercise $record): string => $record->localized_name ?: $record->name),
                        Infolists\Components\TextEntry::make('source')
                            ->label('المصدر')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => $state === Exercise::SOURCE_REPDB ? 'RepDB' : 'مخصص'),
                        Infolists\Components\TextEntry::make('body_part')
                            ->label('جزء الجسم')
                            ->formatStateUsing(fn (Exercise $record): string => $record->localized_body_part ?: '—'),
                        Infolists\Components\TextEntry::make('equipment')
                            ->label('المعدات')
                            ->formatStateUsing(fn (Exercise $record): string => $record->localized_equipment ?: '—'),
                        Infolists\Components\TextEntry::make('difficulty')
                            ->label('المستوى')
                            ->formatStateUsing(fn (Exercise $record): string => $record->difficulty_name),
                        Infolists\Components\TextEntry::make('category')
                            ->label('التصنيف')
                            ->placeholder('—'),
                        Infolists\Components\IconEntry::make('status')
                            ->label('نشط')
                            ->boolean(),
                        Infolists\Components\TextEntry::make('description')
                            ->label('الوصف')
                            ->formatStateUsing(fn (Exercise $record): string => $record->localized_description ?: ($record->description ?: '—'))
                            ->columnSpanFull(),
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
