<?php

namespace App\Http\Resources\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HabitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $today = now()->toDateString();
        $todayLog = $this->logs->first(function ($log) use ($today) {
            return optional($log->logged_on)->toDateString() === $today;
        });

        $isCompletedToday = (bool) ($todayLog?->is_completed);
        $iconKey = $this->resolveIconKey();
        $description = $this->resolveDescription();
        $weekDays = $this->buildWeekDays();

        $weekCompletedDays = collect($weekDays)->where('is_completed', true)->count();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'title' => $this->name,
            'description' => $description,
            'unit' => $this->unit,
            'target_value' => $this->target_value,
            'target_label' => $this->formatTargetLabel(),
            'is_active' => (bool) $this->is_active,
            'icon_key' => $iconKey,
            'week_completed_days' => $weekCompletedDays,
            'week_days' => $weekDays,
            'is_completed_today' => $isCompletedToday,
            'action_label' => $isCompletedToday ? 'مكتمل' : 'سجّل',
            'can_log' => ! $isCompletedToday,
            'today_log' => $todayLog ? [
                'value' => $todayLog->value,
                'is_completed' => (bool) $todayLog->is_completed,
                'logged_on' => optional($todayLog->logged_on)->toDateString(),
            ] : null,
            'actions' => [
                'log_url' => url("/api/v1/habits/{$this->id}/log"),
                'can_log' => ! $isCompletedToday,
                'log_label' => $isCompletedToday ? 'مكتمل' : 'سجّل',
            ],
        ];
    }

    private function resolveIconKey(): string
    {
        $name = mb_strtolower((string) $this->name);

        return match (true) {
            str_contains($name, 'ماء') || str_contains($name, 'water') => 'water',
            str_contains($name, 'خطوة') || str_contains($name, 'steps') => 'steps',
            str_contains($name, 'وجبه') || str_contains($name, 'وجبة') || str_contains($name, 'صحي') || str_contains($name, 'meal') => 'meals',
            str_contains($name, 'قراء') || str_contains($name, 'كتاب') || str_contains($name, 'read') => 'reading',
            str_contains($name, 'نوم') || str_contains($name, 'sleep') => 'sleep',
            str_contains($name, 'قوة') || str_contains($name, 'تمرين') || str_contains($name, 'strength') => 'strength',
            default => 'habit',
        };
    }

    private function resolveDescription(): string
    {
        $name = (string) $this->name;
        $target = (int) $this->target_value;
        $unit = (string) ($this->unit ?? '');

        return match ($this->resolveIconKey()) {
            'water' => $target > 0 ? "اشرب {$target} {$unit} ماء" : 'اشرب كمية كافية من الماء',
            'steps' => $target > 0 ? "حقق ".number_format($target)." خطوة" : 'حقق هدف الخطوات اليومي',
            'meals' => 'التزم بوجبات صحية اليوم',
            'reading' => $target > 0 ? "اقرأ {$target} {$unit}" : 'اقرأ اليوم',
            'sleep' => $target > 0 ? "نم {$target} {$unit}" : 'احرص على نوم كافٍ',
            'strength' => $target > 0 ? "أكمل تمرين قوة لمدة {$target} {$unit}" : 'أكمل تمرين القوة',
            default => $unit !== '' && $target > 0 ? "{$name}: {$target} {$unit}" : $name,
        };
    }

    private function formatTargetLabel(): ?string
    {
        $target = (int) $this->target_value;
        $unit = trim((string) ($this->unit ?? ''));
        if ($target <= 0) {
            return null;
        }

        return $unit !== '' ? "{$target} {$unit}" : (string) $target;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildWeekDays(): array
    {
        $start = now()->copy()->startOfWeek(Carbon::SATURDAY)->startOfDay();
        $labels = ['س', 'ح', 'ن', 'ث', 'ر', 'خ', 'ج'];
        $logsByDate = $this->logs
            ->filter(fn ($log) => (bool) $log->is_completed)
            ->keyBy(fn ($log) => optional($log->logged_on)->toDateString());

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $start->copy()->addDays($i);
            $dateString = $date->toDateString();
            $days[] = [
                'index' => $i + 1,
                'label' => $labels[$i],
                'date' => $dateString,
                'is_today' => $date->isSameDay(now()),
                'is_completed' => $logsByDate->has($dateString),
            ];
        }

        return $days;
    }
}
