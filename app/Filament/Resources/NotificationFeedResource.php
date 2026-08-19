<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationFeedResource\Pages;
use App\Models\NotificationFeed;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class NotificationFeedResource extends Resource
{
    protected static ?string $model = NotificationFeed::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell';

    protected static ?string $navigationGroup = 'التواصل والمتابعة';

    protected static ?string $navigationLabel = 'الإشعارات';

    protected static ?string $modelLabel = 'إشعار';

    protected static ?string $pluralModelLabel = 'الإشعارات';

    protected static ?int $navigationSort = 5;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'coach']) ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return (int) $record->user_id === (int) auth()->id();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->weight(fn (NotificationFeed $record) => $record->read_at ? null : 'bold'),
                Tables\Columns\TextColumn::make('body')
                    ->label('التفاصيل')
                    ->limit(60)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('read_at')
                    ->label('مقروء')
                    ->boolean()
                    ->getStateUsing(fn (NotificationFeed $record): bool => filled($record->read_at)),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('unread')
                    ->label('غير مقروء')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNull('read_at'),
                        false: fn (Builder $query) => $query->whereNotNull('read_at'),
                        blank: fn (Builder $query) => $query,
                    ),
                Tables\Filters\SelectFilter::make('type')
                    ->label('النوع')
                    ->options(fn () => NotificationFeed::query()
                        ->where('user_id', auth()->id())
                        ->distinct()
                        ->orderBy('type')
                        ->pluck('type', 'type')
                        ->all()),
            ])
            ->actions([
                Tables\Actions\Action::make('markRead')
                    ->label('تعيين كمقروء')
                    ->icon('heroicon-o-check')
                    ->visible(fn (NotificationFeed $record): bool => blank($record->read_at))
                    ->action(fn (NotificationFeed $record) => $record->update(['read_at' => now()])),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('markAllRead')
                    ->label('تعليميم الكل كمقروء')
                    ->icon('heroicon-o-check-circle')
                    ->action(function (): void {
                        NotificationFeed::query()
                            ->where('user_id', auth()->id())
                            ->whereNull('read_at')
                            ->update(['read_at' => now()]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('markReadBulk')
                        ->label('تعيين كمقروء')
                        ->action(fn ($records) => $records->each->update(['read_at' => now()])),
                    Tables\Actions\DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotificationFeeds::route('/'),
        ];
    }
}
