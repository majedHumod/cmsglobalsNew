<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\HasAudienceFields;
use App\Filament\Resources\Concerns\ScopesToOwner;
use App\Filament\Resources\SupplementPlanResource\Pages;
use App\Models\SupplementPlan;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SupplementPlanResource extends Resource
{
    use HasAudienceFields;
    use ScopesToOwner;

    protected static ?string $model = SupplementPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $navigationGroup = 'التغذية';

    protected static ?string $navigationLabel = 'خطط المكملات';

    protected static ?string $modelLabel = 'مكمل';

    protected static ?string $pluralModelLabel = 'خطط المكملات';

    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'coach']) ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        $user = auth()->user();

        return $user ? $record->canManage($user) : false;
    }

    public static function canDelete(Model $record): bool
    {
        return static::canEdit($record);
    }

    /**
     * @return array<string, string>
     */
    public static function supplementTypeOptions(): array
    {
        return [
            'protein' => 'بروتين',
            'vitamins' => 'فيتامينات',
            'minerals' => 'معادن',
            'pre_workout' => 'ما قبل التمرين',
            'post_workout' => 'ما بعد التمرين',
            'omega' => 'أوميغا',
            'general' => 'عام',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function timingOptions(): array
    {
        return [
            'morning' => 'صباحاً',
            'pre_workout' => 'قبل التمرين',
            'post_workout' => 'بعد التمرين',
            'night' => 'مساءً',
            'with_meal' => 'مع الوجبة',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('بيانات المكمل')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('supplement_type')
                            ->label('النوع')
                            ->options(static::supplementTypeOptions())
                            ->required()
                            ->default('general')
                            ->native(false),
                        Forms\Components\Select::make('timing')
                            ->label('التوقيت')
                            ->options(static::timingOptions())
                            ->native(false),
                        Forms\Components\TextInput::make('dosage')
                            ->label('الجرعة')
                            ->maxLength(120),
                        Forms\Components\TextInput::make('brand')
                            ->label('العلامة')
                            ->maxLength(120),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('الترتيب')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Select::make('user_id')
                            ->label('المالك')
                            ->options(fn () => User::query()->coaches()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->native(false)
                            ->default(fn () => auth()->id())
                            ->visible(fn () => auth()->user()?->hasRole('admin') ?? false)
                            ->dehydrated(),
                        Forms\Components\Textarea::make('description')
                            ->label('الوصف')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('instructions')
                            ->label('التعليمات')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('warnings')
                            ->label('تحذيرات')
                            ->rows(2)
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('image')
                            ->label('الصورة')
                            ->image()
                            ->directory('supplement-plans')
                            ->disk('public')
                            ->imageEditor()
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_active')->label('نشط')->default(true),
                    ])
                    ->columns(2),
                static::audienceSection(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')->label('الصورة')->disk('public')->square(),
                Tables\Columns\TextColumn::make('name')->label('الاسم')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('brand')
                    ->label('العلامة')
                    ->searchable()
                    ->toggleable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('supplement_type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => static::supplementTypeOptions()[$state] ?? ($state ?: 'عام')),
                Tables\Columns\TextColumn::make('timing')
                    ->label('التوقيت')
                    ->toggleable()
                    ->formatStateUsing(fn (?string $state): string => static::timingOptions()[$state] ?? ($state ?: '—')),
                Tables\Columns\TextColumn::make('dosage')
                    ->label('الجرعة')
                    ->toggleable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('المالك')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn () => auth()->user()?->hasRole('admin') ?? false),
                Tables\Columns\TextColumn::make('sort_order')->label('ترتيب')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('نشط')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->recordAction('viewSupplement')
            ->filters([
                Tables\Filters\SelectFilter::make('supplement_type')
                    ->label('النوع')
                    ->options(static::supplementTypeOptions()),
                Tables\Filters\SelectFilter::make('timing')
                    ->label('التوقيت')
                    ->options(static::timingOptions()),
                Tables\Filters\SelectFilter::make('brand')
                    ->label('العلامة')
                    ->options(fn () => SupplementPlan::query()
                        ->whereNotNull('brand')
                        ->where('brand', '!=', '')
                        ->orderBy('brand')
                        ->distinct()
                        ->pluck('brand', 'brand'))
                    ->searchable(),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('المالك')
                    ->options(fn () => User::query()->coaches()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->visible(fn () => auth()->user()?->hasRole('admin') ?? false),
                Tables\Filters\SelectFilter::make('audience_gender')
                    ->label('الجمهور')
                    ->options([
                        'all' => 'الكل',
                        'male' => 'رجال',
                        'female' => 'نساء',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')->label('نشط'),
            ])
            ->actions([
                Tables\Actions\Action::make('viewSupplement')
                    ->label('عرض')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->slideOver()
                    ->modalHeading(fn (SupplementPlan $record): string => $record->name)
                    ->modalWidth('xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('إغلاق')
                    ->infolist(fn (SupplementPlan $record): array => static::viewSupplementInfolist($record)),
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ]);
    }

    /**
     * @return array<int, Infolists\Components\Component>
     */
    public static function viewSupplementInfolist(SupplementPlan $record): array
    {
        $imageUrl = null;
        if (filled($record->image)) {
            $imageUrl = str_starts_with($record->image, 'http')
                ? $record->image
                : Storage::disk('public')->url($record->image);
            $root = request()?->getSchemeAndHttpHost();
            if ($root && str_starts_with($imageUrl, '/')) {
                $imageUrl = $root.$imageUrl;
            }
        }

        return [
            Infolists\Components\Section::make('نظرة عامة')
                ->schema([
                    Infolists\Components\ImageEntry::make('image')
                        ->label('الصورة')
                        ->hiddenLabel()
                        ->getStateUsing(fn (): ?string => $imageUrl)
                        ->height(160)
                        ->extraImgAttributes(['style' => 'object-fit:cover;border-radius:12px;width:100%;max-width:240px;'])
                        ->visible(fn (): bool => filled($imageUrl))
                        ->columnSpanFull(),
                    Infolists\Components\TextEntry::make('name')->label('الاسم'),
                    Infolists\Components\TextEntry::make('brand')
                        ->label('العلامة')
                        ->placeholder('—'),
                    Infolists\Components\TextEntry::make('supplement_type')
                        ->label('النوع')
                        ->badge()
                        ->formatStateUsing(fn (?string $state): string => static::supplementTypeOptions()[$state] ?? ($state ?: 'عام')),
                    Infolists\Components\TextEntry::make('timing')
                        ->label('التوقيت')
                        ->formatStateUsing(fn (?string $state): string => static::timingOptions()[$state] ?? ($state ?: '—')),
                    Infolists\Components\TextEntry::make('dosage')
                        ->label('الجرعة')
                        ->placeholder('—'),
                    Infolists\Components\TextEntry::make('user.name')
                        ->label('المالك')
                        ->placeholder('—'),
                    Infolists\Components\TextEntry::make('sort_order')->label('الترتيب'),
                    Infolists\Components\IconEntry::make('is_active')->label('نشط')->boolean(),
                    Infolists\Components\TextEntry::make('description')
                        ->label('الوصف')
                        ->placeholder('—')
                        ->columnSpanFull()
                        ->html()
                        ->formatStateUsing(fn (?string $state): string => filled($state)
                            ? nl2br(e($state))
                            : '—'),
                ])
                ->columns(2),
            Infolists\Components\Section::make('التعليمات والتحذيرات')
                ->schema([
                    Infolists\Components\TextEntry::make('instructions')
                        ->label('التعليمات')
                        ->placeholder('—')
                        ->columnSpanFull()
                        ->html()
                        ->formatStateUsing(fn (?string $state): string => filled($state)
                            ? nl2br(e($state))
                            : '—'),
                    Infolists\Components\TextEntry::make('warnings')
                        ->label('تحذيرات')
                        ->placeholder('—')
                        ->columnSpanFull()
                        ->html()
                        ->formatStateUsing(fn (?string $state): string => filled($state)
                            ? nl2br(e($state))
                            : '—'),
                ]),
        ];
    }

    public static function mutateSupplementData(array $data): array
    {
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data = static::mutateAudienceData($data);
        $data = static::mutateOwnerData($data);

        return $data;
    }

    public static function getEloquentQuery(): Builder
    {
        return static::scopeOwnerQuery(parent::getEloquentQuery()->with('user'));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupplementPlans::route('/'),
            'create' => Pages\CreateSupplementPlan::route('/create'),
            'edit' => Pages\EditSupplementPlan::route('/{record}/edit'),
        ];
    }
}
