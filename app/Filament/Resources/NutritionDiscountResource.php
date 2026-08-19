<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\AdminOnlyResource;
use App\Filament\Resources\NutritionDiscountResource\Pages;
use App\Models\NutritionDiscount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NutritionDiscountResource extends Resource
{
    use AdminOnlyResource;

    protected static ?string $model = NutritionDiscount::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'التغذية';

    protected static ?string $navigationLabel = 'خصومات المراكز';

    protected static ?string $modelLabel = 'خصم';

    protected static ?string $pluralModelLabel = 'خصومات المراكز';

    protected static ?int $navigationSort = 3;

    public static function defaultImageUrl(): string
    {
        return NutritionDiscount::defaultImageUrl();
    }

    public static function thumbnailHtml(NutritionDiscount $record): string
    {
        // Prefer HTTP URL; fall back to inline data URI so the cell is never blank.
        $url = $record->hasCustomImage()
            ? $record->image_url
            : NutritionDiscount::defaultImageDataUri();

        return '<img src="'.e($url).'" alt="" width="40" height="40" style="width:40px;height:40px;max-width:40px;max-height:40px;object-fit:cover;border-radius:8px;display:block;background:#0f766e;" loading="lazy" />';
    }

    public static function mutateDiscountData(array $data): array
    {
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        if (empty($data['image'])) {
            NutritionDiscount::ensureDefaultImageExists();
            $data['image'] = NutritionDiscount::DEFAULT_IMAGE;
        }

        return $data;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('بيانات الخصم')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('discount_percentage')
                            ->label('نسبة الخصم %')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100),
                        Forms\Components\DatePicker::make('start_date')
                            ->label('من')
                            ->required()
                            ->native(false),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('إلى')
                            ->required()
                            ->native(false)
                            ->after('start_date'),
                        Forms\Components\FileUpload::make('image')
                            ->label('الصورة')
                            ->image()
                            ->directory('nutrition-discounts')
                            ->disk('public')
                            ->imageEditor()
                            ->helperText('إذا لم تُرفع صورة ستُستخدم صورة افتراضية تلقائياً.')
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('thumbnail')
                    ->label('الصورة')
                    ->html()
                    ->getStateUsing(fn (NutritionDiscount $record): string => static::thumbnailHtml($record)),
                Tables\Columns\TextColumn::make('name')->label('الاسم')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('discount_percentage')
                    ->label('الخصم')
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')->label('من')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('end_date')->label('إلى')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('validity_status')
                    ->label('الحالة الزمنية')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'current' => 'success',
                        'upcoming' => 'info',
                        'expired' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (NutritionDiscount $record): string => $record->validity_status_label),
                Tables\Columns\IconColumn::make('is_active')->label('نشط')->boolean(),
            ])
            ->defaultSort('start_date', 'desc')
            ->recordAction('viewDiscount')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('نشط'),
                Tables\Filters\SelectFilter::make('validity')
                    ->label('الحالة الزمنية')
                    ->options([
                        'current' => 'ساري الآن',
                        'upcoming' => 'قادم',
                        'expired' => 'منتهي',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'current' => $query->valid(),
                            'upcoming' => $query->upcoming(),
                            'expired' => $query->expired(),
                            default => $query,
                        };
                    }),
                Tables\Filters\SelectFilter::make('discount_range')
                    ->label('نسبة الخصم')
                    ->options([
                        '1-15' => '1% — 15%',
                        '16-30' => '16% — 30%',
                        '31-50' => '31% — 50%',
                        '51-100' => 'أكثر من 50%',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            '1-15' => $query->whereBetween('discount_percentage', [1, 15]),
                            '16-30' => $query->whereBetween('discount_percentage', [16, 30]),
                            '31-50' => $query->whereBetween('discount_percentage', [31, 50]),
                            '51-100' => $query->whereBetween('discount_percentage', [51, 100]),
                            default => $query,
                        };
                    }),
                Tables\Filters\Filter::make('date_range')
                    ->label('الفترة')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('من تاريخ')->native(false),
                        Forms\Components\DatePicker::make('until')->label('إلى تاريخ')->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $q, $date) => $q->whereDate('end_date', '>=', $date)
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $q, $date) => $q->whereDate('start_date', '<=', $date)
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('viewDiscount')
                    ->label('عرض')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->slideOver()
                    ->modalHeading(fn (NutritionDiscount $record): string => $record->name)
                    ->modalWidth('lg')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('إغلاق')
                    ->infolist(fn (NutritionDiscount $record): array => static::viewDiscountInfolist($record)),
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
    public static function viewDiscountInfolist(NutritionDiscount $record): array
    {
        $imageSrc = $record->hasCustomImage()
            ? $record->image_url
            : NutritionDiscount::defaultImageDataUri();

        return [
            Infolists\Components\Section::make('تفاصيل الخصم')
                ->schema([
                    Infolists\Components\ImageEntry::make('image')
                        ->label('الصورة')
                        ->hiddenLabel()
                        ->getStateUsing(fn (): string => $imageSrc)
                        ->height(180)
                        ->extraImgAttributes([
                            'style' => 'object-fit:cover;border-radius:12px;width:100%;max-width:280px;background:#0f766e;',
                        ])
                        ->columnSpanFull(),
                    Infolists\Components\TextEntry::make('name')->label('الاسم'),
                    Infolists\Components\TextEntry::make('discount_percentage')
                        ->label('نسبة الخصم')
                        ->suffix('%'),
                    Infolists\Components\TextEntry::make('start_date')
                        ->label('من')
                        ->date('d/m/Y'),
                    Infolists\Components\TextEntry::make('end_date')
                        ->label('إلى')
                        ->date('d/m/Y'),
                    Infolists\Components\TextEntry::make('validity_status')
                        ->label('الحالة الزمنية')
                        ->badge()
                        ->color(fn (): string => match ($record->validity_status) {
                            'current' => 'success',
                            'upcoming' => 'info',
                            'expired' => 'danger',
                            default => 'gray',
                        })
                        ->formatStateUsing(fn (): string => $record->validity_status_label),
                    Infolists\Components\IconEntry::make('is_active')->label('نشط')->boolean(),
                    Infolists\Components\TextEntry::make('has_custom_image')
                        ->label('مصدر الصورة')
                        ->formatStateUsing(fn (): string => $record->has_custom_image ? 'صورة مرفوعة' : 'صورة افتراضية')
                        ->badge()
                        ->color(fn (): string => $record->has_custom_image ? 'success' : 'gray'),
                ])
                ->columns(2),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNutritionDiscounts::route('/'),
            'create' => Pages\CreateNutritionDiscount::route('/create'),
            'edit' => Pages\EditNutritionDiscount::route('/{record}/edit'),
        ];
    }
}
