<?php

namespace App\Filament\Resources\MealPlanResource\Pages;

use App\Filament\Resources\MealPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMealPlans extends ListRecords
{
    protected static string $resource = MealPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('libraryReview')
                ->label('مراجعة المكتبة')
                ->icon('heroicon-o-book-open')
                ->url(MealPlanResource::getUrl('library-review')),
            Actions\CreateAction::make()->label('إضافة وجبة'),
        ];
    }
}
