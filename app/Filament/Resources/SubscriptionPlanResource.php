<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\AdminOnlyResource;

use App\Filament\Resources\SubscriptionPlanResource\Pages;
use App\Models\MembershipType;
use App\Models\SubscriptionPlan;
use App\Services\TenantCache;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SubscriptionPlanResource extends Resource
{
    use AdminOnlyResource;
    protected static ?string $model = SubscriptionPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'العضويات والاشتراكات';

    protected static ?string $navigationLabel = 'خطط الاشتراك';

    protected static ?string $modelLabel = 'خطة اشتراك';

    protected static ?string $pluralModelLabel = 'خطط الاشتراك';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('العرض التجاري')
                    ->description('خطة الاشتراك هي السعر والمدة والمزايا. مسار العضوية يحدد صلاحية الوصول فقط.')
                    ->schema([
                        Forms\Components\Select::make('membership_type_id')
                            ->label('مسار العضوية')
                            ->options(fn () => MembershipType::query()->active()->ordered()->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->native(false)
                            ->default(fn () => request()->integer('membership_type_id') ?: null),
                        Forms\Components\TextInput::make('name')
                            ->label('اسم الخطة')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label('الوصف')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('التسعير والمدة')
                    ->schema([
                        Forms\Components\TextInput::make('duration_days')
                            ->label('المدة بالأيام')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100000)
                            ->default(30),
                        Forms\Components\TextInput::make('compare_at_price')
                            ->label('السعر قبل الخصم')
                            ->numeric()
                            ->minValue(0)
                            ->helperText('اختياري. يظهر مشطوباً إذا كان أعلى من سعر البيع.'),
                        Forms\Components\TextInput::make('price')
                            ->label('سعر البيع')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        Forms\Components\Select::make('gender_scope')
                            ->label('نطاق الجنس')
                            ->options([
                                'all' => 'الجميع',
                                'male' => 'رجال',
                                'female' => 'نساء',
                            ])
                            ->required()
                            ->default('all')
                            ->native(false),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('الترتيب')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        Forms\Components\Toggle::make('is_active')
                            ->label('الخطة مفعلة')
                            ->default(true),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('المزايا')
                    ->schema([
                        Forms\Components\Repeater::make('features')
                            ->label('قائمة المزايا')
                            ->simple(
                                Forms\Components\TextInput::make('feature')
                                    ->label('ميزة')
                                    ->maxLength(255)
                                    ->required(),
                            )
                            ->default([])
                            ->addActionLabel('إضافة ميزة')
                            ->reorderable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الخطة')
                    ->searchable()
                    ->sortable()
                    ->description(fn (SubscriptionPlan $record): ?string => $record->description ? str($record->description)->limit(50)->toString() : null),
                Tables\Columns\TextColumn::make('membershipType.name')
                    ->label('المسار')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('duration_days')
                    ->label('المدة')
                    ->formatStateUsing(fn (SubscriptionPlan $record): string => $record->duration_text),
                Tables\Columns\TextColumn::make('price')
                    ->label('السعر')
                    ->formatStateUsing(function (SubscriptionPlan $record): string {
                        $text = $record->formatted_price;
                        if ($record->hasDiscount()) {
                            $text .= ' (خصم '.$record->discountPercent().'%)';
                        }

                        return $text;
                    }),
                Tables\Columns\TextColumn::make('gender_scope')
                    ->label('الجنس')
                    ->badge()
                    ->formatStateUsing(fn (SubscriptionPlan $record): string => $record->gender_scope_label),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('مفعلة')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('membership_type_id')
                    ->label('المسار')
                    ->relationship('membershipType', 'name'),
                Tables\Filters\TernaryFilter::make('is_active')->label('مفعلة'),
                Tables\Filters\SelectFilter::make('gender_scope')
                    ->label('الجنس')
                    ->options([
                        'all' => 'الجميع',
                        'male' => 'رجال',
                        'female' => 'نساء',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف')
                    ->before(function (SubscriptionPlan $record) {
                        if ($record->memberships()->exists()) {
                            Notification::make()
                                ->title('لا يمكن حذف الخطة لوجود اشتراكات مرتبطة بها.')
                                ->danger()
                                ->send();

                            throw ValidationException::withMessages([
                                'memberships' => 'لا يمكن حذف الخطة لوجود اشتراكات مرتبطة بها.',
                            ]);
                        }
                    })
                    ->after(fn () => static::forgetHomepagePlansCache()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد')
                        ->after(fn () => static::forgetHomepagePlansCache()),
                ]),
            ]);
    }

    public static function mutatePlanData(array $data, ?SubscriptionPlan $record = null): array
    {
        $compare = $data['compare_at_price'] ?? null;
        if ($compare === '' || $compare === null) {
            $data['compare_at_price'] = null;
        } elseif ((float) $compare > 0 && (float) $compare <= (float) ($data['price'] ?? 0)) {
            throw ValidationException::withMessages([
                'compare_at_price' => 'السعر قبل الخصم يجب أن يكون أعلى من سعر البيع ليظهر للعميل كقيمة موفّرة.',
            ]);
        }

        $features = $data['features'] ?? [];
        if (is_array($features)) {
            $data['features'] = array_values(array_filter(array_map(
                static fn ($feature) => trim(is_array($feature) ? (string) ($feature['feature'] ?? '') : (string) $feature),
                $features
            )));
        } else {
            $data['features'] = [];
        }

        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        $name = (string) ($data['name'] ?? '');
        if (! $record || $record->name !== $name) {
            $data['slug'] = static::uniqueSlug($name, $record?->id);
        }

        return $data;
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'plan-'.Str::lower(Str::random(8));
        }

        $slug = $base;
        $counter = 1;

        while (
            SubscriptionPlan::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    public static function forgetHomepagePlansCache(): void
    {
        Cache::forget(TenantCache::key('homepage_subscription_plans'));
    }

    /**
     * Convert stored features array into repeater simple format for editing.
     *
     * @param  array<int, string>|null  $features
     * @return array<int, string>
     */
    public static function featuresForForm(?array $features): array
    {
        return array_values($features ?? []);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptionPlans::route('/'),
            'create' => Pages\CreateSubscriptionPlan::route('/create'),
            'edit' => Pages\EditSubscriptionPlan::route('/{record}/edit'),
        ];
    }
}
