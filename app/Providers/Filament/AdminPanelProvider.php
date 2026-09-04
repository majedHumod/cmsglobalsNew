<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\ManageTrainingSettings;
use App\Http\Middleware\SetFilamentLocale;
use App\Http\Middleware\TenantsMiddleware;
use App\Models\SiteSetting;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('cms')
            ->path('admin-cms')
            ->brandName(fn (): string => $this->resolveBrandName())
            ->brandLogo(fn () => $this->resolveBrandLogo())
            ->brandLogoHeight('2rem')
            ->favicon(fn () => $this->resolveFavicon())
            ->login()
            ->spa()
            ->sidebarCollapsibleOnDesktop()
            ->colors([
                'primary' => Color::Orange,
            ])
            ->font('Tajawal')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
                ManageTrainingSettings::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->navigationGroups([
                NavigationGroup::make()->label('إدارة المحتوى'),
                NavigationGroup::make()->label('العضويات والاشتراكات'),
                NavigationGroup::make()->label('التدريب والحجوزات'),
                NavigationGroup::make()->label('التمارين'),
                NavigationGroup::make()->label('التغذية'),
                NavigationGroup::make()->label('التواصل والمتابعة'),
                NavigationGroup::make()->label('الإعدادات'),
            ])
            ->middleware([
                // Critical: switch tenant DB before session/auth/query.
                TenantsMiddleware::class,
                // Force Arabic UI + RTL for this pilot panel only.
                SetFilamentLocale::class,
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    private function resolveBrandName(): string
    {
        try {
            $name = SiteSetting::get('site_name');
            if (filled($name)) {
                return (string) $name;
            }
        } catch (\Throwable) {
        }

        return (string) config('app.name', 'لوحة الإدارة');
    }

    private function resolveBrandLogo(): ?string
    {
        try {
            $logo = SiteSetting::get('site_logo');
            if (filled($logo) && ! str_starts_with((string) $logo, 'http')) {
                return Storage::disk('public')->url((string) $logo);
            }
            if (filled($logo)) {
                return (string) $logo;
            }
        } catch (\Throwable) {
        }

        // Returning null shows brandName text instead of a Filament default mark.
        return null;
    }

    private function resolveFavicon(): ?string
    {
        try {
            $favicon = SiteSetting::get('site_favicon');
            if (filled($favicon) && ! str_starts_with((string) $favicon, 'http')) {
                return Storage::disk('public')->url((string) $favicon);
            }
            if (filled($favicon)) {
                return (string) $favicon;
            }
        } catch (\Throwable) {
        }

        return null;
    }
}
