<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CoachAvailabilityResource\Pages;
use App\Models\CoachAvailability;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CoachAvailabilityResource extends Resource
{
    protected static ?string $model = CoachAvailability::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'التدريب والحجوزات';

    protected static ?string $navigationLabel = 'أوقات التوفر';

    protected static ?string $modelLabel = 'وقت توفر';

    protected static ?string $pluralModelLabel = 'أوقات التوفر';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('بيانات وقت التوفر')
                    ->description('حدد أيام وأوقات استقبال الحجوزات للمدرب.')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('المدرب')
                            ->options(fn () => User::query()->coaches()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->native(false)
                            ->required()
                            ->default(fn () => auth()->id())
                            ->visible(fn () => auth()->user()?->hasRole('admin') ?? false)
                            ->dehydrated(),
                        Forms\Components\Select::make('day_of_week')
                            ->label('اليوم')
                            ->options([
                                0 => 'الأحد',
                                1 => 'الإثنين',
                                2 => 'الثلاثاء',
                                3 => 'الأربعاء',
                                4 => 'الخميس',
                                5 => 'الجمعة',
                                6 => 'السبت',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\TimePicker::make('start_time')
                            ->label('من')
                            ->required()
                            ->seconds(false),
                        Forms\Components\TimePicker::make('end_time')
                            ->label('إلى')
                            ->required()
                            ->seconds(false)
                            ->after('start_time'),
                        Forms\Components\TextInput::make('slot_duration_minutes')
                            ->label('مدة الفترة (دقيقة)')
                            ->numeric()
                            ->required()
                            ->minValue(15)
                            ->maxValue(480)
                            ->default(60),
                        Forms\Components\TextInput::make('buffer_minutes')
                            ->label('فاصل بين الحجوزات (دقيقة)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(240)
                            ->default(0),
                        Forms\Components\TextInput::make('capacity')
                            ->label('السعة')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100)
                            ->default(1),
                        Forms\Components\TextInput::make('location')
                            ->label('الموقع')
                            ->maxLength(255),
                        Forms\Components\Toggle::make('is_active')
                            ->label('مفعّل')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('المدرب')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('day_of_week')
                    ->label('اليوم')
                    ->formatStateUsing(fn ($state): string => match ((int) $state) {
                        1 => 'الإثنين',
                        2 => 'الثلاثاء',
                        3 => 'الأربعاء',
                        4 => 'الخميس',
                        5 => 'الجمعة',
                        6 => 'السبت',
                        default => 'الأحد',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->label('من')
                    ->formatStateUsing(fn ($state): string => $state ? \Carbon\Carbon::parse($state)->format('H:i') : '—'),
                Tables\Columns\TextColumn::make('end_time')
                    ->label('إلى')
                    ->formatStateUsing(fn ($state): string => $state ? \Carbon\Carbon::parse($state)->format('H:i') : '—'),
                Tables\Columns\TextColumn::make('slot_duration_minutes')
                    ->label('مدة الفترة'),
                Tables\Columns\TextColumn::make('capacity')
                    ->label('السعة'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('مفعّل')
                    ->boolean(),
            ])
            ->defaultSort('day_of_week')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('مفعّل'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل'),
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
        $query = parent::getEloquentQuery()->with('user');

        if (auth()->user()?->hasRole('coach') && ! auth()->user()?->hasRole('admin')) {
            $query->where('user_id', auth()->id());
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoachAvailabilities::route('/'),
            'create' => Pages\CreateCoachAvailability::route('/create'),
            'edit' => Pages\EditCoachAvailability::route('/{record}/edit'),
        ];
    }
}
