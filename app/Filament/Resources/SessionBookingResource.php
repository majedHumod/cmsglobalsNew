<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SessionBookingResource\Pages;
use App\Models\SessionBooking;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SessionBookingResource extends Resource
{
    protected static ?string $model = SessionBooking::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'التدريب والحجوزات';

    protected static ?string $navigationLabel = 'الحجوزات';

    protected static ?string $modelLabel = 'حجز';

    protected static ?string $pluralModelLabel = 'الحجوزات';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات الحجز')
                    ->schema([
                        Forms\Components\Placeholder::make('client')
                            ->label('العميل')
                            ->content(fn (?SessionBooking $record): string => $record?->user?->name ?? '—'),
                        Forms\Components\Placeholder::make('session')
                            ->label('الجلسة')
                            ->content(fn (?SessionBooking $record): string => $record?->trainingSession?->title ?? '—'),
                        Forms\Components\Placeholder::make('when')
                            ->label('الموعد')
                            ->content(function (?SessionBooking $record): string {
                                if (! $record) {
                                    return '—';
                                }

                                $date = optional($record->booking_date)->format('d/m/Y');
                                $time = $record->booking_time ? \Carbon\Carbon::parse($record->booking_time)->format('H:i') : '';

                                return trim($date.' '.$time);
                            }),
                        Forms\Components\Placeholder::make('amount')
                            ->label('المبلغ')
                            ->content(fn (?SessionBooking $record): string => $record ? number_format((float) $record->payment_amount, 2).' ريال' : '—'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('إدارة الحالة')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('حالة الحجز')
                            ->options([
                                'pending' => 'قيد الانتظار',
                                'confirmed' => 'مؤكد',
                                'completed' => 'مكتمل',
                                'cancelled' => 'ملغي',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\Select::make('payment_status')
                            ->label('حالة الدفع')
                            ->options([
                                'pending' => 'معلق',
                                'paid' => 'مدفوع',
                                'failed' => 'فشل',
                                'refunded' => 'مسترد',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\Select::make('attendance_status')
                            ->label('حالة الحضور')
                            ->options([
                                'scheduled' => 'مجدول',
                                'attended' => 'حضر',
                                'missed' => 'غاب',
                                'late_cancelled' => 'إلغاء متأخر',
                            ])
                            ->native(false),
                        Forms\Components\TextInput::make('video_meeting_url')
                            ->label('رابط الاجتماع')
                            ->url()
                            ->maxLength(2048),
                        Forms\Components\Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('العميل')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('trainingSession.title')
                    ->label('الجلسة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('trainingSession.user.name')
                    ->label('المدرب')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('booking_date')
                    ->label('تاريخ الجلسة')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('booking_time')
                    ->label('الوقت')
                    ->formatStateUsing(fn ($state): string => $state ? \Carbon\Carbon::parse($state)->format('H:i') : '—'),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'confirmed' => 'success',
                        'completed' => 'info',
                        'cancelled' => 'danger',
                        default => 'warning',
                    })
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
                Tables\Columns\TextColumn::make('payment_amount')
                    ->label('المبلغ')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2).' ر.س')
                    ->toggleable(),
            ])
            ->defaultSort('booking_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('coach')
                    ->label('المدرب')
                    ->options(fn () => User::query()->coaches()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->query(function (Builder $query, array $data): Builder {
                        $coachId = $data['value'] ?? null;

                        if (blank($coachId)) {
                            return $query;
                        }

                        return $query->whereHas(
                            'trainingSession',
                            fn (Builder $sessionQuery) => $sessionQuery->where('user_id', $coachId)
                        );
                    })
                    ->visible(fn () => auth()->user()?->hasRole('admin') ?? false),
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'قيد الانتظار',
                        'confirmed' => 'مؤكد',
                        'completed' => 'مكتمل',
                        'cancelled' => 'ملغي',
                    ]),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('الدفع')
                    ->options([
                        'pending' => 'معلق',
                        'paid' => 'مدفوع',
                        'failed' => 'فشل',
                        'refunded' => 'مسترد',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('إدارة'),
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
        $query = parent::getEloquentQuery()->with(['user', 'trainingSession.user']);

        if (auth()->user()?->hasRole('coach') && ! auth()->user()?->hasRole('admin')) {
            $query->whereHas('trainingSession', fn ($q) => $q->where('user_id', auth()->id()));
        }

        return $query;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSessionBookings::route('/'),
            'edit' => Pages\EditSessionBooking::route('/{record}/edit'),
        ];
    }
}
