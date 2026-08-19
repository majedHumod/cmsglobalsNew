<?php

namespace App\Filament\Resources\WorkoutScheduleResource\Pages;

use App\Filament\Resources\WorkoutScheduleResource;
use App\Models\User;
use App\Models\WorkoutSchedule;
use Filament\Actions;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;

class WeeklyBoard extends Page
{
    protected static string $resource = WorkoutScheduleResource::class;

    protected static string $view = 'filament.resources.workout-schedule-resource.pages.weekly-board';

    protected static ?string $title = 'الجدول الأسبوعي';

    public int $weekNumber = 1;

    public ?string $coachId = null;

    public bool $isAdmin = false;

    /** @var array<string, string> */
    public array $coachOptions = [];

    /** @var array<int, Collection<int, WorkoutSchedule>> */
    public array $weeklySchedule = [];

    /** @var array{total:int,duration:int,easy:int,hard:int} */
    public array $summary = [
        'total' => 0,
        'duration' => 0,
        'easy' => 0,
        'hard' => 0,
    ];

    public function mount(): void
    {
        $this->isAdmin = auth()->user()?->hasRole('admin') ?? false;

        if ($this->isAdmin) {
            $this->coachOptions = User::query()
                ->coaches()
                ->orderBy('name')
                ->pluck('name', 'id')
                ->mapWithKeys(fn ($name, $id) => [(string) $id => (string) $name])
                ->all();

            $coach = request()->query('coach');
            $this->coachId = filled($coach) ? (string) $coach : null;
        } else {
            $this->coachId = (string) auth()->id();
        }

        $requestedWeek = request()->query('week');
        if ($requestedWeek !== null && $requestedWeek !== '') {
            $this->weekNumber = max(1, min(52, (int) $requestedWeek));
        } else {
            $this->weekNumber = $this->resolveDefaultWeek();
        }

        $this->loadBoard();
    }

    public function getTitle(): string
    {
        return 'الجدول الأسبوعي — الأسبوع '.$this->weekNumber;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('trainingSettings')
                ->label('إعدادات التدريب')
                ->icon('heroicon-o-cog-6-tooth')
                ->color('gray')
                ->url(\App\Filament\Pages\ManageTrainingSettings::getUrl())
                ->visible(fn (): bool => auth()->user()?->hasRole('admin') ?? false),
            Actions\Action::make('listView')
                ->label('عرض القائمة')
                ->icon('heroicon-o-list-bullet')
                ->color('gray')
                ->url(WorkoutScheduleResource::getUrl('list')),
            Actions\Action::make('createSchedule')
                ->label('إضافة موعد تمرين')
                ->icon('heroicon-o-plus')
                ->url(fn (): string => $this->createUrl())
                ->visible(fn (): bool => WorkoutScheduleResource::canCreate()),
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function sessionDayLabels(): array
    {
        return app(\App\Services\WorkoutScheduleService::class)->sessionLabels();
    }

    public function boardUrl(?int $week = null, ?string $coachId = null): string
    {
        $params = [
            'week' => $week ?? $this->weekNumber,
        ];

        $coach = $coachId !== null ? $coachId : $this->coachId;
        if ($this->isAdmin && filled($coach)) {
            $params['coach'] = $coach;
        }

        return WorkoutScheduleResource::getUrl('index', $params);
    }

    public function createUrl(?int $session = null): string
    {
        $query = http_build_query(array_filter([
            'week' => $this->weekNumber,
            'session' => $session,
            'coach' => $this->isAdmin ? $this->coachId : null,
        ], fn ($value) => $value !== null && $value !== ''));

        return WorkoutScheduleResource::getUrl('create').($query !== '' ? '?'.$query : '');
    }

    public function editUrl(WorkoutSchedule $schedule): string
    {
        return WorkoutScheduleResource::getUrl('edit', ['record' => $schedule]);
    }

    public function canDeleteSchedule(WorkoutSchedule $schedule): bool
    {
        return WorkoutScheduleResource::canDelete($schedule);
    }

    protected function resolveDefaultWeek(): int
    {
        $week = $this->baseScheduleQuery()->min('week_number');

        return max(1, min(52, (int) ($week ?: 1)));
    }

    protected function baseScheduleQuery()
    {
        $query = WorkoutSchedule::query();

        if ($this->isAdmin) {
            if (filled($this->coachId)) {
                $query->where('user_id', $this->coachId);
            }
        } else {
            $query->where('user_id', auth()->id());
        }

        return $query;
    }

    protected function loadBoard(): void
    {
        $schedules = $this->baseScheduleQuery()
            ->with(['workout', 'user'])
            ->where('week_number', $this->weekNumber)
            ->orderBy('session_number')
            ->orderBy('id')
            ->get();

        $weekly = [];
        for ($session = 1; $session <= 7; $session++) {
            $weekly[$session] = $schedules->where('session_number', $session)->values();
        }

        $this->weeklySchedule = $weekly;

        $all = $schedules;
        $this->summary = [
            'total' => $all->count(),
            'duration' => (int) $all->sum(fn (WorkoutSchedule $schedule) => (int) ($schedule->workout?->duration ?? 0)),
            'easy' => $all->filter(fn (WorkoutSchedule $schedule) => $schedule->workout?->difficulty === 'easy')->count(),
            'hard' => $all->filter(fn (WorkoutSchedule $schedule) => $schedule->workout?->difficulty === 'hard')->count(),
        ];
    }
}
