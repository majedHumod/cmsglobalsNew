<?php

namespace App\Filament\Resources\MembershipTypeResource\RelationManagers;

use App\Filament\Resources\SubscriptionPlanResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionPlansRelationManager extends RelationManager
{
    protected static string $relationship = 'subscriptionPlans';

    protected static ?string $title = 'خطط الاشتراك المرتبطة';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('الخطة'),
                Tables\Columns\TextColumn::make('duration_days')->label('المدة')->formatStateUsing(fn ($state) => $state.' يوم'),
                Tables\Columns\TextColumn::make('price')
                    ->label('السعر')
                    ->formatStateUsing(fn ($record) => $record->formatted_price),
                Tables\Columns\TextColumn::make('gender_scope')
                    ->label('الجنس')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'male' => 'رجال',
                        'female' => 'نساء',
                        default => 'الجميع',
                    }),
                Tables\Columns\IconColumn::make('is_active')->label('مفعلة')->boolean(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('createPlan')
                    ->label('إضافة خطة')
                    ->url(fn (): string => SubscriptionPlanResource::getUrl('create', [
                        'membership_type_id' => $this->getOwnerRecord()->id,
                    ])),
            ])
            ->actions([
                Tables\Actions\Action::make('editPlan')
                    ->label('تعديل')
                    ->url(fn ($record): string => SubscriptionPlanResource::getUrl('edit', ['record' => $record])),
            ])
            ->defaultSort('sort_order');
    }
}
