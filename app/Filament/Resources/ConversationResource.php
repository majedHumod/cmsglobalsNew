<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConversationResource\Pages;
use App\Filament\Resources\ConversationResource\RelationManagers;
use App\Models\Conversation;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ConversationResource extends Resource
{
    protected static ?string $model = Conversation::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'التواصل والمتابعة';

    protected static ?string $navigationLabel = 'الرسائل';

    protected static ?string $modelLabel = 'محادثة';

    protected static ?string $pluralModelLabel = 'الرسائل';

    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'coach']) ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return $record->participants()->where('user_id', auth()->id())->exists();
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('بدء محادثة')
                    ->schema([
                        Forms\Components\Select::make('recipient_user_id')
                            ->label('المستلم')
                            ->options(function () {
                                $user = auth()->user();
                                $query = User::query()->where('id', '!=', $user->id)->orderBy('name');

                                if ($user?->hasRole('coach') && ! $user->hasRole('admin')) {
                                    $query->clients()->where('coach_id', $user->id);
                                }

                                return $query->pluck('name', 'id');
                            })
                            ->searchable()
                            ->required()
                            ->native(false)
                            ->visibleOn('create'),
                        Forms\Components\TextInput::make('subject')
                            ->label('الموضوع')
                            ->maxLength(255)
                            ->visibleOn('create'),
                        Forms\Components\Textarea::make('message')
                            ->label('الرسالة')
                            ->required()
                            ->rows(5)
                            ->maxLength(5000)
                            ->visibleOn('create')
                            ->columnSpanFull(),
                        Forms\Components\Placeholder::make('subject_display')
                            ->label('الموضوع')
                            ->content(fn (?Conversation $record): string => $record?->subject ?: 'بدون موضوع')
                            ->visibleOn('edit'),
                        Forms\Components\Placeholder::make('participants_display')
                            ->label('المشاركون')
                            ->content(fn (?Conversation $record): string => $record
                                ? $record->users->pluck('name')->join('، ')
                                : '—')
                            ->visibleOn('edit'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subject')
                    ->label('الموضوع')
                    ->placeholder('بدون موضوع')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('users.name')
                    ->label('المشاركون')
                    ->badge()
                    ->separator('، '),
                Tables\Columns\TextColumn::make('messages_count')
                    ->label('الرسائل')
                    ->counts('messages'),
                Tables\Columns\TextColumn::make('last_message_at')
                    ->label('آخر رسالة')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('last_message_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make()->label('فتح'),
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
        return parent::getEloquentQuery()
            ->whereHas('participants', fn (Builder $query) => $query->where('user_id', auth()->id()))
            ->with(['users'])
            ->withCount('messages');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\MessagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConversations::route('/'),
            'create' => Pages\CreateConversation::route('/create'),
            'edit' => Pages\EditConversation::route('/{record}/edit'),
        ];
    }
}
