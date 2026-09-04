<?php

namespace App\Filament\Resources\MealPlanResource\Pages;

use App\Filament\Resources\MealPlanResource;
use App\Models\MealPlan;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class LibraryReview extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = MealPlanResource::class;

    protected static string $view = 'filament.resources.meal-plan-resource.pages.library-review';

    protected static ?string $title = 'مراجعة مكتبة الوجبات';

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'coach']) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                MealPlan::query()
                    ->where('source', MealPlan::SOURCE_ARABIC_LIBRARY)
                    ->orderBy('id')
            )
            ->columns([
                Tables\Columns\TextColumn::make('image')
                    ->label('الصورة')
                    ->html()
                    ->formatStateUsing(function ($state, MealPlan $record): string {
                        $url = $record->image_url;
                        if (! $url) {
                            return '<span style="font-size:11px;color:#9ca3af;">—</span>';
                        }

                        return '<img src="'.e($url).'" alt="" width="48" height="48" style="width:48px;height:48px;object-fit:cover;border-radius:8px;" />';
                    }),
                Tables\Columns\TextColumn::make('name')->label('الاسم')->searchable()->limit(40),
                Tables\Columns\TextColumn::make('name_en')->label('EN')->toggleable()->limit(30),
                Tables\Columns\TextColumn::make('meal_type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'breakfast' => 'فطور',
                        'lunch' => 'غداء',
                        'dinner' => 'عشاء',
                        default => 'سناك',
                    }),
                Tables\Columns\TextColumn::make('calories')->label('سعرات'),
                Tables\Columns\IconColumn::make('is_active')->label('نشط')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('meal_type')
                    ->label('النوع')
                    ->options([
                        'breakfast' => 'فطور',
                        'lunch' => 'غداء',
                        'dinner' => 'عشاء',
                        'snack' => 'سناك',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('stockImage')
                    ->label('صورة مخزون')
                    ->icon('heroicon-o-photo')
                    ->color('info')
                    ->modalHeading(fn (MealPlan $record): string => 'صورة مخزون — '.$record->name)
                    ->visible(fn (MealPlan $record): bool => $record->canReplaceImage(auth()->user()))
                    ->form(fn (MealPlan $record): array => MealPlanResource::stockImageFormSchema($record))
                    ->action(function (MealPlan $record, array $data): void {
                        if (! MealPlanResource::applyStockImageToMeal($record, $data)) {
                            Notification::make()->title('تعذر تنزيل الصورة')->danger()->send();

                            return;
                        }

                        Notification::make()->title('تم تحديث صورة الوجبة')->success()->send();
                    }),
                Tables\Actions\ViewAction::make()
                    ->label('تفاصيل')
                    ->slideOver()
                    ->modalHeading(fn (MealPlan $record): string => $record->name)
                    ->infolist(fn (MealPlan $record): array => MealPlanResource::viewMealInfolist($record)),
            ])
            ->paginated([24, 48]);
    }
}
