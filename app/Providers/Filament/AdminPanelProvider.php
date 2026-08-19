<?php

namespace App\Providers\Filament;

use App\Filament\Pages\ManageTrainingSettings;
use App\Http\Middleware\SetFilamentLocale;
use App\Http\Middleware\TenantsMiddleware;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('cms')
            ->path('admin-cms')
            ->brandName('لوحة الإدارة (Filament)')
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
                Pages\Dashboard::class,
                ManageTrainingSettings::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
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
}
