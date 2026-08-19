<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use App\Services\TenantCache;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;

class ManageSiteSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $view = 'filament.pages.manage-site-settings';

    protected static ?string $navigationGroup = 'الإعدادات';

    protected static ?string $navigationLabel = 'إعدادات الموقع';

    protected static ?string $title = 'إعدادات الموقع';

    protected static ?int $navigationSort = 1;

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'site_name' => SiteSetting::get('site_name', ''),
            'site_description' => SiteSetting::get('site_description', ''),
            'site_logo' => SiteSetting::get('site_logo'),
            'site_favicon' => SiteSetting::get('site_favicon'),
            'primary_color' => SiteSetting::get('primary_color', '#f97316'),
            'secondary_color' => SiteSetting::get('secondary_color', '#0f172a'),
            'font_family' => SiteSetting::get('font_family', config('branding.default_font', 'cairo')),
            'footer_text' => SiteSetting::get('footer_text', ''),
            'contact_email' => SiteSetting::get('contact_email', ''),
            'contact_phone' => SiteSetting::get('contact_phone', ''),
            'contact_whatsapp' => SiteSetting::get('contact_whatsapp', ''),
            'contact_telegram' => SiteSetting::get('contact_telegram', ''),
            'contact_address' => SiteSetting::get('contact_address', ''),
            'contact_map_link' => SiteSetting::get('contact_map_link', ''),
            'social_facebook' => SiteSetting::get('social_facebook', ''),
            'social_twitter' => SiteSetting::get('social_twitter', ''),
            'social_instagram' => SiteSetting::get('social_instagram', ''),
            'social_linkedin' => SiteSetting::get('social_linkedin', ''),
            'social_youtube' => SiteSetting::get('social_youtube', ''),
        ]);
    }

    public function form(Form $form): Form
    {
        $fontOptions = collect(config('branding.fonts', []))
            ->mapWithKeys(fn (array $font, string $key) => [$key => $font['label_ar'] ?? $font['label'] ?? $key])
            ->all();

        return $form
            ->schema([
                Forms\Components\Tabs::make('settings')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('عام')
                            ->schema([
                                Forms\Components\TextInput::make('site_name')->label('اسم الموقع')->required()->maxLength(255),
                                Forms\Components\Textarea::make('site_description')->label('الوصف')->rows(3)->maxLength(500),
                                Forms\Components\FileUpload::make('site_logo')
                                    ->label('الشعار')
                                    ->image()
                                    ->directory('logos')
                                    ->disk('public')
                                    ->imageEditor(),
                                Forms\Components\FileUpload::make('site_favicon')
                                    ->label('الأيقونة')
                                    ->image()
                                    ->directory('favicons')
                                    ->disk('public'),
                                Forms\Components\ColorPicker::make('primary_color')->label('اللون الأساسي'),
                                Forms\Components\ColorPicker::make('secondary_color')->label('اللون الثانوي'),
                                Forms\Components\Select::make('font_family')
                                    ->label('الخط')
                                    ->options($fontOptions)
                                    ->native(false),
                                Forms\Components\Textarea::make('footer_text')->label('نص التذييل')->rows(2)->maxLength(500),
                            ])
                            ->columns(2),
                        Forms\Components\Tabs\Tab::make('التواصل')
                            ->schema([
                                Forms\Components\TextInput::make('contact_email')->label('البريد')->email(),
                                Forms\Components\TextInput::make('contact_phone')->label('الهاتف')->maxLength(20),
                                Forms\Components\TextInput::make('contact_whatsapp')->label('واتساب')->maxLength(20),
                                Forms\Components\TextInput::make('contact_telegram')->label('تيليجرام')->maxLength(255),
                                Forms\Components\Textarea::make('contact_address')->label('العنوان')->rows(2)->columnSpanFull(),
                                Forms\Components\TextInput::make('contact_map_link')->label('رابط الخريطة')->url()->columnSpanFull(),
                            ])
                            ->columns(2),
                        Forms\Components\Tabs\Tab::make('التواصل الاجتماعي')
                            ->schema([
                                Forms\Components\TextInput::make('social_facebook')->label('فيسبوك')->url(),
                                Forms\Components\TextInput::make('social_twitter')->label('تويتر / X')->url(),
                                Forms\Components\TextInput::make('social_instagram')->label('إنستغرام')->url(),
                                Forms\Components\TextInput::make('social_linkedin')->label('لينكدإن')->url(),
                                Forms\Components\TextInput::make('social_youtube')->label('يوتيوب')->url(),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        SiteSetting::set('site_name', $data['site_name'] ?? '', 'general', 'string', 'Site name');
        SiteSetting::set('site_description', $data['site_description'] ?? '', 'general', 'string', 'Site description');
        SiteSetting::set('site_logo', $data['site_logo'] ?? null, 'general', 'string', 'Site logo path');
        SiteSetting::set('site_favicon', $data['site_favicon'] ?? null, 'general', 'string', 'Site favicon path');
        SiteSetting::set('primary_color', $data['primary_color'] ?? null, 'general', 'string', 'Primary color');
        SiteSetting::set('secondary_color', $data['secondary_color'] ?? null, 'general', 'string', 'Secondary color');
        SiteSetting::set('font_family', $data['font_family'] ?? config('branding.default_font', 'cairo'), 'general', 'string', 'Brand Arabic font family');
        SiteSetting::set('footer_text', $data['footer_text'] ?? '', 'general', 'string', 'Footer text');

        SiteSetting::set('contact_email', $data['contact_email'] ?? '', 'contact', 'string', 'Contact email');
        SiteSetting::set('contact_phone', $data['contact_phone'] ?? '', 'contact', 'string', 'Contact phone');
        SiteSetting::set('contact_whatsapp', $data['contact_whatsapp'] ?? '', 'contact', 'string', 'WhatsApp number');
        SiteSetting::set('contact_telegram', $data['contact_telegram'] ?? '', 'contact', 'string', 'Telegram username');
        SiteSetting::set('contact_address', $data['contact_address'] ?? '', 'contact', 'string', 'Physical address');
        SiteSetting::set('contact_map_link', $data['contact_map_link'] ?? '', 'contact', 'string', 'Google Maps link');

        SiteSetting::set('social_facebook', $data['social_facebook'] ?? '', 'social', 'string', 'Facebook');
        SiteSetting::set('social_twitter', $data['social_twitter'] ?? '', 'social', 'string', 'Twitter');
        SiteSetting::set('social_instagram', $data['social_instagram'] ?? '', 'social', 'string', 'Instagram');
        SiteSetting::set('social_linkedin', $data['social_linkedin'] ?? '', 'social', 'string', 'LinkedIn');
        SiteSetting::set('social_youtube', $data['social_youtube'] ?? '', 'social', 'string', 'YouTube');

        SiteSetting::clearGroupCache('general');
        SiteSetting::clearGroupCache('contact');
        SiteSetting::clearGroupCache('social');
        Cache::forget(TenantCache::key('site_settings_general'));
        Cache::forget(TenantCache::key('setting_font_family'));
        Cache::forget(TenantCache::key('setting_primary_color'));
        Cache::forget(TenantCache::key('setting_secondary_color'));

        Notification::make()
            ->title('تم حفظ إعدادات الموقع')
            ->success()
            ->send();
    }
}
