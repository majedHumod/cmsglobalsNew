<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrainingSessionResource\Pages;
use App\Filament\Resources\TrainingSessionResource\RelationManagers;
use App\Models\MembershipType;
use App\Models\TrainingSession;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TrainingSessionResource extends Resource
{
    protected static ?string $model = TrainingSession::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'التدريب والحجوزات';

    protected static ?string $navigationLabel = 'جلسات التدريب';

    protected static ?string $modelLabel = 'جلسة تدريب';

    protected static ?string $pluralModelLabel = 'جلسات التدريب';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('بيانات الجلسة')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('العنوان')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('user_id')
                            ->label('المدرب')
                            ->options(fn () => User::query()->coaches()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->native(false)
                            ->required()
                            ->default(fn () => auth()->id())
                            ->visible(fn () => auth()->user()?->hasRole('admin') ?? false)
                            ->dehydrated(),
                        Forms\Components\Textarea::make('description')
                            ->label('الوصف')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('image')
                            ->label('الصورة')
                            ->image()
                            ->directory('training-sessions')
                            ->disk('public')
                            ->imageEditor()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('التفاصيل التشغيلية')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->label('السعر')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        Forms\Components\TextInput::make('duration_hours')
                            ->label('المدة (ساعات)')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(8)
                            ->default(1),
                        Forms\Components\Select::make('session_type')
                            ->label('نوع الجلسة')
                            ->options([
                                'online' => 'عن بعد',
                                'in_person' => 'حضوري',
                                'hybrid' => 'مختلط',
                            ])
                            ->required()
                            ->default('in_person')
                            ->native(false)
                            ->live(),
                        Forms\Components\TextInput::make('capacity')
                            ->label('السعة')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100)
                            ->default(1),
                        Forms\Components\TextInput::make('location')
                            ->label('الموقع')
                            ->maxLength(255)
                            ->visible(fn (Forms\Get $get): bool => in_array($get('session_type'), ['in_person', 'hybrid'], true)),
                        Forms\Components\TextInput::make('video_meeting_url')
                            ->label('رابط الاجتماع')
                            ->url()
                            ->maxLength(2048)
                            ->visible(fn (Forms\Get $get): bool => in_array($get('session_type'), ['online', 'hybrid'], true)),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('الترتيب')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        Forms\Components\Toggle::make('is_visible')
                            ->label('ظاهرة للجمهور')
                            ->default(true),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('الجمهور المستهدف')
                    ->schema([
                        Forms\Components\Select::make('audience_gender')
                            ->label('نطاق الجنس')
                            ->options([
                                'all' => 'الجميع',
                                'male' => 'رجال',
                                'female' => 'نساء',
                            ])
                            ->default('all')
                            ->native(false),
                        Forms\Components\Select::make('required_membership_types')
                            ->label('أنواع العضوية المطلوبة')
                            ->multiple()
                            ->options(fn () => MembershipType::query()->active()->ordered()->pluck('name', 'id'))
                            ->native(false)
                            ->helperText('اتركه فارغاً ليكون متاحاً لجميع المسارات.'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('الصورة')
                    ->disk('public')
                    ->square(),
                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('المدرب')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('session_type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'online' => 'عن بعد',
                        'hybrid' => 'مختلط',
                        default => 'حضوري',
                    }),
                Tables\Columns\TextColumn::make('price')
                    ->label('السعر')
                    ->formatStateUsing(fn (TrainingSession $record): string => $record->formatted_price),
                Tables\Columns\TextColumn::make('capacity')
                    ->label('السعة'),
                Tables\Columns\TextColumn::make('bookings_count')
                    ->label('الحجوزات')
                    ->counts('bookings'),
                Tables\Columns\TextColumn::make('next_session_date')
                    ->label('تاريخ الجلسة')
                    ->date('d/m/Y')
                    ->placeholder('—')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('next_session_date', $direction);
                    }),
                Tables\Columns\IconColumn::make('is_visible')
                    ->label('ظاهرة')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('session_type')
                    ->label('النوع')
                    ->options([
                        'online' => 'عن بعد',
                        'in_person' => 'حضوري',
                        'hybrid' => 'مختلط',
                    ]),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('المدرب')
                    ->options(fn () => User::query()->coaches()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->visible(fn () => auth()->user()?->hasRole('admin') ?? false),
                Tables\Filters\TernaryFilter::make('is_visible')->label('ظاهرة'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('عرض')
                    ->icon('heroicon-o-eye')
                    ->url(fn (TrainingSession $record): string => route('training-sessions.show', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\Action::make('toggleVisibility')
                    ->label(fn (TrainingSession $record): string => $record->is_visible ? 'إخفاء' : 'إظهار')
                    ->action(function (TrainingSession $record) {
                        $record->update(['is_visible' => ! $record->is_visible]);
                        TrainingSession::clearCache();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف')
                    ->after(fn () => TrainingSession::clearCache()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد')
                        ->after(fn () => TrainingSession::clearCache()),
                ]),
            ]);
    }

    public static function mutateSessionData(array $data): array
    {
        $data['is_visible'] = (bool) ($data['is_visible'] ?? false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['audience_gender'] = $data['audience_gender'] ?? 'all';
        $data['required_membership_types'] = array_values(array_filter(array_map(
            'intval',
            $data['required_membership_types'] ?? []
        )));

        if (! (auth()->user()?->hasRole('admin') ?? false)) {
            $data['user_id'] = auth()->id();
        } elseif (empty($data['user_id'])) {
            $data['user_id'] = auth()->id();
        }

        return $data;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withCount('bookings')
            ->addSelect([
                'next_session_date' => \App\Models\SessionBooking::query()
                    ->select('booking_date')
                    ->whereColumn('training_session_id', 'training_sessions.id')
                    ->whereDate('booking_date', '>=', now()->toDateString())
                    ->where('status', '!=', 'cancelled')
                    ->orderBy('booking_date')
                    ->orderBy('booking_time')
                    ->limit(1),
            ]);

        if (auth()->user()?->hasRole('coach') && ! auth()->user()?->hasRole('admin')) {
            $query->where('user_id', auth()->id());
        }

        return $query;
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\BookingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrainingSessions::route('/'),
            'create' => Pages\CreateTrainingSession::route('/create'),
            'edit' => Pages\EditTrainingSession::route('/{record}/edit'),
        ];
    }
}
