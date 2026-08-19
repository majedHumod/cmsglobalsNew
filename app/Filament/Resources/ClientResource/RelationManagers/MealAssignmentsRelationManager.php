<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Models\ClientMealPlan;
use App\Models\MealPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MealAssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'clientMealAssignments';

    protected static ?string $title = 'النظام الغذائي للعميل';

    protected static ?string $modelLabel = 'وجبة معيّنة';

    protected static ?string $pluralModelLabel = 'الوجبات المعيّنة';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('meal_slot')
                    ->label('توقيت الوجبة')
                    ->options($this->mealSlotOptions())
                    ->native(false)
                    ->nullable()
                    ->afterStateHydrated(fn (Forms\Components\Select $component, $state) => $component->state($state ?? ''))
                    ->dehydrateStateUsing(fn ($state) => $state === '' || $state === null ? null : $state),
                Forms\Components\TextInput::make('sort_order')
                    ->label('الترتيب')
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
                Forms\Components\Toggle::make('is_active')
                    ->label('مفعّل')
                    ->default(true),
                Forms\Components\Textarea::make('notes')
                    ->label('ملاحظات')
                    ->rows(3)
                    ->maxLength(1000)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['mealPlan', 'assignedBy']))
            ->recordTitleAttribute('id')
            ->description('اختر وجبات من المكتبة لتعيينها لهذا العميل. إن وُجدت تعيينات، سيظهر للعميل فقط ما عيّنته (بدل المكتبة الكاملة).')
            ->columns([
                Tables\Columns\TextColumn::make('mealPlan.name')
                    ->label('الوجبة')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('mealPlan.meal_type')
                    ->label('نوع المكتبة')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'breakfast' => 'إفطار',
                        'lunch' => 'غداء',
                        'dinner' => 'عشاء',
                        'snack' => 'سناك',
                        default => $state ?: '—',
                    })
                    ->badge(),
                Tables\Columns\TextColumn::make('meal_slot')
                    ->label('التوقيت المعيّن')
                    ->formatStateUsing(fn (?string $state): string => $this->mealSlotOptions()[$state ?? ''] ?? 'أي وقت')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('mealPlan.calories')
                    ->label('السعرات')
                    ->suffix(' سعرة')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('مفعّل')
                    ->boolean(),
                Tables\Columns\TextColumn::make('assignedBy.name')
                    ->label('عيّن بواسطة')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('meal_slot')
            ->headerActions([
                Tables\Actions\Action::make('assignMeals')
                    ->label('تعيين وجبات')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('تعيين وجبات من المكتبة')
                    ->modalDescription('إن وُجدت تعيينات، سيظهر للعميل فقط ما عيّنته بدل المكتبة الكاملة.')
                    ->form([
                        Forms\Components\Select::make('meal_plan_ids')
                            ->label('وجبات من المكتبة')
                            ->multiple()
                            ->required()
                            ->searchable()
                            ->preload()
                            ->options(fn (): array => MealPlan::query()
                                ->where('is_active', true)
                                ->orderBy('meal_type')
                                ->orderBy('name')
                                ->limit(500)
                                ->get()
                                ->mapWithKeys(function (MealPlan $meal): array {
                                    $type = $meal->meal_type_name;
                                    $calories = $meal->calories ? " ({$meal->calories} سعرة)" : '';
                                    $en = $meal->name_en ? " — {$meal->name_en}" : '';

                                    return [$meal->id => "[{$type}] {$meal->name}{$en}{$calories}"];
                                })
                                ->all())
                            ->helperText('يمكن اختيار أكثر من وجبة دفعة واحدة.'),
                        Forms\Components\Select::make('meal_slot')
                            ->label('توقيت الوجبة (اختياري)')
                            ->options($this->mealSlotOptions())
                            ->native(false)
                            ->nullable()
                            ->dehydrateStateUsing(fn ($state) => $state === '' || $state === null ? null : $state),
                        Forms\Components\Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(3)
                            ->maxLength(1000)
                            ->placeholder('مثال: خطة خسارة وزن أسبوع 1'),
                    ])
                    ->action(function (array $data): void {
                        /** @var \App\Models\User $client */
                        $client = $this->getOwnerRecord();
                        $slot = ! empty($data['meal_slot']) ? $data['meal_slot'] : null;
                        $assigned = 0;

                        foreach ($data['meal_plan_ids'] as $mealPlanId) {
                            ClientMealPlan::query()->updateOrCreate(
                                [
                                    'user_id' => $client->id,
                                    'meal_plan_id' => (int) $mealPlanId,
                                    'meal_slot' => $slot,
                                ],
                                [
                                    'assigned_by' => auth()->id(),
                                    'notes' => $data['notes'] ?? null,
                                    'is_active' => true,
                                ]
                            );
                            $assigned++;
                        }

                        Notification::make()
                            ->title("تم تعيين {$assigned} وجبة ضمن نظام العميل الغذائي")
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
                Tables\Actions\DeleteAction::make()
                    ->label('إزالة'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('إزالة المحدد'),
                ]),
            ])
            ->emptyStateHeading('لم يُعيَّن نظام غذائي خاص بعد')
            ->emptyStateDescription('العميل يرى المكتبة العامة النشطة حتى يتم تعيين وجبات هنا.')
            ->emptyStateIcon('heroicon-o-cake');
    }

    /**
     * @return array<string, string>
     */
    private function mealSlotOptions(): array
    {
        return [
            '' => 'أي وقت',
            'breakfast' => 'إفطار',
            'lunch' => 'غداء',
            'dinner' => 'عشاء',
            'snack' => 'سناك',
        ];
    }
}
