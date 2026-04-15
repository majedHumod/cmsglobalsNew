<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SiteSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display the settings page
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get settings by groups
        $generalSettings = SiteSetting::where('group', 'general')->get();
        $contactSettings = SiteSetting::where('group', 'contact')->get();
        $socialSettings = SiteSetting::where('group', 'social')->get();
        $appSettings = SiteSetting::where('group', 'app')->get();
        
        return view('admin.settings.index', compact(
            'generalSettings',
            'contactSettings',
            'socialSettings',
            'appSettings'
        ));
    }

    /**
     * Update the general settings
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateGeneral(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string|max:500',
            'site_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'site_favicon' => 'nullable|image|mimes:ico,png|max:1024',
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'footer_text' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Handle site logo upload
        if ($request->hasFile('site_logo')) {
            $logoPath = $this->handleFileUpload($request->file('site_logo'), 'logos', SiteSetting::get('site_logo'));
            SiteSetting::set('site_logo', $logoPath, 'general', 'string', 'Site logo path');
        }

        // Handle favicon upload
        if ($request->hasFile('site_favicon')) {
            $faviconPath = $this->handleFileUpload($request->file('site_favicon'), 'favicons', SiteSetting::get('site_favicon'));
            SiteSetting::set('site_favicon', $faviconPath, 'general', 'string', 'Site favicon path');
        }

        // Update text settings
        SiteSetting::set('site_name', $request->site_name, 'general', 'string', 'Site name');
        SiteSetting::set('site_description', $request->site_description, 'general', 'string', 'Site description');
        SiteSetting::set('primary_color', $request->primary_color, 'general', 'string', 'Primary color');
        SiteSetting::set('secondary_color', $request->secondary_color, 'general', 'string', 'Secondary color');
        SiteSetting::set('footer_text', $request->footer_text, 'general', 'string', 'Footer text');

        return back()->with('success', 'تم تحديث الإعدادات العامة بنجاح.');
    }

    /**
     * Update the contact settings
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateContact(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_whatsapp' => 'nullable|string|max:20',
            'contact_telegram' => 'nullable|string|max:255',
            'contact_address' => 'nullable|string|max:500',
            'contact_map_link' => 'nullable|url|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Update contact settings
        SiteSetting::set('contact_email', $request->contact_email, 'contact', 'string', 'Contact email');
        SiteSetting::set('contact_phone', $request->contact_phone, 'contact', 'string', 'Contact phone');
        SiteSetting::set('contact_whatsapp', $request->contact_whatsapp, 'contact', 'string', 'WhatsApp number');
        SiteSetting::set('contact_telegram', $request->contact_telegram, 'contact', 'string', 'Telegram username');
        SiteSetting::set('contact_address', $request->contact_address, 'contact', 'string', 'Physical address');
        SiteSetting::set('contact_map_link', $request->contact_map_link, 'contact', 'string', 'Google Maps link');

        return back()->with('success', 'تم تحديث معلومات الاتصال بنجاح.');
    }

    /**
     * Update the social media settings
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSocial(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'social_facebook' => 'nullable|url|max:255',
            'social_twitter' => 'nullable|url|max:255',
            'social_instagram' => 'nullable|url|max:255',
            'social_linkedin' => 'nullable|url|max:255',
            'social_youtube' => 'nullable|url|max:255',
            'app_android' => 'nullable|url|max:255',
            'app_ios' => 'nullable|url|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Update social media settings
        SiteSetting::set('social_facebook', $request->social_facebook, 'social', 'string', 'Facebook page URL');
        SiteSetting::set('social_twitter', $request->social_twitter, 'social', 'string', 'Twitter profile URL');
        SiteSetting::set('social_instagram', $request->social_instagram, 'social', 'string', 'Instagram profile URL');
        SiteSetting::set('social_linkedin', $request->social_linkedin, 'social', 'string', 'LinkedIn profile URL');
        SiteSetting::set('social_youtube', $request->social_youtube, 'social', 'string', 'YouTube channel URL');
        
        // Update app links
        SiteSetting::set('app_android', $request->app_android, 'app', 'string', 'Android app URL');
        SiteSetting::set('app_ios', $request->app_ios, 'app', 'string', 'iOS app URL');

        return back()->with('success', 'تم تحديث روابط التواصل الاجتماعي بنجاح.');
    }

    /**
     * Update the app settings
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateApp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'maintenance_mode' => 'nullable|boolean',
            'maintenance_message' => 'nullable|string|max:500',
            'enable_registration' => 'nullable|boolean',
            'default_locale' => 'nullable|string|in:ar,en',
            'items_per_page' => 'nullable|integer|min:5|max:100',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Update app settings
        SiteSetting::set('maintenance_mode', $request->has('maintenance_mode'), 'app', 'boolean', 'Maintenance mode');
        SiteSetting::set('maintenance_message', $request->maintenance_message, 'app', 'string', 'Maintenance message');
        SiteSetting::set('enable_registration', $request->has('enable_registration'), 'app', 'boolean', 'Enable user registration');
        SiteSetting::set('default_locale', $request->default_locale, 'app', 'string', 'Default locale');
        SiteSetting::set('items_per_page', $request->items_per_page, 'app', 'integer', 'Items per page');

        return back()->with('success', 'تم تحديث إعدادات التطبيق بنجاح.');
    }

    /**
     * Update the homepage settings
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateHomepage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'training_sessions_title' => 'nullable|string|max:255',
            'training_sessions_description' => 'nullable|string|max:500',
            'training_sessions_count' => 'nullable|integer|min:1|max:12',
            'training_sessions_enabled' => 'nullable|boolean',
            'testimonials_title' => 'nullable|string|max:255',
            'testimonials_description' => 'nullable|string|max:500',
            'testimonials_count' => 'nullable|integer|min:1|max:10',
            'testimonials_enabled' => 'nullable|boolean',
            'articles_enabled' => 'nullable|boolean',
            'articles_count' => 'nullable|integer|min:1|max:12',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Update training sessions settings
        SiteSetting::set('training_sessions_title', $request->training_sessions_title, 'homepage', 'string', 'Training sessions section title');
        SiteSetting::set('training_sessions_description', $request->training_sessions_description, 'homepage', 'string', 'Training sessions section description');
        SiteSetting::set('training_sessions_count', $request->training_sessions_count, 'homepage', 'integer', 'Number of training sessions to display');
        SiteSetting::set('training_sessions_enabled', $request->has('training_sessions_enabled'), 'homepage', 'boolean', 'Enable training sessions section');
        
        // Update testimonials settings
        SiteSetting::set('testimonials_title', $request->testimonials_title, 'homepage', 'string', 'Testimonials section title');
        SiteSetting::set('testimonials_description', $request->testimonials_description, 'homepage', 'string', 'Testimonials section description');
        SiteSetting::set('testimonials_count', $request->testimonials_count, 'homepage', 'integer', 'Number of testimonials to display');
        SiteSetting::set('testimonials_enabled', $request->has('testimonials_enabled'), 'homepage', 'boolean', 'Enable testimonials section');
        SiteSetting::set('articles_enabled', $request->has('articles_enabled'), 'homepage', 'boolean', 'Enable articles section');
        SiteSetting::set('articles_count', $request->articles_count ?: 3, 'homepage', 'integer', 'Number of articles to display');

        // Clear related caches to ensure immediate effect
        \App\Models\TrainingSession::clearCache();
        \App\Models\Testimonial::clearCache();
        
        return back()->with('success', 'تم تحديث إعدادات الصفحة الرئيسية بنجاح.');
    }
    
    /**
     * Handle file upload and return the file path
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $directory
     * @param string|null $oldPath
     * @return string
     */
    private function handleFileUpload($file, $directory, $oldPath = null)
    {
        // Delete old file if exists
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        // Generate a unique filename
        $filename = Str::random(20) . '.' . $file->getClientOriginalExtension();
        
        // Store the file
        $path = $file->storeAs($directory, $filename, 'public');
        
        return $path;
    }
}