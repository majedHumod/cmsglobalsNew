<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\AdminOnlyResource;

use App\Filament\Resources\MembershipTypeResource\Pages;
use App\Filament\Resources\MembershipTypeResource\RelationManagers;
use App\Models\MembershipType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class MembershipTypeResource extends Resource
{
    use AdminOnlyResource;
    protected static ?string $model = MembershipType::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'العضويات والاشتراكات';

    protected static ?string $navigationLabel = 'أنواع العضويات';

    protected static ?string $modelLabel = 'مسار عضوية';

    protected static ?string $pluralModelLabel = 'أنواع العضويات';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('بيانات مسار العضوية')
                    ->description('المسار يحدد صلاحية الوصول للمحتوى. السعر والمدة يُداران من خطط الاشتراك.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم المسار')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('ترتيب العرض')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        Forms\Components\Textarea::make('description')
                            ->label('الوصف')
                            ->rows(3)
                            ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('name')
                    ->label('المسار')
                    ->searchable()
                    ->sortable()
                    ->description(fn (MembershipType $record): ?string => $record->description ? str($record->description)->limit(60)->toString() : null),
                Tables\Columns\TextColumn::make('subscription_plans_count')
                    ->label('خطط الاشتراك')
                    ->counts('subscriptionPlans')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('الترتيب')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_protected')
                    ->label('محمي')
                    ->boolean()
                    ->toggleable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('نشط'),
                Tables\Filters\TernaryFilter::make('is_protected')->label('محمي'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('تعديل')
                    ->visible(fn (MembershipType $record): bool => $record->canBeModified()),
                Tables\Actions\Action::make('toggle')
                    ->label(fn (MembershipType $record): string => $record->is_active ? 'إيقاف' : 'تفعيل')
                    ->visible(fn (MembershipType $record): bool => $record->canBeModified())
                    ->action(function (MembershipType $record) {
                        $record->update(['is_active' => ! $record->is_active]);
                        Notification::make()
                            ->title($record->is_active ? 'تم تفعيل المسار' : 'تم إيقاف المسار')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('addPlan')
                    ->label('إضافة خطة')
                    ->url(fn (MembershipType $record): string => SubscriptionPlanResource::getUrl('create', [
                        'membership_type_id' => $record->id,
                    ])),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف')
                    ->visible(fn (MembershipType $record): bool => $record->canBeDeleted()),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SubscriptionPlansRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('subscriptionPlans');
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        if ($slug === '') {
            $slug = 'membership-'.Str::lower(Str::random(8));
        }

        $base = $slug;
        $counter = 1;

        while (
            MembershipType::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMembershipTypes::route('/'),
            'create' => Pages\CreateMembershipType::route('/create'),
            'edit' => Pages\EditMembershipType::route('/{record}/edit'),
        ];
    }
}
