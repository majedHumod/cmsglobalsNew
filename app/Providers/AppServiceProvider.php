<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use App\Services\TenantCache;
use App\Services\FeatureFlagService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\FileUpload;
use League\Flysystem\UnableToCheckFileExistence;
use Throwable;
use Illuminate\Support\Facades\Event;
use App\Events\BookingLifecycleChanged;
use App\Events\CheckInSubmitted;
use App\Events\MembershipLifecycleChanged;
use App\Events\HabitLogRecorded;
use App\Listeners\SendBookingLifecycleNotifications;
use App\Listeners\SendCheckInNotifications;
use App\Listeners\SendMembershipLifecycleNotifications;
use App\Listeners\SendHabitLogNotifications;
use App\Listeners\AwardHabitGamification;
use App\Listeners\AwardCheckInGamification;
use App\Listeners\ApplyTenantMailBranding;
use Illuminate\Mail\Events\MessageSending;
use App\Services\Communication\CommunicationGatewayInterface;
use App\Services\Communication\WebhookCommunicationGateway;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CommunicationGatewayInterface::class, WebhookCommunicationGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(BookingLifecycleChanged::class, SendBookingLifecycleNotifications::class);
        Event::listen(CheckInSubmitted::class, SendCheckInNotifications::class);
        Event::listen(CheckInSubmitted::class, AwardCheckInGamification::class);
        Event::listen(MembershipLifecycleChanged::class, SendMembershipLifecycleNotifications::class);
        Event::listen(HabitLogRecorded::class, SendHabitLogNotifications::class);
        Event::listen(HabitLogRecorded::class, AwardHabitGamification::class);
        Event::listen(MessageSending::class, ApplyTenantMailBranding::class);

        // Align generated asset URLs with the current tenant host (APP_URL often differs).
        $this->configureRequestAwareStorageUrls();
        $this->configureFilamentUploads();

        // Set locale
        App::setLocale(Session::get('locale', config('app.locale')));
        // Only override exercise content language when the user explicitly chose /lang/{locale}
        if (Session::has('locale')) {
            config(['exercise_localization.runtime_locale' => Session::get('locale')]);
        }
        
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
                                'required_membership_types', 'audience_gender', 'menu_order'
                            ])
                            ->where('show_in_menu', true)
                            ->where('is_published', true)
                            ->orderBy('menu_order')
                            ->get();
                    });
                    
                    // تصفية الصفحات بناءً على صلاحيات المستخدم
                    $user = auth()->user();
                    $menuPages = $allMenuPages->filter(fn ($page) => $page->canAccess($user));
                    
                    $view->with('menuPages', $menuPages);
                }
            } catch (\Exception $e) {
                // If there's an error (like table doesn't exist), just provide empty collection
                $view->with('menuPages', collect());
            }
        });
    }

    protected function configureRequestAwareStorageUrls(): void
    {
        if ($this->app->runningInConsole() || ! $this->app->bound('request')) {
            return;
        }

        $request = request();
        if (! $request || ! $request->getHost()) {
            return;
        }

        $root = $request->getSchemeAndHttpHost();
        URL::forceRootUrl($root);
        config(['filesystems.disks.public.url' => $root.'/storage']);
        Storage::forgetDisk('public');
    }

    protected function configureFilamentUploads(): void
    {
        $normalize = static function (?string $path): ?string {
            if (! filled($path)) {
                return null;
            }

            $path = trim($path);
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                $urlPath = parse_url($path, PHP_URL_PATH) ?: '';
                // Convert absolute storage URLs back to relative disk paths.
                if (is_string($urlPath) && str_contains($urlPath, '/storage/')) {
                    $relative = preg_replace('#^/storage/#', '', $urlPath) ?? '';

                    return $relative !== '' ? ltrim($relative, '/') : null;
                }

                // Keep true external CDN URLs as-is for preview only.
                return $path;
            }

            $path = ltrim((string) $path, '/');
            $path = preg_replace('#^storage/#', '', $path) ?? $path;

            return $path !== '' ? $path : null;
        };

        $publicUrl = static function (string $relative): string {
            $relative = ltrim($relative, '/');
            $root = request()?->getSchemeAndHttpHost();

            return ($root ?: rtrim((string) config('app.url'), '/')).'/storage/'.$relative;
        };

        FileUpload::configureUsing(function (FileUpload $component) use ($normalize, $publicUrl): void {
            $component
                ->visibility('public')
                ->afterStateHydrated(function (BaseFileUpload $component, $state) use ($normalize): void {
                    if (blank($state)) {
                        return;
                    }

                    if (is_array($state)) {
                        $component->state(
                            collect($state)
                                ->map(fn ($item) => is_string($item) ? $normalize($item) : $item)
                                ->filter()
                                ->values()
                                ->all()
                        );

                        return;
                    }

                    if (is_string($state)) {
                        $component->state($normalize($state));
                    }
                })
                ->getUploadedFileUsing(function (BaseFileUpload $component, string $file, string | array | null $storedFileNames) use ($normalize, $publicUrl): ?array {
                    $file = $normalize($file) ?? $file;

                    if (str_starts_with($file, 'http://') || str_starts_with($file, 'https://')) {
                        $name = is_array($storedFileNames) ? ($storedFileNames[$file] ?? null) : $storedFileNames;
                        $name ??= basename(parse_url($file, PHP_URL_PATH) ?: $file);

                        return [
                            'name' => $name,
                            'size' => 0,
                            'type' => null,
                            'url' => $file,
                        ];
                    }

                    $storage = $component->getDisk();
                    $url = $publicUrl($file);
                    $name = is_array($storedFileNames) ? ($storedFileNames[$file] ?? null) : $storedFileNames;
                    $name ??= basename($file);

                    try {
                        if (! $storage->exists($file)) {
                            return [
                                'name' => $name,
                                'size' => 0,
                                'type' => null,
                                'url' => $url,
                            ];
                        }
                    } catch (UnableToCheckFileExistence) {
                        return [
                            'name' => $name,
                            'size' => 0,
                            'type' => null,
                            'url' => $url,
                        ];
                    }

                    try {
                        return [
                            'name' => $name,
                            'size' => $storage->size($file),
                            'type' => $storage->mimeType($file),
                            'url' => $url,
                        ];
                    } catch (Throwable) {
                        return [
                            'name' => $name,
                            'size' => 0,
                            'type' => null,
                            'url' => $url,
                        ];
                    }
                });
        });
    }
}