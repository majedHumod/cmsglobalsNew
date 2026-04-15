<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use App\Services\TenantCache;
use Illuminate\Support\Facades\Blade;
use App\Services\FeatureFlagService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set locale
        App::setLocale(Session::get('locale', config('app.locale')));
        
        // Blade feature flag directive: @feature('flag') ... @endfeature
        Blade::if('feature', function (string $flag) {
            return FeatureFlagService::enabled($flag, false);
        });
        
        // Share menu pages with all views
        View::composer('*', function ($view) {
            try {
                // Only load pages if we're in a tenant context and the pages table exists
                if (class_exists(\App\Models\Page::class)) {
                    // Cache menu pages for better performance
                    $allMenuPages = Cache::remember(TenantCache::key('menu_pages'), 3600, function () {
                        return \App\Models\Page::select([
                                'id', 'title', 'slug', 'access_level', 
                                'required_membership_types', 'menu_order'
                            ])
                            ->where('show_in_menu', true)
                            ->where('is_published', true)
                            ->orderBy('menu_order')
                            ->get();
                    });
                    
                    // تصفية الصفحات بناءً على صلاحيات المستخدم
                    $user = auth()->user();
                    $menuPages = $allMenuPages->filter(function($page) use ($user) {
                        // الصفحات العامة متاحة للجميع
                        if ($page->access_level === 'public') {
                            return true;
                        }
                        
                        // إذا لم يكن المستخدم مسجل الدخول
                        if (!$user) {
                            return false;
                        }
                        
                        // المستخدمين المسجلين
                        if ($page->access_level === 'authenticated') {
                            return true;
                        }
                        
                        // المستخدمين العاديين
                        if ($page->access_level === 'user' && $user->hasRole('user')) {
                            return true;
                        }
                        
                        // مديري الصفحات
                        if ($page->access_level === 'page_manager' && $user->hasRole('page_manager')) {
                            return true;
                        }
                        
                        // المديرين
                        if ($page->access_level === 'admin' && $user->hasRole('admin')) {
                            return true;
                        }
                        
                        // العضويات المدفوعة
                        if ($page->access_level === 'membership' && $user->membership_type_id) {
                            $requiredTypes = $page->required_membership_types;
                            if (is_string($requiredTypes)) {
                                $requiredTypes = json_decode($requiredTypes, true) ?: [];
                            }
                            
                            return in_array($user->membership_type_id, $requiredTypes);
                        }
                        
                        return false;
                    });
                    
                    $view->with('menuPages', $menuPages);
                }
            } catch (\Exception $e) {
                // If there's an error (like table doesn't exist), just provide empty collection
                $view->with('menuPages', collect());
            }
        });
    }
}