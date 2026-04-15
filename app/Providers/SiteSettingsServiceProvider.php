<?php

namespace App\Providers;

use App\Models\SiteSetting;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use App\Services\TenantCache;

class SiteSettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share site settings with all views
        // Create default settings
        $defaultSettings = [
            'general' => [
                'site_name' => config('app.name', 'Laravel'),
                'site_description' => 'نظام إدارة محتوى متكامل يوفر حلول متقدمة لإدارة المحتوى الرقمي.',
                'site_logo' => null,
                'site_favicon' => null,
                'primary_color' => '#6366f1',
                'secondary_color' => '#10b981',
                'footer_text' => '© ' . date('Y') . ' ' . config('app.name', 'Laravel') . '. جميع الحقوق محفوظة.'
            ],
            'contact' => [
                'contact_email' => 'info@example.com',
                'contact_phone' => '+966541221765',
                'contact_whatsapp' => '+966541221765',
                'contact_telegram' => '@cmsglobal',
                'contact_address' => 'الرياض، المملكة العربية السعودية',
                'contact_map_link' => 'https://maps.google.com/?q=24.7136,46.6753'
            ],
            'social' => [
                'social_facebook' => 'https://facebook.com/cmsglobal',
                'social_twitter' => 'https://twitter.com/cmsglobal',
                'social_instagram' => 'https://instagram.com/cmsglobal',
                'social_linkedin' => 'https://linkedin.com/company/cmsglobal',
                'social_youtube' => 'https://youtube.com/c/cmsglobal'
            ],
            'app' => [
                'app_android' => 'https://play.google.com/store/apps/details?id=com.cmsglobal.app',
                'app_ios' => 'https://apps.apple.com/app/cmsglobal/id123456789',
                'maintenance_mode' => false,
                'maintenance_message' => 'الموقع قيد الصيانة حالياً. يرجى المحاولة لاحقاً.',
                'enable_registration' => true,
                'default_locale' => 'ar',
                'items_per_page' => 15
            ],
            'homepage' => [
                'training_sessions_title' => 'مدربونا الخبراء',
                'training_sessions_description' => 'تعرف على مدربينا المعتمدين المتخصصين في إرشادك خلال رحلتك مع الدعم الشخصي والتعليمات الواعية وممارسات العافية الشاملة',
                'training_sessions_count' => 4,
                'training_sessions_enabled' => true,
                'testimonials_title' => 'ماذا يقول عملاؤنا',
                'testimonials_description' => 'اكتشف تجارب عملائنا الحقيقية وكيف ساعدتهم خدماتنا في تحقيق أهدافهم وتحسين حياتهم بطرق مذهلة ومؤثرة.',
                'testimonials_count' => 3,
                'testimonials_enabled' => true,
                'articles_enabled' => true,
                'articles_count' => 3,
            ]
        ];

        // Try to load settings from database
        $dbSettings = $defaultSettings;
        try {
            if (Schema::hasTable('site_settings')) {
                // Get settings from database with caching
                $generalSettings = Cache::remember(TenantCache::key('site_settings_general'), 7200, function () {
                    return SiteSetting::getGroup('general')->toArray();
                });
                $contactSettings = Cache::remember(TenantCache::key('site_settings_contact'), 7200, function () {
                    return SiteSetting::getGroup('contact')->toArray();
                });
                $socialSettings = Cache::remember(TenantCache::key('site_settings_social'), 7200, function () {
                    return SiteSetting::getGroup('social')->toArray();
                });
                $appSettings = Cache::remember(TenantCache::key('site_settings_app'), 7200, function () {
                    return SiteSetting::getGroup('app')->toArray();
                });
                $homepageSettings = Cache::remember(TenantCache::key('site_settings_homepage'), 7200, function () {
                    return SiteSetting::getGroup('homepage')->toArray();
                });
                
                // Merge with defaults (so we always have all keys)
                $dbSettings = [
                    'general' => array_merge($defaultSettings['general'], $generalSettings),
                    'contact' => array_merge($defaultSettings['contact'], $contactSettings),
                    'social' => array_merge($defaultSettings['social'], $socialSettings),
                    'app' => array_merge($defaultSettings['app'], $appSettings),
                    'homepage' => array_merge($defaultSettings['homepage'], $homepageSettings)
                ];
            }
        } catch (\Exception $e) {
            // If there's an error, we'll use the default settings
            \Log::error('Error loading site settings: ' . $e->getMessage());
        }

        // Share settings with all views
        View::share('siteSettings', $dbSettings);
    }
}