<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\ScopesToOwner;
use App\Filament\Resources\ExerciseResource\Pages;
use App\Models\Exercise;
use App\Services\ExerciseTranslationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ExerciseResource extends Resource
{
    use ScopesToOwner;

    protected static ?string $model = Exercise::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'التمارين';

    protected static ?string $navigationLabel = 'مكتبة التمارين';

    protected static ?string $modelLabel = 'تمرين';

    protected static ?string $pluralModelLabel = 'مكتبة التمارين';

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

        return $record->source === Exercise::SOURCE_CUSTOM
            && (int) $record->user_id === (int) $user->id;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'coach']) ?? false;
    }

    public static function canView(Model $record): bool
    {
        return static::canViewAny();
    }

    public static function canDelete(Model $record): bool
    {
        if ($record->source === Exercise::SOURCE_REPDB) {
            return false;
        }

        return static::canEdit($record);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('بيانات الحركة')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم الحركة')
                            ->required()
                            ->maxLength(255)
                            ->disabled(fn (?Exercise $record) => $record && $record->source === Exercise::SOURCE_REPDB && ! auth()->user()?->hasRole('admin')),
                        Forms\Components\Select::make('difficulty')
                            ->label('المستوى')
                            ->options([
                                'beginner' => 'مبتدئ',
                                'intermediate' => 'متوسط',
                                'advanced' => 'متقدم',
                                'easy' => 'سهل',
                                'medium' => 'متوسط',
                                'hard' => 'صعب',
                            ])
                            ->native(false),
                        Forms\Components\TextInput::make('category')
                            ->label('التصنيف')
                            ->maxLength(120),
                        Forms\Components\TextInput::make('equipment')
                            ->label('المعدات')
                            ->maxLength(120),
                        Forms\Components\TextInput::make('body_part')
                            ->label('جزء الجسم')
                            ->maxLength(120),
                        Forms\Components\TextInput::make('video_url')
                            ->label('رابط فيديو توضيحي')
                            ->url()
                            ->maxLength(2048),
                        Forms\Components\Textarea::make('description')
                            ->label('الوصف')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\TagsInput::make('instructions')
                            ->label('خطوات الأداء')
                            ->helperText('أدخل كل خطوة ثم اضغط Enter.')
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('image_start_path')
                            ->label('صورة توضيحية')
                            ->image()
                            ->directory('exercise-library/custom')
                            ->disk('public')
                            ->imageEditor()
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('status')
                            ->label('تفعيل الحركة في المكتبة')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_start_path')
                    ->label('الصورة')
                    ->disk('public')
                    ->square(),
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->searchLocalized($search);
                    })
                    ->formatStateUsing(fn (Exercise $record): string => $record->localized_name)
                    ->sortable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('source')
                    ->label('المصدر')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state === Exercise::SOURCE_REPDB ? 'RepDB' : 'مخصص'),
                Tables\Columns\TextColumn::make('body_part')
                    ->label('جزء الجسم')
                    ->formatStateUsing(fn (Exercise $record): string => $record->localized_body_part ?: '—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('equipment')
                    ->label('المعدات')
                    ->formatStateUsing(fn (Exercise $record): string => $record->localized_equipment ?: '—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('difficulty')
                    ->label('المستوى')
                    ->formatStateUsing(fn (Exercise $record): string => $record->difficulty_name)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('category')
                    ->label('التصنيف')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('status')
                    ->label('نشط')
                    ->boolean(),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('body_part')
                    ->label('جزء الجسم')
                    ->options(fn (): array => static::distinctAttributeOptions('body_part'))
                    ->searchable(),
                Tables\Filters\SelectFilter::make('equipment')
                    ->label('المعدات')
                    ->options(fn (): array => static::distinctAttributeOptions('equipment'))
                    ->searchable(),
                Tables\Filters\SelectFilter::make('difficulty')
                    ->label('المستوى')
                    ->options([
                        'beginner' => 'مبتدئ',
                        'intermediate' => 'متوسط',
                        'advanced' => 'متقدم',
                        'easy' => 'سهل',
                        'medium' => 'متوسط',
                        'hard' => 'صعب',
                    ]),
                Tables\Filters\SelectFilter::make('source')
                    ->label('المصدر')
                    ->options([
                        Exercise::SOURCE_CUSTOM => 'مخصص',
                        Exercise::SOURCE_REPDB => 'RepDB',
                    ]),
                Tables\Filters\TernaryFilter::make('status')->label('نشط'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('عرض'),
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف')
                    ->visible(fn (Exercise $record): bool => static::canDelete($record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ]);
    }

    /**
     * @return array<string, string>
     */
    protected static function distinctAttributeOptions(string $attribute): array
    {
        $service = app(ExerciseTranslationService::class);

        return Exercise::query()
            ->whereNotNull($attribute)
            ->where($attribute, '!=', '')
            ->distinct()
            ->orderBy($attribute)
            ->pluck($attribute)
            ->mapWithKeys(fn (string $value): array => [
                $value => $service->label($attribute, $value) ?: $value,
            ])
            ->all();
    }

    public static function mutateExerciseData(array $data): array
    {
        $data['status'] = (bool) ($data['status'] ?? false);
        $data['source'] = $data['source'] ?? Exercise::SOURCE_CUSTOM;
        $data = static::mutateOwnerData($data);

        return $data;
    }

    public static function getEloquentQuery(): Builder
    {
        // Library is shared; coaches see all for attaching to workouts.
        return parent::getEloquentQuery();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExercises::route('/'),
            'create' => Pages\CreateExercise::route('/create'),
            'view' => Pages\ViewExercise::route('/{record}'),
            'edit' => Pages\EditExercise::route('/{record}/edit'),
        ];
    }
}
