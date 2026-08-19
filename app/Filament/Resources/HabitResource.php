<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HabitResource\Pages;
use App\Models\Habit;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HabitResource extends Resource
{
    protected static ?string $model = Habit::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $navigationGroup = 'التواصل والمتابعة';

    protected static ?string $navigationLabel = 'متابعة العادات';

    protected static ?string $modelLabel = 'عادة';

    protected static ?string $pluralModelLabel = 'العادات';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'coach']) ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }
        if ($user->hasRole('admin')) {
            return true;
        }

        if ((int) $record->created_by_user_id === (int) $user->id) {
            return true;
        }

        $client = $record->client;

        return $client ? $user->isCoachOf($client) : false;
    }

    public static function canDelete(Model $record): bool
    {
        return static::canEdit($record);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('العادة')
                    ->schema([
                        Forms\Components\Select::make('client_user_id')
                            ->label('العميل')
                            ->options(function () {
                                $query = User::query()->clients()->orderBy('name');
                                $user = auth()->user();
                                if ($user?->hasRole('coach') && ! $user->hasRole('admin')) {
                                    $query->where('coach_id', $user->id);
                                }

                                return $query->pluck('name', 'id');
                            })
                            ->searchable()
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->maxLength(120),
                        Forms\Components\TextInput::make('unit')
                            ->label('الوحدة')
                            ->maxLength(50)
                            ->placeholder('مثلاً: أكواب / دقائق'),
                        Forms\Components\TextInput::make('target_value')
                            ->label('الهدف')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(100000)
                            ->default(1),
                        Forms\Components\Toggle::make('is_active')
                            ->label('نشطة')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client.name')
                    ->label('العميل')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('العادة')
                    ->searchable(),
                Tables\Columns\TextColumn::make('target_value')
                    ->label('الهدف')
                    ->formatStateUsing(fn (Habit $record): string => trim($record->target_value.' '.($record->unit ?? ''))),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('أنشأها')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشطة')
                    ->boolean(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('نشطة'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\Action::make('toggle')
                    ->label(fn (Habit $record): string => $record->is_active ? 'إيقاف' : 'تفعيل')
                    ->action(fn (Habit $record) => $record->update(['is_active' => ! $record->is_active])),
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
        $query = parent::getEloquentQuery()->with(['client', 'creator']);
        $user = auth()->user();

        if ($user?->hasRole('coach') && ! $user->hasRole('admin')) {
            $query->where(function (Builder $inner) use ($user) {
                $inner->where('created_by_user_id', $user->id)
                    ->orWhereHas('client', fn (Builder $client) => $client->where('coach_id', $user->id));
            });
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHabits::route('/'),
            'create' => Pages\CreateHabit::route('/create'),
            'edit' => Pages\EditHabit::route('/{record}/edit'),
        ];
    }
}
