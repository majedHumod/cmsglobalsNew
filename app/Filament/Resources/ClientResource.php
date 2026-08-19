<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ClientResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $slug = 'clients';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'التدريب والحجوزات';

    protected static ?string $navigationLabel = 'العملاء';

    protected static ?string $modelLabel = 'عميل';

    protected static ?string $pluralModelLabel = 'العملاء';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 4;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user?->hasAnyRole(['admin', 'coach']) ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('التعيين والمتابعة')
                    ->schema([
                        Forms\Components\Placeholder::make('name_display')
                            ->label('الاسم')
                            ->content(fn (?User $record): string => $record?->name ?? '—'),
                        Forms\Components\Placeholder::make('email_display')
                            ->label('البريد')
                            ->content(fn (?User $record): string => $record?->email ?? '—'),
                        Forms\Components\Select::make('coach_id')
                            ->label('المدرب المسؤول')
                            ->options(fn () => User::query()->coaches()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->native(false)
                            ->nullable()
                            ->visible(fn () => auth()->user()?->hasRole('admin') ?? false)
                            ->dehydrated(fn () => auth()->user()?->hasRole('admin') ?? false),
                        Forms\Components\Placeholder::make('coach_display')
                            ->label('المدرب المسؤول')
                            ->content(fn (?User $record): string => $record?->coach?->name ?? 'غير معيّن')
                            ->visible(fn () => ! (auth()->user()?->hasRole('admin') ?? false)),
                        Forms\Components\DatePicker::make('membership_expires_at')
                            ->label('انتهاء العضوية')
                            ->native(false),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('الملف الرياضي')
                    ->relationship('clientProfile')
                    ->schema([
                        Forms\Components\Textarea::make('fitness_goal')
                            ->label('الهدف')
                            ->rows(2)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('target_weight')
                            ->label('الوزن المستهدف (كجم)')
                            ->numeric()
                            ->minValue(0),
                        Forms\Components\Select::make('activity_level')
                            ->label('مستوى النشاط')
                            ->options([
                                'beginner' => 'مبتدئ',
                                'intermediate' => 'متوسط',
                                'advanced' => 'متقدم',
                            ])
                            ->default('beginner')
                            ->native(false),
                        Forms\Components\Select::make('preferred_contact_method')
                            ->label('وسيلة التواصل')
                            ->options([
                                'whatsapp' => 'واتساب',
                                'sms' => 'SMS',
                                'email' => 'بريد',
                                'phone' => 'هاتف',
                            ])
                            ->default('whatsapp')
                            ->native(false),
                        Forms\Components\Select::make('week_advance_mode')
                            ->label('انتقال أسابيع البرنامج')
                            ->options([
                                'auto' => 'تلقائي',
                                'manual' => 'يدوي (المدرب يتحكم)',
                            ])
                            ->placeholder('حسب إعدادات التدريب العامة')
                            ->native(false)
                            ->nullable()
                            ->helperText('عند اختيار يدوي يتوقف الانتقال التلقائي لهذا المتدرب.'),
                        Forms\Components\DateTimePicker::make('program_started_at')
                            ->label('بداية خطة التمرين')
                            ->native(false)
                            ->seconds(false)
                            ->helperText('يُضبط تلقائياً عند اعتماد الاشتراك إن كان التفعيل التلقائي مفعّلاً.'),
                        Forms\Components\TextInput::make('current_program_week')
                            ->label('أسبوع البرنامج الحالي')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(52)
                            ->default(1)
                            ->helperText('في الوضع التلقائي يُحدَّث هذا الرقم تلقائياً؛ عدّله يدوياً بعد اختيار الوضع اليدوي.'),
                        Forms\Components\Textarea::make('injuries')
                            ->label('إصابات / قيود')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('medical_notes')
                            ->label('ملاحظات إضافية')
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
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('البريد')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('الجوال')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('coach.name')
                    ->label('المدرب')
                    ->placeholder('غير معيّن')
                    ->sortable(),
                Tables\Columns\TextColumn::make('membership_expires_at')
                    ->label('انتهاء العضوية')
                    ->date('d/m/Y')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('التسجيل')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('coach_id')
                    ->label('المدرب')
                    ->options(fn () => User::query()->coaches()->orderBy('name')->pluck('name', 'id'))
                    ->visible(fn () => auth()->user()?->hasRole('admin') ?? false),
                Tables\Filters\Filter::make('unassigned')
                    ->label('بدون مدرب')
                    ->query(fn (Builder $query) => $query->whereNull('coach_id'))
                    ->visible(fn () => auth()->user()?->hasRole('admin') ?? false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('متابعة'),
                Tables\Actions\EditAction::make()->label('تعديل الملف'),
            ])
            ->bulkActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->clients()
            ->with(['coach', 'clientProfile', 'membershipType']);

        $user = auth()->user();
        if ($user?->hasRole('coach') && ! $user->hasRole('admin')) {
            $query->where('coach_id', $user->id);
        }

        return $query;
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\MealAssignmentsRelationManager::class,
            RelationManagers\ProgressCheckInsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'view' => Pages\ViewClient::route('/{record}'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
