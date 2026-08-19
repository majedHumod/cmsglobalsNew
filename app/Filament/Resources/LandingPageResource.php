<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\AdminOnlyResource;

use App\Filament\Resources\LandingPageResource\Pages;
use App\Models\LandingPage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LandingPageResource extends Resource
{
    use AdminOnlyResource;
    protected static ?string $model = LandingPage::class;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    protected static ?string $navigationGroup = 'إدارة المحتوى';

    protected static ?string $navigationLabel = 'الصفحة الرئيسية';

    protected static ?string $modelLabel = 'صفحة رئيسية';

    protected static ?string $pluralModelLabel = 'صفحات الهبوط';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('المحتوى الأساسي')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('العنوان')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('subtitle')
                            ->label('العنوان الفرعي')
                            ->maxLength(255),
                        Forms\Components\FileUpload::make('header_image')
                            ->label('صورة الهيدر')
                            ->image()
                            ->directory('landing-pages')
                            ->disk('public')
                            ->imageEditor()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->columnSpanFull(),
                        Forms\Components\ColorPicker::make('header_text_color')
                            ->label('لون نص الهيدر')
                            ->required()
                            ->default('#ffffff'),
                        Forms\Components\RichEditor::make('content')
                            ->label('المحتوى')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('زر الانضمام')
                    ->schema([
                        Forms\Components\Toggle::make('show_join_button')
                            ->label('إظهار زر الانضمام')
                            ->default(true)
                            ->live(),
                        Forms\Components\TextInput::make('join_button_text')
                            ->label('نص الزر')
                            ->maxLength(50)
                            ->visible(fn (Forms\Get $get): bool => (bool) $get('show_join_button')),
                        Forms\Components\TextInput::make('join_button_url')
                            ->label('رابط الزر')
                            ->maxLength(255)
                            ->visible(fn (Forms\Get $get): bool => (bool) $get('show_join_button')),
                        Forms\Components\ColorPicker::make('join_button_color')
                            ->label('لون الزر')
                            ->visible(fn (Forms\Get $get): bool => (bool) $get('show_join_button')),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('SEO والحالة')
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->label('عنوان الميتا')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('meta_description')
                            ->label('وصف الميتا')
                            ->rows(2)
                            ->maxLength(255),
                        Forms\Components\Toggle::make('is_active')
                            ->label('تفعيل كصفحة رئيسية نشطة')
                            ->helperText('تفعيل هذه الصفحة سيلغي تفعيل الصفحات الأخرى تلقائياً.')
                            ->default(false),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('header_image')
                    ->label('الصورة')
                    ->disk('public')
                    ->square(),
                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->sortable()
                    ->description(fn (LandingPage $record): ?string => $record->subtitle),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشطة')
                    ->boolean(),
                Tables\Columns\IconColumn::make('show_join_button')
                    ->label('زر الانضمام')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('نشطة'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\Action::make('activate')
                    ->label('تفعيل')
                    ->visible(fn (LandingPage $record): bool => ! $record->is_active)
                    ->requiresConfirmation()
                    ->action(function (LandingPage $record) {
                        LandingPage::query()->where('id', '!=', $record->id)->update(['is_active' => false]);
                        $record->update(['is_active' => true]);
                        LandingPage::clearCache();

                        Notification::make()
                            ->title('تم تفعيل الصفحة الرئيسية')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف')
                    ->after(fn () => LandingPage::clearCache()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد')
                        ->after(fn () => LandingPage::clearCache()),
                ]),
            ]);
    }

    public static function syncActiveState(LandingPage $record, array $data): array
    {
        $data['show_join_button'] = (bool) ($data['show_join_button'] ?? false);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        if ($data['is_active']) {
            LandingPage::query()
                ->when($record->exists, fn ($q) => $q->where('id', '!=', $record->id))
                ->update(['is_active' => false]);
        }

        return $data;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLandingPages::route('/'),
            'create' => Pages\CreateLandingPage::route('/create'),
            'edit' => Pages\EditLandingPage::route('/{record}/edit'),
        ];
    }
}
