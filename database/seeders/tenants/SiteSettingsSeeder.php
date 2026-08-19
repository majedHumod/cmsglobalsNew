<?php

namespace Database\Seeders\Tenants;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // General Settings
        SiteSetting::set('site_name', config('app.name', 'CMS Global'), 'general', 'string', 'Site name');
        SiteSetting::set('site_description', 'نظام إدارة محتوى متكامل', 'general', 'string', 'Site description');
        SiteSetting::set('site_logo', null, 'general', 'string', 'Site logo path');
        SiteSetting::set('site_favicon', null, 'general', 'string', 'Site favicon path');
        SiteSetting::set('primary_color', '#6366f1', 'general', 'string', 'Primary color');
        SiteSetting::set('secondary_color', '#10b981', 'general', 'string', 'Secondary color');
        SiteSetting::set('font_family', config('branding.default_font', 'cairo'), 'general', 'string', 'Brand Arabic font family');
        SiteSetting::set('footer_text', '© ' . date('Y') . ' ' . config('app.name', 'CMS Global') . '. جميع الحقوق محفوظة.', 'general', 'string', 'Footer text');

        // Contact Settings
        SiteSetting::set('contact_email', 'info@example.com', 'contact', 'string', 'Contact email');
        SiteSetting::set('contact_phone', '+966541221765', 'contact', 'string', 'Contact phone');
        SiteSetting::set('contact_whatsapp', '+966541221765', 'contact', 'string', 'WhatsApp number');
        SiteSetting::set('contact_telegram', '@cmsglobal', 'contact', 'string', 'Telegram username');
        SiteSetting::set('contact_address', 'الرياض، المملكة العربية السعودية', 'contact', 'string', 'Physical address');
        SiteSetting::set('contact_map_link', 'https://maps.google.com/?q=24.7136,46.6753', 'contact', 'string', 'Google Maps link');

        // Social Media Settings
        SiteSetting::set('social_facebook', 'https://facebook.com/cmsglobal', 'social', 'string', 'Facebook page URL');
        SiteSetting::set('social_twitter', 'https://twitter.com/cmsglobal', 'social', 'string', 'Twitter profile URL');
        SiteSetting::set('social_instagram', 'https://instagram.com/cmsglobal', 'social', 'string', 'Instagram profile URL');
        SiteSetting::set('social_linkedin', 'https://linkedin.com/company/cmsglobal', 'social', 'string', 'LinkedIn profile URL');
        SiteSetting::set('social_youtube', 'https://youtube.com/c/cmsglobal', 'social', 'string', 'YouTube channel URL');

        // App Settings
        SiteSetting::set('app_android', 'https://play.google.com/store/apps/details?id=com.cmsglobal.app', 'app', 'string', 'Android app URL');
        SiteSetting::set('app_ios', 'https://apps.apple.com/app/cmsglobal/id123456789', 'app', 'string', 'iOS app URL');
        SiteSetting::set('maintenance_mode', false, 'app', 'boolean', 'Maintenance mode');
        SiteSetting::set('maintenance_message', 'الموقع قيد الصيانة حالياً. يرجى المحاولة لاحقاً.', 'app', 'string', 'Maintenance message');
        SiteSetting::set('enable_registration', true, 'app', 'boolean', 'Enable user registration');
        SiteSetting::set('default_locale', 'ar', 'app', 'string', 'Default locale');
        SiteSetting::set('items_per_page', 15, 'app', 'integer', 'Items per page');
    }
}