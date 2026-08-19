<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\AdminOnlyResource;
use App\Filament\Resources\PageResource\Pages;
use App\Models\MembershipType;
use App\Models\Page;
use App\Support\PageAudienceInput;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Js;
use Illuminate\Support\Str;

class PageResource extends Resource
{
    use AdminOnlyResource;
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'إدارة المحتوى';

    protected static ?string $navigationLabel = 'الصفحات';

    protected static ?string $modelLabel = 'صفحة';

    protected static ?string $pluralModelLabel = 'الصفحات';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('المحتوى')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('العنوان')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, ?string $state, ?Page $record) {
                                if ($record || filled($get('slug'))) {
                                    return;
                                }

                                $suggested = Str::slug((string) $state);
                                if ($suggested !== '') {
                                    $set('slug', $suggested);
                                }
                            }),
                        Forms\Components\TextInput::make('slug')
                            ->label('الرابط')
                            ->maxLength(255)
                            ->nullable()
                            ->unique(ignoreRecord: true)
                            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? trim($state) : null)
                            ->placeholder('اتركه فارغاً ليُولَّد تلقائياً')
                            ->helperText('اختياري: يُولَّد تلقائياً من العنوان بعد حفظ الصفحة، أو يمكنك إدخاله يدوياً.'),
                        Forms\Components\RichEditor::make('content')
                            ->label('المحتوى')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('excerpt')
                            ->label('المقتطف')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('featured_image')
                            ->label('الصورة البارزة')
                            ->image()
                            ->directory('pages')
                            ->disk('public')
                            ->imageEditor()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('SEO')
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->label('عنوان الميتا')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('meta_description')
                            ->label('وصف الميتا')
                            ->maxLength(160)
                            ->rows(2),
                    ])
                    ->columns(2)
                    ->collapsed(),

                Forms\Components\Section::make('الوصول والنشر')
                    ->schema([
                        Forms\Components\Select::make('access_level')
                            ->label('مستوى الوصول')
                            ->options([
                                'public' => 'عام للجميع',
                                'authenticated' => 'المستخدمين المسجلين',
                                'admin' => 'المدراء فقط',
                                'user' => 'المتدربين',
                                'page_manager' => 'مديري الصفحات',
                                'membership' => 'أعضاء العضويات',
                            ])
                            ->required()
                            ->native(false)
                            ->live(),
                        Forms\Components\Select::make('required_membership_types')
                            ->label('أنواع العضوية المطلوبة')
                            ->multiple()
                            ->options(fn () => MembershipType::query()->active()->orderBy('sort_order')->pluck('name', 'id'))
                            ->visible(fn (Forms\Get $get): bool => $get('access_level') === 'membership')
                            ->required(fn (Forms\Get $get): bool => $get('access_level') === 'membership')
                            ->native(false),
                        Forms\Components\Select::make('audience_gender')
                            ->label('نطاق الجنس')
                            ->options([
                                'all' => 'الجميع',
                                'male' => 'رجال',
                                'female' => 'نساء',
                            ])
                            ->default('all')
                            ->native(false),
                        Forms\Components\TextInput::make('menu_order')
                            ->label('ترتيب القائمة')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        Forms\Components\Toggle::make('is_published')
                            ->label('منشورة')
                            ->default(false),
                        Forms\Components\Toggle::make('show_in_menu')
                            ->label('إظهار في القائمة')
                            ->default(false),
                        Forms\Components\Toggle::make('is_premium')
                            ->label('محتوى مدفوع')
                            ->default(false),
                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('تاريخ النشر'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Page $record): string => (string) $record->slug),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('منشورة')
                    ->boolean(),
                Tables\Columns\IconColumn::make('show_in_menu')
                    ->label('في القائمة')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('access_level')
                    ->label('الوصول')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'public' => 'عام',
                        'authenticated' => 'مسجلين',
                        'admin' => 'مدراء',
                        'user' => 'متدربين',
                        'page_manager' => 'مديري صفحات',
                        'membership' => 'عضويات',
                        default => (string) $state,
                    }),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('المؤلف')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('تاريخ النشر')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')->label('منشورة'),
                Tables\Filters\TernaryFilter::make('show_in_menu')->label('في القائمة'),
                Tables\Filters\SelectFilter::make('access_level')
                    ->label('الوصول')
                    ->options([
                        'public' => 'عام',
                        'authenticated' => 'مسجلين',
                        'membership' => 'عضويات',
                        'admin' => 'مدراء',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('عرض')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Page $record): string => route('pages.show', $record->slug))
                    ->openUrlInNewTab()
                    ->visible(fn (Page $record): bool => filled($record->slug)),
                Tables\Actions\Action::make('copyLink')
                    ->label('نسخ الرابط')
                    ->icon('heroicon-o-clipboard-document')
                    ->color('gray')
                    ->visible(fn (Page $record): bool => filled($record->slug))
                    ->alpineClickHandler(fn (Page $record): string => static::clipboardAlpineHandler(
                        route('pages.show', $record->slug)
                    )),
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ]);
    }

    public static function clipboardAlpineHandler(string $url): string
    {
        $encodedUrl = Js::from($url);

        return <<<JS
            (async () => {
                const text = {$encodedUrl};
                let copied = false;

                try {
                    if (window.isSecureContext && navigator.clipboard && navigator.clipboard.writeText) {
                        await navigator.clipboard.writeText(text);
                        copied = true;
                    }
                } catch (e) {
                    copied = false;
                }

                if (! copied) {
                    const el = document.createElement('textarea');
                    el.value = text;
                    el.setAttribute('readonly', '');
                    el.style.position = 'fixed';
                    el.style.top = '-9999px';
                    document.body.appendChild(el);
                    el.focus();
                    el.select();
                    try {
                        copied = document.execCommand('copy');
                    } catch (e) {
                        copied = false;
                    }
                    document.body.removeChild(el);
                }

                if (copied) {
                    new FilamentNotification()
                        .title('تم نسخ رابط الصفحة')
                        .body(text)
                        .success()
                        .send();
                } else {
                    new FilamentNotification()
                        .title('تعذر نسخ الرابط')
                        .body(text)
                        .danger()
                        .send();
                }
            })()
        JS;
    }

    public static function mutateAudienceData(array $data, ?Page $record = null): array
    {
        $accessLevel = (string) ($data['access_level'] ?? 'public');
        $data['required_membership_types'] = PageAudienceInput::membershipTypeIdsForAccessLevel(
            $accessLevel,
            $data['required_membership_types'] ?? []
        );
        $data['audience_gender'] = $data['audience_gender'] ?? 'all';
        $data['is_published'] = (bool) ($data['is_published'] ?? false);
        $data['show_in_menu'] = (bool) ($data['show_in_menu'] ?? false);
        $data['is_premium'] = (bool) ($data['is_premium'] ?? false);
        $data['menu_order'] = (int) ($data['menu_order'] ?? 0);

        if ($data['is_published'] && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        if (! $data['is_published']) {
            $data['published_at'] = $data['published_at'] ?? null;
        }

        $data = static::ensureUniqueSlug($data, $record);

        return $data;
    }

    public static function ensureUniqueSlug(array $data, ?Page $record = null): array
    {
        $base = trim((string) ($data['slug'] ?? ''));
        if ($base === '') {
            $base = Str::slug((string) ($data['title'] ?? 'page'));
        }

        if ($base === '') {
            $base = 'page';
        }

        $slug = $base;
        $counter = 1;

        while (
            Page::query()
                ->where('slug', $slug)
                ->when($record, fn ($query) => $query->where('id', '!=', $record->id))
                ->exists()
        ) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        $data['slug'] = $slug;

        return $data;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
