<?php

namespace App\Filament\Pages;

use App\Services\AdminDashboardStatsService;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string $view = 'filament.pages.cms-dashboard';

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'لوحة التحكم';

    protected static ?string $title = 'لوحة التحكم';

    protected static ?int $navigationSort = -2;

    public string $activeTab = 'overview';

    public function getHeading(): string
    {
        $name = auth()->user()?->name ?? '';

        return $name !== '' ? "مرحباً {$name}!" : 'مرحباً!';
    }

    public function getSubheading(): ?string
    {
        return 'ملخص سريع حسب أقسام لوحة التحكم';
    }

    public function getWidgets(): array
    {
        return [];
    }

    /**
     * @return array<string, array{label: string, icon: string, stats: list<array{label: string, value: string|int, icon: string, url?: string|null}>}>
     */
    public function getTabsProperty(): array
    {
        return app(AdminDashboardStatsService::class)->tabs();
    }

    /**
     * @return list<array{label: string, value: string|int, icon: string, url?: string|null}>
     */
    public function getActiveStatsProperty(): array
    {
        return $this->tabs[$this->activeTab]['stats'] ?? [];
    }

    /**
     * @return list<array{day: string, count: int}>
     */
    public function getWeeklySeriesProperty(): array
    {
        return app(AdminDashboardStatsService::class)->weeklyBookingSeries();
    }

    /**
     * @return list<array{id: int, title: string, meta: string, status: string, status_color: string}>
     */
    public function getRecentActivityProperty(): array
    {
        return app(AdminDashboardStatsService::class)->recentActivity();
    }

    public function setTab(string $tab): void
    {
        if (! array_key_exists($tab, $this->tabs)) {
            return;
        }

        $this->activeTab = $tab;
    }
}
