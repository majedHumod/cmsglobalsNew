<?php

namespace App\Filament\Widgets;

use App\Models\SessionBooking;
use App\Services\CoachRiskService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CoachWorkspaceStats extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected static bool $isLazy = false;

    public ?int $coachId = null;

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        $coachId = ($user?->hasRole('admin') ?? false) ? $this->coachId : null;
        $summary = app(CoachRiskService::class)->summaryFor($user, $coachId);

        $upcoming = SessionBooking::query()
            ->whereHas('trainingSession', function ($query) use ($user, $coachId) {
                if ($user->hasRole('coach') && ! $user->hasRole('admin')) {
                    $query->where('user_id', $user->id);
                } elseif ($coachId) {
                    $query->where('user_id', $coachId);
                }
            })
            ->upcoming()
            ->count();

        $avgWorkout = number_format((float) ($summary['workout_completion_rate'] ?? 0), 0);

        return [
            Stat::make('إجمالي العملاء', (string) ($summary['clients'] ?? 0))
                ->description('كل العملاء ضمن العرض الحالي')
                ->descriptionIcon('heroicon-m-users')
                ->icon('heroicon-o-users')
                ->color('primary'),

            Stat::make('معرّضون للانقطاع', (string) ($summary['atRiskCount'] ?? 0))
                ->description('يحتاجون متابعة عاجلة')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('danger'),

            Stat::make('Check-in متأخر', (string) ($summary['clientsNeedingCheckIn'] ?? 0))
                ->description('أكثر من 14 يوماً بدون تحديث')
                ->descriptionIcon('heroicon-m-clock')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('warning'),

            Stat::make('التزام تمارين منخفض', (string) ($summary['clientsLowWorkoutCompliance'] ?? 0))
                ->description('أقل من 50% التزام')
                ->descriptionIcon('heroicon-m-bolt')
                ->icon('heroicon-o-bolt')
                ->color('warning'),

            Stat::make('التزام غذائي منخفض', (string) ($summary['clientsLowNutrition'] ?? 0))
                ->description('أداء التغذية دون المستوى')
                ->descriptionIcon('heroicon-m-cake')
                ->icon('heroicon-o-cake')
                ->color('info'),

            Stat::make('حجوزات قادمة', (string) $upcoming)
                ->description("متوسط التزام التمارين: {$avgWorkout}%")
                ->descriptionIcon('heroicon-m-calendar-days')
                ->icon('heroicon-o-calendar-days')
                ->color('success'),
        ];
    }
}
