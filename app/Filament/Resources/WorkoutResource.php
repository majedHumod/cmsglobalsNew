<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\HasAudienceFields;
use App\Filament\Resources\Concerns\ScopesToOwner;
use App\Filament\Resources\WorkoutResource\Pages;
use App\Filament\Resources\WorkoutResource\RelationManagers;
use App\Models\User;
use App\Models\Workout;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class WorkoutResource extends Resource
{
    use HasAudienceFields;
    use ScopesToOwner;

    protected static ?string $model = Workout::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationGroup = 'التمارين';

    protected static ?string $navigationLabel = 'التمارين الرياضية';

    protected static ?string $modelLabel = 'تمرين رياضي';

    protected static ?string $pluralModelLabel = 'التمارين الرياضية';

    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'coach']) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'coach']) ?? false;
    }

    public static function canView(Model $record): bool
    {
        return static::canViewAny();
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
                Forms\Components\Section::make('بيانات التمرين')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('user_id')
                            ->label('المدرب')
                            ->options(fn () => User::query()->coaches()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->native(false)
                            ->default(fn () => auth()->id())
                            ->visible(fn () => auth()->user()?->hasRole('admin') ?? false)
                            ->dehydrated(),
                        Forms\Components\Textarea::make('description')
                            ->label('الوصف')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('duration')
                            ->label('المدة (دقيقة)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(300)
                            ->default(45),
                        Forms\Components\Select::make('difficulty')
                            ->label('الصعوبة')
                            ->options([
                                'easy' => 'سهل',
                                'medium' => 'متوسط',
                                'hard' => 'صعب',
                            ])
                            ->default('medium')
                            ->native(false),
                        Forms\Components\TextInput::make('equipment_label')
                            ->label('المعدات')
                            ->maxLength(120),
                        Forms\Components\TextInput::make('video_url')
                            ->label('رابط الفيديو')
                            ->url()
                            ->maxLength(2048),
                        Forms\Components\FileUpload::make('image')
                            ->label('الصورة')
                            ->image()
                            ->directory('workouts')
                            ->disk('public')
                            ->imageEditor()
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
                Tables\Columns\ImageColumn::make('image')
                    ->label('الصورة')
                    ->disk('public')
                    ->square(),
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('المدرب')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('duration')
                    ->label('المدة')
                    ->suffix(' د'),
                Tables\Columns\TextColumn::make('difficulty')
                    ->label('الصعوبة')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'easy' => 'سهل',
                        'hard' => 'صعب',
                        default => 'متوسط',
                    }),
                Tables\Columns\TextColumn::make('exercises_count')
                    ->label('الحركات')
                    ->counts('exercises'),
                Tables\Columns\IconColumn::make('status')
                    ->label('نشط')
                    ->boolean(),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('المدرب')
                    ->options(fn () => User::query()->coaches()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->visible(fn () => auth()->user()?->hasRole('admin') ?? false),
                Tables\Filters\SelectFilter::make('difficulty')
                    ->label('الصعوبة')
                    ->options([
                        'easy' => 'سهل',
                        'medium' => 'متوسط',
                        'hard' => 'صعب',
                    ]),
                Tables\Filters\TernaryFilter::make('status')->label('نشط'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('عرض'),
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ]);
    }

    public static function mutateWorkoutData(array $data): array
    {
        $data['status'] = (bool) ($data['status'] ?? false);
        $data = static::mutateAudienceData($data);
        $data = static::mutateOwnerData($data);

        return $data;
    }

    public static function getEloquentQuery(): Builder
    {
        return static::scopeOwnerQuery(
            parent::getEloquentQuery()
                ->with(['user'])
                ->withCount('exercises')
        );
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ExercisesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkouts::route('/'),
            'create' => Pages\CreateWorkout::route('/create'),
            'view' => Pages\ViewWorkout::route('/{record}'),
            'edit' => Pages\EditWorkout::route('/{record}/edit'),
        ];
    }
}
