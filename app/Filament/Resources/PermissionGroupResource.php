<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\AdminOnlyResource;
use App\Filament\Resources\PermissionGroupResource\Pages;
use App\Filament\Resources\PermissionGroupResource\RelationManagers\CategoriesRelationManager;
use App\Models\PermissionGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PermissionGroupResource extends Resource
{
    use AdminOnlyResource;

    protected static ?string $model = PermissionGroup::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?string $navigationGroup = 'الإعدادات';

    protected static ?string $navigationLabel = 'مجموعات الصلاحيات';

    protected static ?string $modelLabel = 'مجموعة صلاحيات';

    protected static ?string $pluralModelLabel = 'مجموعات الصلاحيات';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->label('الاسم')->required()->maxLength(255),
                Forms\Components\TextInput::make('slug')->label('Slug')->maxLength(255)->helperText('يُنشأ تلقائياً إن تُرك فارغاً'),
                Forms\Components\TextInput::make('icon')->label('الأيقونة')->maxLength(50)->placeholder('shield-check'),
                Forms\Components\ColorPicker::make('color')->label('اللون'),
                Forms\Components\TextInput::make('sort_order')->label('الترتيب')->numeric()->default(0),
                Forms\Components\Toggle::make('is_active')->label('نشط')->default(true),
                Forms\Components\Textarea::make('description')->label('الوصف')->rows(2)->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('الاسم')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->label('Slug')->toggleable(),
                Tables\Columns\TextColumn::make('categories_count')->label('التصنيفات')->counts('categories'),
                Tables\Columns\TextColumn::make('sort_order')->label('الترتيب')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('نشط')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CategoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermissionGroups::route('/'),
            'create' => Pages\CreatePermissionGroup::route('/create'),
            'edit' => Pages\EditPermissionGroup::route('/{record}/edit'),
        ];
    }
}
