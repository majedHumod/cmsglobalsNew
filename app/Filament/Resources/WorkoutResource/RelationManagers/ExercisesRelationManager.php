<?php

namespace App\Filament\Resources\WorkoutResource\RelationManagers;

use App\Models\Workout;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ExercisesRelationManager extends RelationManager
{
    protected static string $relationship = 'exercises';

    protected static ?string $title = 'حركات التمرين';

    protected static ?string $modelLabel = 'حركة';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('sets')
                    ->label('المجموعات')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(30)
                    ->default(3),
                Forms\Components\TextInput::make('reps')
                    ->label('التكرارات')
                    ->maxLength(50),
                Forms\Components\TextInput::make('rest_seconds')
                    ->label('الراحة (ثانية)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(600)
                    ->default(60),
                Forms\Components\TextInput::make('coach_cue')
                    ->label('ملاحظة المدرب')
                    ->maxLength(255),
                Forms\Components\TextInput::make('sort_order')
                    ->label('الترتيب')
                    ->numeric()
                    ->minValue(0)
                    ->default(fn (): int => $this->nextSortOrder()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الحركة')
                    ->formatStateUsing(fn ($record): string => $record->localized_name ?: (string) $record->name),
                Tables\Columns\TextColumn::make('pivot.sets')->label('مجموعات'),
                Tables\Columns\TextColumn::make('pivot.reps')->label('تكرارات'),
                Tables\Columns\TextColumn::make('pivot.rest_seconds')->label('راحة'),
                Tables\Columns\TextColumn::make('pivot.coach_cue')->label('ملاحظة')->limit(30),
                Tables\Columns\TextColumn::make('pivot.sort_order')->label('ترتيب'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('إضافة حركة')
                    ->preloadRecordSelect()
                    ->recordSelectOptionsQuery(fn ($query) => $query->active()->orderBy('name'))
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect()->label('الحركة'),
                        Forms\Components\TextInput::make('sets')->label('المجموعات')->numeric()->default(3)->minValue(1),
                        Forms\Components\TextInput::make('reps')->label('التكرارات'),
                        Forms\Components\TextInput::make('rest_seconds')->label('الراحة')->numeric()->default(60)->minValue(0),
                        Forms\Components\TextInput::make('coach_cue')->label('ملاحظة المدرب'),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('الترتيب')
                            ->numeric()
                            ->minValue(0)
                            ->default(fn (): int => $this->nextSortOrder())
                            ->helperText('يُضبط تلقائياً إذا كان الترتيب مستخدماً.'),
                    ])
                    ->using(function (array $data, Model $record): void {
                        /** @var Workout $workout */
                        $workout = $this->getOwnerRecord();
                        $sortOrder = $this->resolveUniqueSortOrder(
                            isset($data['sort_order']) && $data['sort_order'] !== '' && $data['sort_order'] !== null
                                ? (int) $data['sort_order']
                                : null
                        );

                        $workout->exercises()->attach($record->getKey(), [
                            'sets' => isset($data['sets']) && $data['sets'] !== '' ? (int) $data['sets'] : null,
                            'reps' => isset($data['reps']) && $data['reps'] !== '' ? (string) $data['reps'] : null,
                            'rest_seconds' => isset($data['rest_seconds']) && $data['rest_seconds'] !== '' ? (int) $data['rest_seconds'] : null,
                            'coach_cue' => isset($data['coach_cue']) && $data['coach_cue'] !== '' ? (string) $data['coach_cue'] : null,
                            'sort_order' => $sortOrder,
                        ]);
                    })
                    ->after(function (): void {
                        $this->getOwnerRecord()->syncExerciseCountFromLibrary();
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('viewExercise')
                    ->label('عرض التفاصيل')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Model $record): string => \App\Filament\Resources\ExerciseResource::getUrl('view', ['record' => $record])),
                Tables\Actions\EditAction::make()
                    ->label('تعديل')
                    ->using(function (Model $record, array $data): void {
                        /** @var Workout $workout */
                        $workout = $this->getOwnerRecord();
                        $requested = isset($data['sort_order']) && $data['sort_order'] !== ''
                            ? (int) $data['sort_order']
                            : (int) ($record->pivot->sort_order ?? 0);

                        $conflict = DB::table('workout_exercises')
                            ->where('workout_id', $workout->id)
                            ->where('sort_order', $requested)
                            ->where('exercise_id', '!=', $record->getKey())
                            ->exists();

                        $sortOrder = $conflict
                            ? $this->resolveUniqueSortOrder($requested)
                            : $requested;

                        $workout->exercises()->updateExistingPivot($record->getKey(), [
                            'sets' => isset($data['sets']) && $data['sets'] !== '' ? (int) $data['sets'] : null,
                            'reps' => isset($data['reps']) && $data['reps'] !== '' ? (string) $data['reps'] : null,
                            'rest_seconds' => isset($data['rest_seconds']) && $data['rest_seconds'] !== '' ? (int) $data['rest_seconds'] : null,
                            'coach_cue' => isset($data['coach_cue']) && $data['coach_cue'] !== '' ? (string) $data['coach_cue'] : null,
                            'sort_order' => $sortOrder,
                        ]);
                    }),
                Tables\Actions\DetachAction::make()
                    ->label('إزالة')
                    ->after(function (): void {
                        $this->getOwnerRecord()->syncExerciseCountFromLibrary();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make()
                    ->label('إزالة المحدد')
                    ->after(function (): void {
                        $this->getOwnerRecord()->syncExerciseCountFromLibrary();
                    }),
            ]);
    }

    protected function nextSortOrder(): int
    {
        $workoutId = $this->getOwnerRecord()->getKey();
        $max = DB::table('workout_exercises')
            ->where('workout_id', $workoutId)
            ->max('sort_order');

        return $max === null ? 0 : ((int) $max + 1);
    }

    protected function resolveUniqueSortOrder(?int $requested): int
    {
        $workoutId = $this->getOwnerRecord()->getKey();
        $sortOrder = $requested ?? $this->nextSortOrder();

        $exists = DB::table('workout_exercises')
            ->where('workout_id', $workoutId)
            ->where('sort_order', $sortOrder)
            ->exists();

        return $exists ? $this->nextSortOrder() : $sortOrder;
    }
}
