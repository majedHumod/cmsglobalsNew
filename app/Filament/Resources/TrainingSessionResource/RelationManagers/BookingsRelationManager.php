<?php

namespace App\Filament\Resources\TrainingSessionResource\RelationManagers;

use App\Filament\Resources\SessionBookingResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class BookingsRelationManager extends RelationManager
{
    protected static string $relationship = 'bookings';

    protected static ?string $title = 'حجوزات هذه الجلسة';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('العميل'),
                Tables\Columns\TextColumn::make('booking_date')->label('التاريخ')->date('d/m/Y'),
                Tables\Columns\TextColumn::make('booking_time')->label('الوقت')->time('H:i'),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'confirmed' => 'مؤكد',
                        'completed' => 'مكتمل',
                        'cancelled' => 'ملغي',
                        default => 'قيد الانتظار',
                    }),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('الدفع')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'paid' => 'مدفوع',
                        'failed' => 'فشل',
                        'refunded' => 'مسترد',
                        default => 'معلق',
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('manage')
                    ->label('إدارة')
                    ->url(fn ($record): string => SessionBookingResource::getUrl('edit', ['record' => $record])),
            ])
            ->defaultSort('booking_date', 'desc');
    }
}
