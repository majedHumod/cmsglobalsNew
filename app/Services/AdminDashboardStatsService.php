<?php

namespace App\Services;

use App\Models\Article;
use App\Models\CommunityPost;
use App\Models\Conversation;
use App\Models\Exercise;
use App\Models\Faq;
use App\Models\Habit;
use App\Models\LandingPage;
use App\Models\MealPlan;
use App\Models\MembershipType;
use App\Models\Note;
use App\Models\NotificationFeed;
use App\Models\NutritionDiscount;
use App\Models\Page;
use App\Models\SessionBooking;
use App\Models\SubscriptionPlan;
use App\Models\SupplementPlan;
use App\Models\Testimonial;
use App\Models\TrainingSession;
use App\Models\User;
use App\Models\UserMembership;
use App\Models\Workout;
use App\Models\WorkoutSchedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class AdminDashboardStatsService
{
    /**
     * @return array<string, array{label: string, icon: string, stats: list<array{label: string, value: string|int, icon: string, url?: string|null}>}>
     */
    public function tabs(): array
    {
        return [
            'overview' => [
                'label' => 'نظرة عامة',
                'icon' => 'heroicon-o-home',
                'stats' => $this->overviewStats(),
            ],
            'content' => [
                'label' => 'المحتوى',
                'icon' => 'heroicon-o-document-text',
                'stats' => $this->contentStats(),
            ],
            'memberships' => [
                'label' => 'العضويات',
                'icon' => 'heroicon-o-identification',
                'stats' => $this->membershipStats(),
            ],
            'training' => [
                'label' => 'التدريب',
                'icon' => 'heroicon-o-academic-cap',
                'stats' => $this->trainingStats(),
            ],
            'workouts' => [
                'label' => 'التمارين',
                'icon' => 'heroicon-o-bolt',
                'stats' => $this->workoutStats(),
            ],
            'nutrition' => [
                'label' => 'التغذية',
                'icon' => 'heroicon-o-cake',
                'stats' => $this->nutritionStats(),
            ],
            'engagement' => [
                'label' => 'التواصل',
                'icon' => 'heroicon-o-chat-bubble-left-right',
                'stats' => $this->engagementStats(),
            ],
        ];
    }

    /**
     * @return list<array{label: string, value: string|int, icon: string, url?: string|null}>
     */
    public function overviewStats(): array
    {
        return [
            $this->stat('الأعضاء', $this->count(User::class, 'users'), 'heroicon-o-users'),
            $this->stat('اشتراكات نشطة', $this->activeMemberships(), 'heroicon-o-check-badge'),
            $this->stat('حجوزات الجلسات', $this->count(SessionBooking::class, 'session_bookings'), 'heroicon-o-calendar-days'),
            $this->stat('جلسات التدريب', $this->count(TrainingSession::class, 'training_sessions'), 'heroicon-o-academic-cap'),
            $this->stat('الجداول الغذائية', $this->count(MealPlan::class, 'meal_plans'), 'heroicon-o-cake'),
            $this->stat('التمارين', $this->count(Exercise::class, 'exercises'), 'heroicon-o-bolt'),
            $this->stat('محادثات', $this->count(Conversation::class, 'conversations'), 'heroicon-o-chat-bubble-left-right'),
            $this->stat('منشورات المجتمع', $this->count(CommunityPost::class, 'community_posts'), 'heroicon-o-user-group'),
        ];
    }

    /**
     * @return list<array{label: string, value: string|int, icon: string}>
     */
    public function contentStats(): array
    {
        return [
            $this->stat('الصفحات', $this->count(Page::class, 'pages'), 'heroicon-o-document'),
            $this->stat('المقالات', $this->count(Article::class, 'articles'), 'heroicon-o-newspaper'),
            $this->stat('الأسئلة الشائعة', $this->count(Faq::class, 'faqs'), 'heroicon-o-question-mark-circle'),
            $this->stat('قصص النجاح', $this->count(Testimonial::class, 'testimonials'), 'heroicon-o-chat-bubble-bottom-center-text'),
            $this->stat('صفحات الهبوط', $this->count(LandingPage::class, 'landing_pages'), 'heroicon-o-window'),
            $this->stat('الملاحظات', $this->count(Note::class, 'notes'), 'heroicon-o-pencil-square'),
        ];
    }

    /**
     * @return list<array{label: string, value: string|int, icon: string}>
     */
    public function membershipStats(): array
    {
        return [
            $this->stat('أنواع العضويات', $this->count(MembershipType::class, 'membership_types'), 'heroicon-o-rectangle-stack'),
            $this->stat('خطط الاشتراك', $this->count(SubscriptionPlan::class, 'subscription_plans'), 'heroicon-o-credit-card'),
            $this->stat('اشتراكات الأعضاء', $this->count(UserMembership::class, 'user_memberships'), 'heroicon-o-users'),
            $this->stat('اشتراكات نشطة', $this->activeMemberships(), 'heroicon-o-check-badge'),
            $this->stat('تنتهي خلال 7 أيام', $this->expiringMemberships(7), 'heroicon-o-clock'),
            $this->stat('منتهية', $this->expiredMemberships(), 'heroicon-o-x-circle'),
        ];
    }

    /**
     * @return list<array{label: string, value: string|int, icon: string}>
     */
    public function trainingStats(): array
    {
        $user = auth()->user();
        $clients = 0;
        if (Schema::hasTable('users')) {
            $q = User::query()->clients();
            if ($user?->hasRole('coach') && ! $user->hasRole('admin')) {
                $q->where('coach_id', $user->id);
            }
            $clients = $q->count();
        }

        return [
            $this->stat('العملاء', $clients, 'heroicon-o-user-group'),
            $this->stat('جلسات التدريب', $this->count(TrainingSession::class, 'training_sessions'), 'heroicon-o-academic-cap'),
            $this->stat('إجمالي الحجوزات', $this->count(SessionBooking::class, 'session_bookings'), 'heroicon-o-ticket'),
            $this->stat('حجوزات قادمة', $this->upcomingBookings(), 'heroicon-o-calendar'),
            $this->stat('حجوزات اليوم', $this->todayBookings(), 'heroicon-o-calendar-days'),
            $this->stat('حجوزات معلقة', $this->pendingBookings(), 'heroicon-o-exclamation-circle'),
        ];
    }

    /**
     * @return list<array{label: string, value: string|int, icon: string}>
     */
    public function workoutStats(): array
    {
        return [
            $this->stat('مكتبة التمارين', $this->count(Exercise::class, 'exercises'), 'heroicon-o-rectangle-group'),
            $this->stat('البرامج الرياضية', $this->count(Workout::class, 'workouts'), 'heroicon-o-fire'),
            $this->stat('جداول أسبوعية', $this->count(WorkoutSchedule::class, 'workout_schedules'), 'heroicon-o-table-cells'),
            $this->stat('جداول هذا الأسبوع', $this->schedulesThisWeek(), 'heroicon-o-calendar'),
        ];
    }

    /**
     * @return list<array{label: string, value: string|int, icon: string}>
     */
    public function nutritionStats(): array
    {
        return [
            $this->stat('الجداول الغذائية', $this->count(MealPlan::class, 'meal_plans'), 'heroicon-o-cake'),
            $this->stat('خطط المكملات', $this->count(SupplementPlan::class, 'supplement_plans'), 'heroicon-o-beaker'),
            $this->stat('خصومات المراكز', $this->count(NutritionDiscount::class, 'nutrition_discounts'), 'heroicon-o-tag'),
        ];
    }

    /**
     * @return list<array{label: string, value: string|int, icon: string}>
     */
    public function engagementStats(): array
    {
        return [
            $this->stat('المحادثات', $this->count(Conversation::class, 'conversations'), 'heroicon-o-chat-bubble-left-right'),
            $this->stat('الإشعارات', $this->count(NotificationFeed::class, 'notification_feeds'), 'heroicon-o-bell'),
            $this->stat('العادات', $this->count(Habit::class, 'habits'), 'heroicon-o-check-circle'),
            $this->stat('منشورات المجتمع', $this->count(CommunityPost::class, 'community_posts'), 'heroicon-o-globe-alt'),
        ];
    }

    /**
     * @return list<array{day: string, count: int}>
     */
    public function weeklyBookingSeries(): array
    {
        if (! Schema::hasTable('session_bookings')) {
            return collect(range(6, 0))->map(fn (int $i) => [
                'day' => now()->subDays($i)->translatedFormat('D'),
                'count' => 0,
            ])->all();
        }

        try {
            $start = now()->subDays(6)->startOfDay();
            $rows = SessionBooking::query()
                ->where('created_at', '>=', $start)
                ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
                ->groupBy('d')
                ->pluck('c', 'd');

            return collect(range(6, 0))->map(function (int $i) use ($rows) {
                $date = now()->subDays($i);

                return [
                    'day' => $date->translatedFormat('D'),
                    'count' => (int) ($rows[$date->toDateString()] ?? 0),
                ];
            })->all();
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * @return list<array{id: int, title: string, meta: string, status: string, status_color: string}>
     */
    public function recentActivity(int $limit = 6): array
    {
        $items = [];

        if (Schema::hasTable('session_bookings')) {
            try {
                SessionBooking::query()
                    ->with(['user', 'trainingSession'])
                    ->latest()
                    ->take($limit)
                    ->get()
                    ->each(function (SessionBooking $booking) use (&$items) {
                        $status = (string) ($booking->status ?? 'pending');
                        $items[] = [
                            'id' => (int) $booking->id,
                            'title' => $booking->user?->name ?: 'حجز جلسة',
                            'meta' => $booking->trainingSession?->title
                                ?: ($booking->created_at?->diffForHumans() ?? ''),
                            'status' => $this->bookingStatusLabel($status),
                            'status_color' => match ($status) {
                                'confirmed', 'completed', 'done' => 'success',
                                'cancelled', 'canceled' => 'danger',
                                default => 'warning',
                            },
                        ];
                    });
            } catch (\Throwable) {
            }
        }

        if ($items === [] && Schema::hasTable('user_memberships')) {
            try {
                UserMembership::query()
                    ->with(['user', 'membershipType'])
                    ->latest()
                    ->take($limit)
                    ->get()
                    ->each(function (UserMembership $membership) use (&$items) {
                        $active = (bool) $membership->is_active && $membership->expires_at && $membership->expires_at->isFuture();
                        $items[] = [
                            'id' => (int) $membership->id,
                            'title' => $membership->user?->name ?: 'اشتراك',
                            'meta' => $membership->membershipType?->name ?: 'عضوية',
                            'status' => $active ? 'نشط' : 'منتهي',
                            'status_color' => $active ? 'success' : 'gray',
                        ];
                    });
            } catch (\Throwable) {
            }
        }

        return array_slice($items, 0, $limit);
    }

    /**
     * @return array{label: string, value: string|int, icon: string, url?: string|null}
     */
    private function stat(string $label, int|string $value, string $icon, ?string $url = null): array
    {
        return [
            'label' => $label,
            'value' => is_int($value) ? number_format($value) : $value,
            'icon' => $icon,
            'url' => $url,
        ];
    }

    private function count(string $modelClass, string $table): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        try {
            return (int) $modelClass::query()->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function activeMemberships(): int
    {
        if (! Schema::hasTable('user_memberships')) {
            return 0;
        }

        try {
            return UserMembership::query()
                ->where('is_active', true)
                ->where('expires_at', '>', now())
                ->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function expiringMemberships(int $days): int
    {
        if (! Schema::hasTable('user_memberships')) {
            return 0;
        }

        try {
            return UserMembership::query()
                ->where('is_active', true)
                ->whereBetween('expires_at', [now(), now()->addDays($days)])
                ->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function expiredMemberships(): int
    {
        if (! Schema::hasTable('user_memberships')) {
            return 0;
        }

        try {
            return UserMembership::query()
                ->where(function ($q) {
                    $q->where('is_active', false)
                        ->orWhere('expires_at', '<=', now());
                })
                ->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function upcomingBookings(): int
    {
        if (! Schema::hasTable('session_bookings')) {
            return 0;
        }

        try {
            if (method_exists(SessionBooking::class, 'scopeUpcoming')) {
                return SessionBooking::query()->upcoming()->count();
            }

            return SessionBooking::query()
                ->whereDate('scheduled_at', '>=', now()->toDateString())
                ->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function todayBookings(): int
    {
        if (! Schema::hasTable('session_bookings')) {
            return 0;
        }

        try {
            return SessionBooking::query()
                ->whereDate('created_at', Carbon::today())
                ->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function pendingBookings(): int
    {
        if (! Schema::hasTable('session_bookings')) {
            return 0;
        }

        try {
            return SessionBooking::query()
                ->whereIn('status', ['pending', 'awaiting', 'waiting'])
                ->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function schedulesThisWeek(): int
    {
        if (! Schema::hasTable('workout_schedules')) {
            return 0;
        }

        try {
            return WorkoutSchedule::query()
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function bookingStatusLabel(string $status): string
    {
        return match ($status) {
            'confirmed' => 'مؤكد',
            'completed', 'done' => 'مكتمل',
            'cancelled', 'canceled' => 'ملغي',
            'pending' => 'معلق',
            default => $status,
        };
    }
}
