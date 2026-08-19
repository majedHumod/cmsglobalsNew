<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommunityPostResource\Pages;
use App\Filament\Resources\CommunityPostResource\RelationManagers;
use App\Models\CommunityPost;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CommunityPostResource extends Resource
{
    protected static ?string $model = CommunityPost::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'التواصل والمتابعة';

    protected static ?string $navigationLabel = 'المجتمع';

    protected static ?string $modelLabel = 'منشور';

    protected static ?string $pluralModelLabel = 'منشورات المجتمع';

    protected static ?int $navigationSort = 6;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'coach']) ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'coach']) ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->hasRole('admin')
            || (int) $record->user_id === (int) auth()->id();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('المنشور')
                    ->schema([
                        Forms\Components\Placeholder::make('author')
                            ->label('الكاتب')
                            ->content(fn (?CommunityPost $record): string => $record?->user?->name ?? auth()->user()?->name ?? '—'),
                        Forms\Components\Textarea::make('content')
                            ->label('المحتوى')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_visible')
                            ->label('ظاهر للعامة')
                            ->default(true)
                            ->helperText('إخفاء المنشور يخفيه من واجهة المجتمع دون حذفه.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('الكاتب')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('content')
                    ->label('المحتوى')
                    ->limit(70)
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('comments_count')
                    ->label('تعليقات')
                    ->counts('comments'),
                Tables\Columns\TextColumn::make('reactions_count')
                    ->label('تفاعل')
                    ->counts('reactions'),
                Tables\Columns\IconColumn::make('is_visible')
                    ->label('ظاهر')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_visible')->label('ظاهر'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('إدارة'),
                Tables\Actions\Action::make('toggleVisibility')
                    ->label(fn (CommunityPost $record): string => $record->is_visible ? 'إخفاء' : 'إظهار')
                    ->action(fn (CommunityPost $record) => $record->update(['is_visible' => ! $record->is_visible])),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('user')->withCount(['comments', 'reactions']);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CommentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCommunityPosts::route('/'),
            'create' => Pages\CreateCommunityPost::route('/create'),
            'edit' => Pages\EditCommunityPost::route('/{record}/edit'),
        ];
    }
}
