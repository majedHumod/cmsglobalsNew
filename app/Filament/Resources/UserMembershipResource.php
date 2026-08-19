<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\AdminOnlyResource;
use App\Filament\Resources\UserMembershipResource\Pages;
use App\Models\MembershipType;
use App\Models\SubscriptionPlan;
use App\Models\UserMembership;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserMembershipResource extends Resource
{
    use AdminOnlyResource;

    protected static ?string $model = UserMembership::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'العضويات والاشتراكات';

    protected static ?string $navigationLabel = 'اشتراكات الأعضاء';

    protected static ?string $modelLabel = 'اشتراك عضو';

    protected static ?string $pluralModelLabel = 'اشتراكات الأعضاء';

    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('العضوية')
                    ->schema([
                        Forms\Components\Placeholder::make('member')
                            ->label('العضو')
                            ->content(fn (?UserMembership $record): string => $record?->user?->name ?? '—'),
                        Forms\Components\Select::make('membership_type_id')
                            ->label('مسار العضوية')
                            ->options(fn () => MembershipType::query()->ordered()->pluck('name', 'id'))
                            ->searchable()
                            ->native(false)
                            ->required(),
                        Forms\Components\Select::make('subscription_plan_id')
                            ->label('خطة الاشتراك')
                            ->options(fn () => SubscriptionPlan::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->native(false),
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('تاريخ البدء')
                            ->required()
                            ->native(false),
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('تاريخ الانتهاء')
                            ->required()
                            ->native(false),
                        Forms\Components\Toggle::make('is_active')
                            ->label('نشط'),
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
                        Forms\Components\TextInput::make('payment_amount')
                            ->label('مبلغ الدفع')
                            ->numeric()
                            ->minValue(0),
                        Forms\Components\TextInput::make('payment_reference')
                            ->label('مرجع الدفع')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(3)
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
                    ->label('العضو')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('membershipType.name')
                    ->label('المسار')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('subscriptionPlan.name')
                    ->label('الخطة')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('البدء')
                    ->dateTime('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('الانتهاء')
                    ->dateTime('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status_text')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (UserMembership $record): string => match (true) {
                        ! $record->is_active => 'gray',
                        $record->is_expired => 'danger',
                        $record->payment_status !== 'paid' => 'warning',
                        default => 'success',
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
                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
            ])
            ->defaultSort('expires_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('الدفع')
                    ->options([
                        'pending' => 'معلق',
                        'paid' => 'مدفوع',
                        'failed' => 'فشل',
                        'refunded' => 'مسترد',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')->label('نشط'),
                Tables\Filters\SelectFilter::make('membership_type_id')
                    ->label('المسار')
                    ->options(fn () => MembershipType::query()->ordered()->pluck('name', 'id')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('إدارة'),
            ])
            ->bulkActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user', 'membershipType', 'subscriptionPlan']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserMemberships::route('/'),
            'edit' => Pages\EditUserMembership::route('/{record}/edit'),
        ];
    }
}
