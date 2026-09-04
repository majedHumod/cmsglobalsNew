<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\HasAudienceFields;
use App\Filament\Resources\Concerns\ScopesToOwner;
use App\Filament\Resources\MealPlanResource\Pages;
use App\Models\MealPlan;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class MealPlanResource extends Resource
{
    use HasAudienceFields;
    use ScopesToOwner;

    protected static ?string $model = MealPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-cake';

    protected static ?string $navigationGroup = 'التغذية';

    protected static ?string $navigationLabel = 'الجداول الغذائية';

    protected static ?string $modelLabel = 'وجبة / خطة';

    protected static ?string $pluralModelLabel = 'الجداول الغذائية';

    protected static ?int $navigationSort = 1;

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
        $user = auth()->user();

        return $user ? $record->canDelete($user) : false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('بيانات الوجبة')
                    ->schema([
                        Forms\Components\Placeholder::make('library_notice')
                            ->label('')
                            ->content(new HtmlString(
                                '<div class="rounded-lg bg-warning-50 px-3 py-2 text-sm text-warning-800 ring-1 ring-warning-600/20 dark:bg-warning-400/10 dark:text-warning-200">'
                                .'هذه الوجبة مضافة من مكتبة الوجبات ولا يمكن تعديلها أو حذفها.'
                                .'</div>'
                            ))
                            ->visible(fn (?MealPlan $record): bool => (bool) $record?->isFromLibrary())
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->maxLength(255)
                            ->disabled(fn (?MealPlan $record): bool => (bool) $record?->isFromLibrary()),
                        Forms\Components\TextInput::make('name_en')
                            ->label('الاسم EN')
                            ->maxLength(255)
                            ->disabled(fn (?MealPlan $record): bool => (bool) $record?->isFromLibrary()),
                        Forms\Components\Select::make('meal_type')
                            ->label('نوع الوجبة')
                            ->options([
                                'breakfast' => 'فطور',
                                'lunch' => 'غداء',
                                'dinner' => 'عشاء',
                                'snack' => 'سناك',
                            ])
                            ->required()
                            ->native(false)
                            ->disabled(fn (?MealPlan $record): bool => (bool) $record?->isFromLibrary()),
                        Forms\Components\Select::make('difficulty')
                            ->label('الصعوبة')
                            ->options([
                                'easy' => 'سهل',
                                'medium' => 'متوسط',
                                'hard' => 'صعب',
                            ])
                            ->default('easy')
                            ->native(false)
                            ->disabled(fn (?MealPlan $record): bool => (bool) $record?->isFromLibrary()),
                        Forms\Components\Select::make('user_id')
                            ->label('المدرب')
                            ->options(fn () => User::query()->coaches()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->native(false)
                            ->default(fn () => auth()->id())
                            ->visible(fn () => auth()->user()?->hasRole('admin') ?? false)
                            ->disabled(fn (?MealPlan $record): bool => (bool) $record?->isFromLibrary())
                            ->dehydrated(),
                        Forms\Components\Textarea::make('description')
                            ->label('الوصف')
                            ->rows(3)
                            ->columnSpanFull()
                            ->disabled(fn (?MealPlan $record): bool => (bool) $record?->isFromLibrary()),
                        Forms\Components\Textarea::make('ingredients')
                            ->label('المكونات')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull()
                            ->disabled(fn (?MealPlan $record): bool => (bool) $record?->isFromLibrary()),
                        Forms\Components\Textarea::make('instructions')
                            ->label('طريقة التحضير')
                            ->rows(4)
                            ->columnSpanFull()
                            ->disabled(fn (?MealPlan $record): bool => (bool) $record?->isFromLibrary()),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('القيم الغذائية')
                    ->schema([
                        Forms\Components\TextInput::make('calories')->label('سعرات')->numeric()->minValue(0)->default(0)
                            ->disabled(fn (?MealPlan $record): bool => (bool) $record?->isFromLibrary()),
                        Forms\Components\TextInput::make('protein')->label('بروتين')->numeric()->minValue(0)->default(0)
                            ->disabled(fn (?MealPlan $record): bool => (bool) $record?->isFromLibrary()),
                        Forms\Components\TextInput::make('carbs')->label('كارب')->numeric()->minValue(0)->default(0)
                            ->disabled(fn (?MealPlan $record): bool => (bool) $record?->isFromLibrary()),
                        Forms\Components\TextInput::make('fats')->label('دهون')->numeric()->minValue(0)->default(0)
                            ->disabled(fn (?MealPlan $record): bool => (bool) $record?->isFromLibrary()),
                        Forms\Components\TextInput::make('prep_time')->label('تحضير (د)')->numeric()->minValue(0)
                            ->disabled(fn (?MealPlan $record): bool => (bool) $record?->isFromLibrary()),
                        Forms\Components\TextInput::make('cook_time')->label('طبخ (د)')->numeric()->minValue(0)
                            ->disabled(fn (?MealPlan $record): bool => (bool) $record?->isFromLibrary()),
                        Forms\Components\TextInput::make('servings')->label('حصص')->numeric()->minValue(1)->default(1)
                            ->disabled(fn (?MealPlan $record): bool => (bool) $record?->isFromLibrary()),
                        Forms\Components\Placeholder::make('image_preview')
                            ->label('الصورة الحالية')
                            ->content(function (?MealPlan $record): HtmlString|string {
                                $url = $record?->image_url;
                                if (! $url) {
                                    return 'لا توجد صورة';
                                }

                                return new HtmlString(
                                    '<img src="'.e($url).'" alt="" style="width:96px;height:96px;object-fit:cover;border-radius:8px;display:block;" />'
                                );
                            })
                            ->visible(fn (?MealPlan $record): bool => filled($record?->image))
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('image')
                            ->label('رفع / استبدال الصورة')
                            ->image()
                            ->directory('meal-plans')
                            ->disk('public')
                            ->visibility('public')
                            ->imageEditor()
                            ->imagePreviewHeight('120')
                            ->downloadable()
                            ->openable()
                            ->columnSpanFull()
                            ->disabled(fn (?MealPlan $record): bool => (bool) $record?->isFromLibrary())
                            ->dehydrated(fn (?MealPlan $record): bool => ! (bool) $record?->isFromLibrary()),
                        Forms\Components\Toggle::make('is_active')->label('نشط')->default(true)
                            ->disabled(fn (?MealPlan $record): bool => (bool) $record?->isFromLibrary()),
                    ])
                    ->columns(4),
                static::audienceSection()
                    ->disabled(fn (?MealPlan $record): bool => (bool) $record?->isFromLibrary()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('image')
                    ->label('الصورة')
                    ->html()
                    ->formatStateUsing(function ($state, MealPlan $record): string {
                        $url = $record->image_url;
                        if (! $url) {
                            return '<span style="font-size:11px;color:#9ca3af;">—</span>';
                        }

                        return '<img src="'.e($url).'" alt="" width="40" height="40" style="width:40px;height:40px;max-width:40px;max-height:40px;object-fit:cover;border-radius:8px;display:block;" loading="lazy" />';
                    }),
                Tables\Columns\TextColumn::make('name')->label('الاسم')->searchable()->sortable()->limit(35),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('المدرب')
                    ->toggleable()
                    ->placeholder('مكتبة'),
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
                Tables\Columns\TextColumn::make('source')
                    ->label('المصدر')
                    ->badge()
                    ->color(fn (?string $state): string => $state === MealPlan::SOURCE_ARABIC_LIBRARY ? 'info' : 'success')
                    ->formatStateUsing(fn (?string $state): string => $state === MealPlan::SOURCE_ARABIC_LIBRARY ? 'مكتبة الوجبات' : 'مخصص'),
                Tables\Columns\IconColumn::make('is_active')->label('نشط')->boolean(),
            ])
            ->defaultSort('name')
            ->recordAction('viewMeal')
            ->filters([
                Tables\Filters\SelectFilter::make('source')
                    ->label('المصدر')
                    ->options([
                        MealPlan::SOURCE_CUSTOM => 'مضافة من العميل / مخصصة',
                        MealPlan::SOURCE_ARABIC_LIBRARY => 'مكتبة الوجبات',
                    ]),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('المدرب')
                    ->options(fn () => User::query()->coaches()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->visible(fn () => auth()->user()?->hasRole('admin') ?? false),
                Tables\Filters\SelectFilter::make('meal_type')
                    ->label('النوع')
                    ->options([
                        'breakfast' => 'فطور',
                        'lunch' => 'غداء',
                        'dinner' => 'عشاء',
                        'snack' => 'سناك',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')->label('نشط'),
            ])
            ->actions([
                Tables\Actions\Action::make('viewMeal')
                    ->label('عرض')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->slideOver()
                    ->modalHeading(fn (MealPlan $record): string => $record->name)
                    ->modalWidth('xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('إغلاق')
                    ->infolist(fn (MealPlan $record): array => static::viewMealInfolist($record)),
                Tables\Actions\Action::make('editMeal')
                    ->label('تعديل')
                    ->icon('heroicon-o-pencil-square')
                    ->color('gray')
                    ->url(fn (MealPlan $record): ?string => static::canEdit($record)
                        ? static::getUrl('edit', ['record' => $record])
                        : null)
                    ->disabled(fn (MealPlan $record): bool => ! static::canEdit($record))
                    ->tooltip(fn (MealPlan $record): ?string => $record->isFromLibrary()
                        ? 'وجبة من مكتبة الوجبات ولا يمكن تعديلها أو حذفها'
                        : null),
                Tables\Actions\Action::make('deleteMeal')
                    ->label('حذف')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('حذف الوجبة')
                    ->modalDescription('هل أنت متأكد من حذف هذه الوجبة؟')
                    ->disabled(fn (MealPlan $record): bool => ! static::canDelete($record))
                    ->tooltip(fn (MealPlan $record): ?string => $record->isFromLibrary()
                        ? 'وجبة من مكتبة الوجبات ولا يمكن تعديلها أو حذفها'
                        : null)
                    ->action(function (MealPlan $record): void {
                        abort_unless(static::canDelete($record), 403, 'وجبة من مكتبة الوجبات ولا يمكن حذفها.');
                        $record->delete();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد')
                        ->action(function ($records): void {
                            $records
                                ->filter(fn (MealPlan $record): bool => static::canDelete($record))
                                ->each(fn (MealPlan $record) => $record->delete());
                        }),
                ]),
            ]);
    }

    /**
     * Shared stock-image picker schema for Filament actions.
     *
     * @return array<int, Forms\Components\Component>
     */
    public static function stockImageFormSchema(?MealPlan $record = null): array
    {
        $defaultQuery = $record
            ? ((string) ($record->name_en ?: $record->name))
            : '';

        return [
            Forms\Components\TextInput::make('q')
                ->label('كلمة البحث')
                ->required()
                ->minLength(2)
                ->maxLength(120)
                ->default($defaultQuery),
            Forms\Components\Select::make('provider')
                ->label('المصدر')
                ->options(fn (): array => collect(app(\App\Services\OpenCommercialImageService::class)->availableProviders())
                    ->mapWithKeys(fn ($enabled, $key) => $enabled ? [$key => ucfirst($key)] : [])
                    ->all())
                ->default('openverse')
                ->required()
                ->native(false),
            Forms\Components\Actions::make([
                Forms\Components\Actions\Action::make('runSearch')
                    ->label('بحث عن صور')
                    ->icon('heroicon-o-magnifying-glass')
                    ->action(function (Forms\Get $get, Forms\Set $set): void {
                        $q = trim((string) $get('q'));
                        $provider = (string) ($get('provider') ?: 'openverse');
                        if (strlen($q) < 2) {
                            \Filament\Notifications\Notification::make()
                                ->title('أدخل كلمة بحث من حرفين على الأقل')
                                ->warning()
                                ->send();

                            return;
                        }

                        $results = app(\App\Services\OpenCommercialImageService::class)->search($q, $provider, 12);
                        $options = [];
                        $meta = [];
                        foreach ($results as $row) {
                            $url = (string) ($row['full_url'] ?? '');
                            if ($url === '') {
                                continue;
                            }
                            $thumb = e((string) ($row['thumb_url'] ?? $url));
                            $label = e((string) ($row['attribution'] ?? $row['photographer'] ?? 'صورة'));
                            $options[$url] = new HtmlString(
                                '<span style="display:inline-flex;align-items:center;gap:8px;">'
                                .'<img src="'.$thumb.'" alt="" width="40" height="40" style="width:40px;height:40px;object-fit:cover;border-radius:6px;" />'
                                .'<span>'.$label.'</span></span>'
                            );
                            $meta[$url] = $row;
                        }

                        $set('result_options', $options);
                        $set('result_meta', $meta);
                        $set('image_url', null);

                        if ($options === []) {
                            \Filament\Notifications\Notification::make()
                                ->title('لا توجد نتائج')
                                ->warning()
                                ->send();
                        }
                    }),
            ]),
            Forms\Components\Radio::make('image_url')
                ->label('اختر صورة')
                ->options(fn (Forms\Get $get): array => $get('result_options') ?? [])
                ->allowHtml()
                ->required()
                ->visible(fn (Forms\Get $get): bool => filled($get('result_options')))
                ->live()
                ->afterStateUpdated(function (?string $state, Forms\Get $get, Forms\Set $set): void {
                    $row = ($get('result_meta') ?? [])[$state] ?? null;
                    if (! is_array($row)) {
                        return;
                    }
                    $set('attribution', $row['attribution'] ?? null);
                    $set('attribution_url', $row['attribution_url'] ?? null);
                }),
            Forms\Components\TextInput::make('attribution')
                ->label('نسب الصورة')
                ->maxLength(255)
                ->visible(fn (Forms\Get $get): bool => filled($get('image_url'))),
            Forms\Components\TextInput::make('attribution_url')
                ->label('رابط النسب')
                ->url()
                ->maxLength(2048)
                ->visible(fn (Forms\Get $get): bool => filled($get('image_url'))),
        ];
    }

    /**
     * Download a stock image and attach it to a meal plan.
     */
    public static function applyStockImageToMeal(MealPlan $record, array $data): bool
    {
        abort_unless($record->canReplaceImage(auth()->user()), 403);

        $stored = app(\App\Services\OpenCommercialImageService::class)->downloadAndStore(
            (string) ($data['image_url'] ?? ''),
            ($record->external_id ?: 'meal-'.$record->id),
            $data['attribution'] ?? ('Open stock image'.(! empty($data['provider']) ? ' ('.$data['provider'].')' : '')),
            $data['attribution_url'] ?? null
        );

        if (! $stored) {
            return false;
        }

        if ($record->image && ! str_starts_with((string) $record->image, 'http')) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($record->image);
        }

        $record->update([
            'image' => $stored['path'],
            'image_attribution' => $stored['attribution'],
            'image_attribution_url' => $stored['attribution_url'],
        ]);

        return true;
    }

    /**
     * @return array<int, Infolists\Components\Component>
     */
    public static function viewMealInfolist(MealPlan $record): array
    {
        $mealType = match ($record->meal_type) {
            'breakfast' => 'فطور',
            'lunch' => 'غداء',
            'dinner' => 'عشاء',
            default => 'سناك',
        };

        $difficulty = match ($record->difficulty) {
            'medium' => 'متوسط',
            'hard' => 'صعب',
            default => 'سهل',
        };

        return [
            Infolists\Components\Section::make('نظرة عامة')
                ->schema([
                    Infolists\Components\ImageEntry::make('image')
                        ->label('الصورة')
                        ->hiddenLabel()
                        ->getStateUsing(fn (MealPlan $record): ?string => $record->image_url)
                        ->height(180)
                        ->extraImgAttributes(['style' => 'object-fit:cover;border-radius:12px;width:100%;max-width:280px;'])
                        ->visible(fn (MealPlan $record): bool => filled($record->image_url))
                        ->columnSpanFull(),
                    Infolists\Components\TextEntry::make('name')->label('الاسم'),
                    Infolists\Components\TextEntry::make('name_en')
                        ->label('الاسم EN')
                        ->placeholder('—'),
                    Infolists\Components\TextEntry::make('meal_type')
                        ->label('النوع')
                        ->formatStateUsing(fn (): string => $mealType)
                        ->badge(),
                    Infolists\Components\TextEntry::make('difficulty')
                        ->label('الصعوبة')
                        ->formatStateUsing(fn (): string => $difficulty),
                    Infolists\Components\TextEntry::make('source')
                        ->label('المصدر')
                        ->badge()
                        ->color(fn (): string => $record->isFromLibrary() ? 'info' : 'success')
                        ->formatStateUsing(fn (): string => $record->isFromLibrary() ? 'مكتبة الوجبات' : 'مخصص'),
                    Infolists\Components\TextEntry::make('user.name')
                        ->label('المدرب')
                        ->placeholder('مكتبة'),
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
            Infolists\Components\Section::make('القيم الغذائية')
                ->schema([
                    Infolists\Components\TextEntry::make('calories')->label('سعرات')->suffix(' سعرة'),
                    Infolists\Components\TextEntry::make('protein')->label('بروتين')->suffix(' غ'),
                    Infolists\Components\TextEntry::make('carbs')->label('كارب')->suffix(' غ'),
                    Infolists\Components\TextEntry::make('fats')->label('دهون')->suffix(' غ'),
                    Infolists\Components\TextEntry::make('prep_time')->label('تحضير')->suffix(' د')->placeholder('—'),
                    Infolists\Components\TextEntry::make('cook_time')->label('طبخ')->suffix(' د')->placeholder('—'),
                    Infolists\Components\TextEntry::make('servings')->label('الحصص')->placeholder('—'),
                    Infolists\Components\TextEntry::make('nutrition_is_estimated')
                        ->label('التغذية')
                        ->formatStateUsing(fn (): string => $record->nutrition_is_estimated ? 'تقديرية' : 'دقيقة')
                        ->badge()
                        ->color(fn (): string => $record->nutrition_is_estimated ? 'warning' : 'success'),
                ])
                ->columns(4),
            Infolists\Components\Section::make('المكونات وطريقة التحضير')
                ->schema([
                    Infolists\Components\TextEntry::make('ingredients')
                        ->label('المكونات')
                        ->columnSpanFull()
                        ->html()
                        ->formatStateUsing(fn (?string $state): string => filled($state)
                            ? nl2br(e($state))
                            : '—'),
                    Infolists\Components\TextEntry::make('instructions')
                        ->label('طريقة التحضير')
                        ->placeholder('—')
                        ->columnSpanFull()
                        ->html()
                        ->formatStateUsing(fn (?string $state): string => filled($state)
                            ? nl2br(e($state))
                            : '—'),
                ]),
        ];
    }

    public static function mutateMealPlanData(array $data): array
    {
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['source'] = $data['source'] ?? MealPlan::SOURCE_CUSTOM;
        if (array_key_exists('image', $data)) {
            $data['image'] = static::normalizeStoredImagePath($data['image']);
        }
        $data = static::mutateAudienceData($data);
        $data = static::mutateOwnerData($data);

        return $data;
    }

    /**
     * Normalize Filament upload state to a relative public-disk path.
     */
    public static function normalizeStoredImagePath(mixed $image): ?string
    {
        if (is_array($image)) {
            $image = collect($image)->filter(fn ($item) => filled($item))->first();
        }

        if (! is_string($image) || trim($image) === '') {
            return null;
        }

        $image = trim($image);

        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            $path = parse_url($image, PHP_URL_PATH) ?: '';
            $path = preg_replace('#^/storage/#', '', (string) $path) ?? '';

            return $path !== '' ? ltrim($path, '/') : null;
        }

        $image = ltrim($image, '/');
        $image = preg_replace('#^storage/#', '', $image) ?? $image;

        return $image !== '' ? $image : null;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with('user');

        if (auth()->user()?->hasRole('coach') && ! auth()->user()?->hasRole('admin')) {
            $query->where(function (Builder $inner) {
                $inner->where('user_id', auth()->id())
                    ->orWhere('source', MealPlan::SOURCE_ARABIC_LIBRARY);
            });
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMealPlans::route('/'),
            'library-review' => Pages\LibraryReview::route('/library-review'),
            'create' => Pages\CreateMealPlan::route('/create'),
            'edit' => Pages\EditMealPlan::route('/{record}/edit'),
        ];
    }
}
