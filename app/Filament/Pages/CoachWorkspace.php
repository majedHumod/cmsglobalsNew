<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ClientResource;
use App\Filament\Resources\CoachAvailabilityResource;
use App\Models\SessionBooking;
use App\Models\User;
use App\Services\CoachRiskService;
use App\Services\MessagingService;
use App\Services\NotificationFeedService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class CoachWorkspace extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static string $view = 'filament.pages.coach-workspace';

    protected static ?string $navigationGroup = 'التدريب والحجوزات';

    protected static ?string $navigationLabel = 'مساحة عمل المدرب';

    protected static ?string $title = 'مساحة عمل المدرب';

    protected static ?int $navigationSort = 0;

    public ?string $filter = null;

    public ?int $coachId = null;

    public bool $isAdmin = false;

    public array $summary = [];

    /** @var array<int, array{id:int,name:string}> */
    public array $coaches = [];

    /** @var array<int, array<string, mixed>> */
    public array $atRiskClients = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'coach']) ?? false;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('clients')
                ->label('كل العملاء')
                ->icon('heroicon-o-users')
                ->color('gray')
                ->url(ClientResource::getUrl('index')),
            Action::make('availability')
                ->label('إدارة التوفر')
                ->icon('heroicon-o-calendar-days')
                ->url(CoachAvailabilityResource::getUrl('index')),
        ];
    }

    public function mount(CoachRiskService $coachRiskService): void
    {
        $user = auth()->user();
        $this->isAdmin = $user?->hasRole('admin') ?? false;
        $this->filter = request()->query('filter');

        if ($this->isAdmin && request()->filled('coach_id')) {
            $this->coachId = (int) request()->query('coach_id');
        }

        if ($this->isAdmin) {
            $this->coaches = User::query()
                ->coaches()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (User $coach) => ['id' => $coach->id, 'name' => $coach->name])
                ->all();
        }

        $this->refreshWorkspace($coachRiskService);
    }

    public function setFilter(?string $filter): void
    {
        $this->filter = $filter ?: null;
        $this->refreshWorkspace(app(CoachRiskService::class));
    }

    public function setCoachId(?int $coachId): void
    {
        if (! $this->isAdmin) {
            return;
        }

        $this->coachId = $coachId ?: null;
        $this->refreshWorkspace(app(CoachRiskService::class));
    }

    public function sendReminder(int $userId): void
    {
        $coach = auth()->user();
        $client = User::query()->clients()->findOrFail($userId);

        if ($coach->hasRole('coach') && ! $coach->hasRole('admin') && (int) $client->coach_id !== (int) $coach->id) {
            Notification::make()->title('لا يمكنك تذكير هذا العميل')->danger()->send();

            return;
        }

        $messageBody = 'مدربك يطلب منك متابعة برنامجك اليومي وإرسال تحديث.';

        if ($client->coach_id || $coach->hasRole('admin')) {
            $messaging = app(MessagingService::class);
            $conversation = $messaging->findOrCreateDirectConversation($coach, $client, 'تذكير من المدرب');
            $messaging->sendMessage($conversation, $coach, $messageBody);
        }

        app(NotificationFeedService::class)->pushToUser(
            $client->id,
            'coach.reminder',
            'تذكير من المدرب',
            $messageBody,
            [
                'coach_id' => $coach->id,
                'messages_url' => route('client.messages.index'),
            ]
        );

        Notification::make()
            ->title('تم إرسال التذكير للعميل')
            ->success()
            ->send();
    }

    protected function refreshWorkspace(CoachRiskService $coachRiskService): void
    {
        $user = auth()->user();
        $coachId = $this->isAdmin ? $this->coachId : null;

        $summary = $coachRiskService->summaryFor($user, $coachId);
        $summary['upcomingBookings'] = SessionBooking::query()
            ->whereHas('trainingSession', function ($query) use ($user, $coachId) {
                if ($user->hasRole('coach') && ! $user->hasRole('admin')) {
                    $query->where('user_id', $user->id);
                } elseif ($coachId) {
                    $query->where('user_id', $coachId);
                }
            })
            ->upcoming()
            ->count();

        $this->summary = $summary;
        $this->atRiskClients = $coachRiskService
            ->atRiskClients($user, 30, $this->filter, $coachId)
            ->all();
    }

    public function reasonLabel(string $reason): string
    {
        return match ($reason) {
            'checkin_overdue' => 'Check-in متأخر',
            'low_compliance' => 'التزام تمارين منخفض',
            'low_habits' => 'عادات منخفضة',
            'low_nutrition' => 'التزام غذائي منخفض',
            'expiring_soon' => 'عضوية تنتهي قريباً',
            default => $reason,
        };
    }

    public function priorityClasses(string $priority): string
    {
        return match ($priority) {
            'high' => 'bg-red-100 text-red-800 dark:bg-red-400/10 dark:text-red-300',
            'medium' => 'bg-amber-100 text-amber-800 dark:bg-amber-400/10 dark:text-amber-300',
            default => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-400/10 dark:text-emerald-300',
        };
    }

    public function rateClasses(float|int $rate, int $threshold = 50): string
    {
        return ((float) $rate) < $threshold
            ? 'text-red-600 dark:text-red-400 font-semibold'
            : 'text-emerald-700 dark:text-emerald-400';
    }
}
