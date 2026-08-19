<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\HasAudienceFields;
use App\Filament\Resources\Concerns\ScopesToOwner;
use App\Filament\Resources\WorkoutScheduleResource\Pages;
use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutSchedule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class WorkoutScheduleResource extends Resource
{
    use HasAudienceFields;
    use ScopesToOwner;

    protected static ?string $model = WorkoutSchedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'التمارين';

    protected static ?string $navigationLabel = 'الجدول الأسبوعي';

    protected static ?string $modelLabel = 'موعد تمرين';

    protected static ?string $pluralModelLabel = 'الجدول الأسبوعي';

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'coach']) ?? false;
    }

    public static function canCreate(): bool
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

        return (int) $record->user_id === (int) $user->id;
    }

    public static function canDelete(Model $record): bool
    {
        return static::canEdit($record);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('موعد التمرين')
                    ->schema([
                        Forms\Components\Select::make('workout_id')
                            ->label('التمرين')
                            ->options(function () {
                                $query = Workout::query()->where('status', true)->orderBy('name');
                                if (auth()->user()?->hasRole('coach') && ! auth()->user()?->hasRole('admin')) {
                                    $query->where('user_id', auth()->id());
                                }

                                return $query->pluck('name', 'id');
                            })
                            ->searchable()
                            ->required()
                            ->native(false),
                        Forms\Components\Select::make('user_id')
                            ->label('المدرب')
                            ->options(fn () => User::query()->coaches()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->native(false)
                            ->default(fn () => auth()->id())
                            ->visible(fn () => auth()->user()?->hasRole('admin') ?? false)
                            ->dehydrated(),
                        Forms\Components\TextInput::make('week_number')
                            ->label('رقم الأسبوع')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(52)
                            ->default(1),
                        Forms\Components\Select::make('session_number')
                            ->label('اليوم / الجلسة')
                            ->options(Pages\WeeklyBoard::sessionDayLabels())
                            ->required()
                            ->native(false)
                            ->default(1),
                        Forms\Components\Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('status')
                            ->label('نشط')
                            ->default(true),
                    ])
                    ->columns(2),
                static::audienceSection(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('workout.name')
                    ->label('التمرين')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('week_number')
                    ->label('الأسبوع')
                    ->sortable(),
                Tables\Columns\TextColumn::make('session_number')
                    ->label('اليوم')
                    ->formatStateUsing(fn (?int $state): string => Pages\WeeklyBoard::sessionDayLabels()[$state] ?? (string) $state)
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('المدرب')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('status')
                    ->label('نشط')
                    ->boolean(),
            ])
            ->defaultSort('week_number')
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('المدرب')
                    ->options(fn () => User::query()->coaches()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->visible(fn () => auth()->user()?->hasRole('admin') ?? false),
                Tables\Filters\SelectFilter::make('week_number')
                    ->label('الأسبوع')
                    ->options(collect(range(1, 12))->mapWithKeys(fn (int $week) => [$week => "الأسبوع {$week}"]))
                    ->native(false),
                Tables\Filters\SelectFilter::make('session_number')
                    ->label('اليوم')
                    ->options(Pages\WeeklyBoard::sessionDayLabels()),
                Tables\Filters\TernaryFilter::make('status')->label('نشط'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ]);
    }

    public static function mutateScheduleData(array $data): array
    {
        $data['status'] = (bool) ($data['status'] ?? false);
        $data = static::mutateAudienceData($data);
        $data = static::mutateOwnerData($data);

        return $data;
    }

    public static function getEloquentQuery(): Builder
    {
        return static::scopeOwnerQuery(
            parent::getEloquentQuery()->with(['workout', 'user'])
        );
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\WeeklyBoard::route('/'),
            'list' => Pages\ListWorkoutSchedules::route('/list'),
            'create' => Pages\CreateWorkoutSchedule::route('/create'),
            'edit' => Pages\EditWorkoutSchedule::route('/{record}/edit'),
        ];
    }
}
