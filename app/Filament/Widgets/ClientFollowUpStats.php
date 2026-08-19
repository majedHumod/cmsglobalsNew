<?php

namespace App\Filament\Widgets;

use App\Models\SessionBooking;
use App\Models\User;
use App\Services\CoachRiskService;
use App\Services\WorkoutScheduleService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ClientFollowUpStats extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected static bool $isLazy = false;

    public ?User $record = null;

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        if (! $this->record) {
            return [];
        }

        $assessment = app(CoachRiskService::class)->assessClient($this->record);
        $workoutRate = app(WorkoutScheduleService::class)->complianceRateForClient($this->record);
        $upcoming = SessionBooking::query()
            ->where('user_id', $this->record->id)
            ->upcoming()
            ->count();

        return [
            Stat::make('التزام التمارين', number_format((float) $workoutRate, 0).'%')
                ->description('آخر 7 أيام')
                ->descriptionIcon('heroicon-m-bolt')
                ->icon('heroicon-o-bolt')
                ->color($workoutRate >= 50 ? 'success' : 'danger'),
            Stat::make('درجة المخاطر', ($assessment['risk_score'] ?? 0).'%')
                ->description('مؤشر الانقطاع')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->icon('heroicon-o-exclamation-triangle')
                ->color(($assessment['risk_score'] ?? 0) >= 60 ? 'danger' : 'warning'),
            Stat::make('العادات الأسبوعية', ($assessment['habit_weekly_completion'] ?? 0).'%')
                ->description('إكمال العادات')
                ->descriptionIcon('heroicon-m-check-circle')
                ->icon('heroicon-o-check-circle')
                ->color('primary'),
            Stat::make('التغذية', ($assessment['nutrition_adherence'] ?? 0).'%')
                ->description('التزام غذائي · حجوزات قادمة: '.$upcoming)
                ->descriptionIcon('heroicon-m-cake')
                ->icon('heroicon-o-calendar-days')
                ->color('info'),
        ];
    }
}
