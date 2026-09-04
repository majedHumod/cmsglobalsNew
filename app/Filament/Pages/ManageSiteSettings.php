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
            'app_android' => SiteSetting::get('app_android', ''),
            'app_ios' => SiteSetting::get('app_ios', ''),
            'maintenance_mode' => (bool) SiteSetting::get('maintenance_mode', false),
            'maintenance_message' => SiteSetting::get('maintenance_message', ''),
            'enable_registration' => (bool) SiteSetting::get('enable_registration', true),
            'default_locale' => SiteSetting::get('default_locale', 'ar'),
            'items_per_page' => (int) SiteSetting::get('items_per_page', 15),
            'training_sessions_title' => SiteSetting::get('training_sessions_title', 'مدربونا الخبراء'),
            'training_sessions_description' => SiteSetting::get('training_sessions_description', ''),
            'training_sessions_count' => (int) SiteSetting::get('training_sessions_count', 4),
            'training_sessions_enabled' => (bool) SiteSetting::get('training_sessions_enabled', true),
            'testimonials_title' => SiteSetting::get('testimonials_title', 'ماذا يقول عملاؤنا'),
            'testimonials_description' => SiteSetting::get('testimonials_description', ''),
            'testimonials_count' => (int) SiteSetting::get('testimonials_count', 3),
            'testimonials_enabled' => (bool) SiteSetting::get('testimonials_enabled', true),
            'articles_enabled' => (bool) SiteSetting::get('articles_enabled', true),
            'articles_count' => (int) SiteSetting::get('articles_count', 3),
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
                                Forms\Components\TextInput::make('app_android')->label('رابط أندرويد')->url(),
                                Forms\Components\TextInput::make('app_ios')->label('رابط iOS')->url(),
                            ])
                            ->columns(2),
                        Forms\Components\Tabs\Tab::make('التطبيق')
                            ->schema([
                                Forms\Components\Toggle::make('enable_registration')->label('تفعيل التسجيل'),
                                Forms\Components\Toggle::make('maintenance_mode')->label('وضع الصيانة'),
                                Forms\Components\Textarea::make('maintenance_message')->label('رسالة الصيانة')->rows(2)->columnSpanFull(),
                                Forms\Components\Select::make('default_locale')
                                    ->label('اللغة الافتراضية')
                                    ->options(['ar' => 'العربية', 'en' => 'English'])
                                    ->native(false),
                                Forms\Components\TextInput::make('items_per_page')
                                    ->label('عدد العناصر في الصفحة')
                                    ->numeric()
                                    ->minValue(5)
                                    ->maxValue(100),
                            ])
                            ->columns(2),
                        Forms\Components\Tabs\Tab::make('الصفحة الرئيسية')
                            ->schema([
                                Forms\Components\Section::make('جلسات التدريب')
                                    ->schema([
                                        Forms\Components\Toggle::make('training_sessions_enabled')->label('تفعيل القسم'),
                                        Forms\Components\TextInput::make('training_sessions_title')->label('العنوان')->maxLength(255),
                                        Forms\Components\TextInput::make('training_sessions_count')->label('عدد العناصر')->numeric()->minValue(1)->maxValue(12),
                                        Forms\Components\Textarea::make('training_sessions_description')->label('الوصف')->rows(2)->columnSpanFull(),
                                    ])
                                    ->columns(2),
                                Forms\Components\Section::make('الشهادات')
                                    ->schema([
                                        Forms\Components\Toggle::make('testimonials_enabled')->label('تفعيل القسم'),
                                        Forms\Components\TextInput::make('testimonials_title')->label('العنوان')->maxLength(255),
                                        Forms\Components\TextInput::make('testimonials_count')->label('عدد العناصر')->numeric()->minValue(1)->maxValue(10),
                                        Forms\Components\Textarea::make('testimonials_description')->label('الوصف')->rows(2)->columnSpanFull(),
                                    ])
                                    ->columns(2),
                                Forms\Components\Section::make('المقالات')
                                    ->schema([
                                        Forms\Components\Toggle::make('articles_enabled')->label('تفعيل القسم'),
                                        Forms\Components\TextInput::make('articles_count')->label('عدد المقالات')->numeric()->minValue(1)->maxValue(12),
                                    ])
                                    ->columns(2),
                            ]),
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

        SiteSetting::set('app_android', $data['app_android'] ?? '', 'app', 'string', 'Android app URL');
        SiteSetting::set('app_ios', $data['app_ios'] ?? '', 'app', 'string', 'iOS app URL');
        SiteSetting::set('maintenance_mode', (bool) ($data['maintenance_mode'] ?? false), 'app', 'boolean', 'Maintenance mode');
        SiteSetting::set('maintenance_message', $data['maintenance_message'] ?? '', 'app', 'string', 'Maintenance message');
        SiteSetting::set('enable_registration', (bool) ($data['enable_registration'] ?? false), 'app', 'boolean', 'Enable user registration');
        SiteSetting::set('default_locale', $data['default_locale'] ?? 'ar', 'app', 'string', 'Default locale');
        SiteSetting::set('items_per_page', (int) ($data['items_per_page'] ?? 15), 'app', 'integer', 'Items per page');

        SiteSetting::set('training_sessions_title', $data['training_sessions_title'] ?? '', 'homepage', 'string', 'Training sessions section title');
        SiteSetting::set('training_sessions_description', $data['training_sessions_description'] ?? '', 'homepage', 'string', 'Training sessions section description');
        SiteSetting::set('training_sessions_count', (int) ($data['training_sessions_count'] ?? 4), 'homepage', 'integer', 'Number of training sessions to display');
        SiteSetting::set('training_sessions_enabled', (bool) ($data['training_sessions_enabled'] ?? false), 'homepage', 'boolean', 'Enable training sessions section');
        SiteSetting::set('testimonials_title', $data['testimonials_title'] ?? '', 'homepage', 'string', 'Testimonials section title');
        SiteSetting::set('testimonials_description', $data['testimonials_description'] ?? '', 'homepage', 'string', 'Testimonials section description');
        SiteSetting::set('testimonials_count', (int) ($data['testimonials_count'] ?? 3), 'homepage', 'integer', 'Number of testimonials to display');
        SiteSetting::set('testimonials_enabled', (bool) ($data['testimonials_enabled'] ?? false), 'homepage', 'boolean', 'Enable testimonials section');
        SiteSetting::set('articles_enabled', (bool) ($data['articles_enabled'] ?? false), 'homepage', 'boolean', 'Enable articles section');
        SiteSetting::set('articles_count', (int) ($data['articles_count'] ?? 3), 'homepage', 'integer', 'Number of articles to display');

        SiteSetting::clearGroupCache('general');
        SiteSetting::clearGroupCache('contact');
        SiteSetting::clearGroupCache('social');
        SiteSetting::clearGroupCache('app');
        SiteSetting::clearGroupCache('homepage');
        Cache::forget(TenantCache::key('site_settings_general'));
        Cache::forget(TenantCache::key('site_settings_app'));
        Cache::forget(TenantCache::key('site_settings_homepage'));
        Cache::forget(TenantCache::key('setting_font_family'));
        Cache::forget(TenantCache::key('setting_primary_color'));
        Cache::forget(TenantCache::key('setting_secondary_color'));

        try {
            \App\Models\TrainingSession::clearCache();
            \App\Models\Testimonial::clearCache();
        } catch (\Throwable) {
            // ignore if models/caches unavailable
        }

        Notification::make()
            ->title('تم حفظ إعدادات الموقع')
            ->success()
            ->send();
    }
}
