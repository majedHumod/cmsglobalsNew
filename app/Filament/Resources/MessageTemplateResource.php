<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MessageTemplateResource\Pages;
use App\Models\MessageTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MessageTemplateResource extends Resource
{
    protected static ?string $model = MessageTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'التواصل والمتابعة';

    protected static ?string $navigationLabel = 'قوالب الرسائل';

    protected static ?string $modelLabel = 'قالب';

    protected static ?string $pluralModelLabel = 'قوالب الرسائل';

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'coach']) ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }
        if ($user->hasRole('admin')) {
            return true;
        }

        return (int) $record->created_by_user_id === (int) $user->id;
    }

    public static function canDelete(Model $record): bool
    {
        return static::canEdit($record);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('القالب')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->maxLength(120),
                        Forms\Components\TextInput::make('description')
                            ->label('وصف مختصر')
                            ->maxLength(255),
                        Forms\Components\Select::make('category')
                            ->label('التصنيف')
                            ->options([
                                'general' => 'عام',
                                'follow_up' => 'متابعة',
                                'reminder' => 'تذكير',
                                'global' => 'عام للجميع',
                            ])
                            ->default('general')
                            ->native(false)
                            ->disabled(fn () => ! (auth()->user()?->hasRole('admin') ?? false))
                            ->dehydrated(),
                        Forms\Components\Textarea::make('body')
                            ->label('النص')
                            ->required()
                            ->rows(6)
                            ->helperText('المتغيرات المتاحة: {{client_name}} {{coach_name}} {{org_name}} {{date}} {{membership_expires}}')
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
                Tables\Columns\TextColumn::make('name')->label('الاسم')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('category')->label('التصنيف')->badge(),
                Tables\Columns\TextColumn::make('creator.name')->label('أنشأه')->toggleable(),
                Tables\Columns\IconColumn::make('is_active')->label('نشط')->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->label('آخر تحديث')->dateTime('d/m/Y')->toggleable(),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('نشط'),
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
        $query = parent::getEloquentQuery()->with('creator');
        $user = auth()->user();

        if ($user?->hasRole('coach') && ! $user->hasRole('admin')) {
            $query->where(function (Builder $inner) use ($user) {
                $inner->where('created_by_user_id', $user->id)
                    ->orWhere('category', 'global');
            });
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMessageTemplates::route('/'),
            'create' => Pages\CreateMessageTemplate::route('/create'),
            'edit' => Pages\EditMessageTemplate::route('/{record}/edit'),
        ];
    }
}
