<?php

namespace App\Services;

use App\Http\Resources\Api\ClientHomeResource;
use App\Jobs\EvaluateClientSignalsJob;
use App\Models\Habit;
use App\Models\Message;
use App\Models\NotificationFeed;
use App\Models\Page;
use App\Models\ProgressCheckIn;
use App\Models\SessionBooking;
use App\Models\User;
use App\Models\UserMembership;
use Illuminate\Http\Request;

class ClientHomeService
{
    public function __construct(
        protected WorkoutScheduleService $scheduleService,
        protected MealLogService $mealLogService,
    ) {}

    public function resourceFor(User $user, ?Request $request = null): ClientHomeResource
    {
        return new ClientHomeResource($this->payloadFor($user));
    }

    public function payloadFor(User $user): array
    {
        $today = now()->toDateString();

        $todayBookings = SessionBooking::query()
            ->where('user_id', $user->id)
            ->whereDate('booking_date', $today)
            ->with('trainingSession')
            ->orderBy('booking_time')
            ->get();

        $habitsToday = Habit::query()
            ->active()
            ->where('client_user_id', $user->id)
            ->with(['logs' => fn ($query) => $query->whereBetween(
                'logged_on',
                [now()->copy()->startOfWeek(\Carbon\Carbon::SATURDAY)->toDateString(), $today]
            )])
            ->orderBy('id')
            ->get();

        $lastCheckIn = ProgressCheckIn::query()
            ->where('user_id', $user->id)
            ->latest('checked_in_at')
            ->first();

        EvaluateClientSignalsJob::dispatch($user->id);

        $latestNotification = NotificationFeed::query()
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->first();

        $habitInsights = app(HabitInsightsService::class)->summarize($habitsToday);
        $gamification = app(GamificationService::class)->leaderboard($user);
        $todayWorkouts = $this->scheduleService->todaySchedulesFor($user);
        $weekOverviewRaw = $this->scheduleService->weekOverviewFor($user);
        $weekOverview = $this->enrichWeekOverviewForHome($user, $weekOverviewRaw);
        $workoutCompliance = $this->scheduleService->weeklyComplianceRate($user);
        $nutritionAdherence = $this->mealLogService->weeklyAdherenceRate($user);

        $latestMessage = Message::query()
            ->with('sender')
            ->whereHas('conversation.participants', fn ($q) => $q->where('user_id', $user->id))
            ->where('sender_user_id', '!=', $user->id)
            ->when($user->coach_id, fn ($q) => $q->where('sender_user_id', $user->coach_id))
            ->latest('sent_at')
            ->first();

        // إن لم توجد رسالة من المدرب المعيّن، خذ آخر رسالة واردة للمتدرب.
        if (! $latestMessage) {
            $latestMessage = Message::query()
                ->with('sender')
                ->whereHas('conversation.participants', fn ($q) => $q->where('user_id', $user->id))
                ->where('sender_user_id', '!=', $user->id)
                ->latest('sent_at')
                ->first();
        }

        $communicationsSummary = app(\App\Services\Communication\CommunicationInboxService::class)->summaryFor($user);
        $unreadNotificationsCount = (int) ($communicationsSummary['notifications_unread'] ?? 0);
        $unreadMessagesCount = (int) ($communicationsSummary['messages_unread'] ?? 0);

        $habitScore = (float) ($habitInsights['weekly_completion'] ?? 0);
        $checkInScore = $lastCheckIn && $lastCheckIn->checked_in_at >= now()->subDays(7)
            ? (float) (($lastCheckIn->average_adherence ?? 0) * 10)
            : 0.0;

        $progressScore = round(
            ($habitScore * 0.4) + ($checkInScore * 0.3) + ($workoutCompliance * 0.3),
            1
        );

        $nextBestAction = 'استمر على نفس الوتيرة';
        if ($todayWorkouts->isNotEmpty() && $todayWorkouts->every(fn (array $item) => ! $item['is_completed'] && ! $item['is_skipped'])) {
            $nextBestAction = 'أنجز تمرين اليوم لرفع نسبة التزامك.';
        } elseif (($habitInsights['missed_days'] ?? 0) > 4) {
            $nextBestAction = 'سجّل عادات اليوم لإعادة بناء الالتزام.';
        } elseif (! $lastCheckIn || $lastCheckIn->checked_in_at < now()->subDays(7)) {
            $nextBestAction = 'أرسل متابعة جديدة للمدرب اليوم.';
        }

        $progressHeadline = match (true) {
            $progressScore >= 70 => 'أنت على الطريق الصحيح!',
            $progressScore >= 40 => 'جيد… يمكنك تحسين التزامك اليوم.',
            default => 'ابدأ اليوم بعادة أو تمرين لرفع تقدمك.',
        };

        $progressTrend = match (true) {
            $progressScore >= 70 => 'up',
            $progressScore >= 40 => 'steady',
            default => 'down',
        };

        $currentProgramWeek = $this->scheduleService->currentProgramWeek($user);

        $membershipDaysRemaining = null;
        $renewUrl = null;
        if ($user->membership_expires_at) {
            $membershipDaysRemaining = (int) now()->startOfDay()->diffInDays($user->membership_expires_at->startOfDay(), false);
            if ($membershipDaysRemaining <= 7) {
                $activeMembership = UserMembership::query()
                    ->where('user_id', $user->id)
                    ->where('is_active', true)
                    ->latest('expires_at')
                    ->first();
                $renewUrl = $activeMembership
                    ? route('subscription-plans.renew', $activeMembership)
                    : route('subscription-plans.public');
            }
        }

        $memberPages = $this->memberPagesFor($user);

        return [
            'date' => $today,
            'progress_score' => $progressScore,
            'weekly_habit_completion' => $habitInsights['weekly_completion'] ?? 0,
            'workout_compliance' => $workoutCompliance,
            'current_program_week' => $currentProgramWeek,
            'next_best_action' => $nextBestAction,
            'progress_overview' => [
                'title' => 'تقدمك الكلي',
                'score' => $progressScore,
                'headline' => $progressHeadline,
                'trend' => $progressTrend,
                'habits_percent' => round((float) ($habitInsights['weekly_completion'] ?? 0), 1),
                'workouts_percent' => round((float) $workoutCompliance, 1),
                'program_week' => (int) $currentProgramWeek,
                'next_best_action' => $nextBestAction,
            ],
            'bookings' => $todayBookings,
            'habits' => $habitsToday,
            'today_workouts' => $todayWorkouts,
            'week_overview' => $weekOverview,
            'week_overview_section' => [
                'title' => 'نظرة الأسبوع',
                'icon_key' => 'calendar',
                'legend' => [
                    ['key' => 'workout', 'label' => 'تمرين', 'color' => 'purple'],
                    ['key' => 'habit', 'label' => 'عادة', 'color' => 'green'],
                    ['key' => 'rest', 'label' => 'راحة', 'color' => 'grey'],
                ],
                'days' => $weekOverview,
            ],
            'gamification' => $gamification,
            'latest_notification' => $latestNotification,
            'latest_message' => $latestMessage,
            'coach_message' => $this->coachMessagePayload($latestMessage),
            'member_pages' => $memberPages,
            'member_pages_section' => [
                'title' => 'محتوى لك',
                'view_all_label' => 'عرض الكل',
                'view_all_url' => route('client.pages.index'),
                'items' => $memberPages,
            ],
            'nutrition_card' => [
                'title' => 'التغذية اليومية',
                'subtitle' => 'التزامك: '.round($nutritionAdherence).'%',
                'adherence' => $nutritionAdherence,
                'adherence_label' => round($nutritionAdherence).'%',
                'action_label' => 'سجّل وجبة',
                'enabled' => true,
                'endpoint' => '/api/v1/nutrition',
            ],
            'challenges_card' => [
                'title' => 'التحديات',
                'subtitle' => 'شارك في التحديات واربح',
                'icon_key' => 'trophy',
                'action_label' => 'عرض التحديات',
                'enabled' => true,
                'endpoint' => '/api/v1/challenges',
            ],
            'community_card' => [
                'title' => 'المجتمع',
                'subtitle' => 'تواصل وتفاعل مع الأعضاء',
                'icon_key' => 'community',
                'action_label' => 'فتح المجتمع',
                'enabled' => true,
                'endpoint' => '/api/v1/community/posts',
            ],
            'check_in_card' => [
                'title' => 'المتابعة',
                'subtitle' => $lastCheckIn
                    ? 'آخر متابعة: '.optional($lastCheckIn->checked_in_at)->locale('ar')->diffForHumans()
                    : 'سجّل بياناتك لمتابعة تقدمك',
                'icon_key' => 'chart_up',
                'action_label' => 'تسجيل المتابعة',
                'enabled' => true,
                'endpoint' => '/api/v1/check-ins',
                'submit_endpoint' => '/api/v1/check-ins',
            ],
            'bookings_card' => [
                'title' => 'الحجوزات',
                'subtitle' => $todayBookings->isNotEmpty()
                    ? 'لديك '.$todayBookings->count().' حجز اليوم'
                    : 'عرض حجوزاتك القادمة والسابقة',
                'icon_key' => 'calendar',
                'action_label' => 'حجوزاتي',
                'enabled' => true,
                'endpoint' => '/api/v1/bookings',
                'create_endpoint' => '/api/v1/bookings/sessions',
            ],
            'unread_notifications_count' => $unreadNotificationsCount,
            'unread_messages_count' => $unreadMessagesCount,
            'communications_summary' => $communicationsSummary,
            'last_check_in' => $lastCheckIn,
            'check_in_url' => route('client.progress.create'),
            'messages_url' => route('client.messages.index'),
            'nutrition_url' => route('client.nutrition.index'),
            'community_url' => route('client.community.index'),
            'challenges_url' => route('client.challenges.index'),
            'pages_url' => route('client.pages.index'),
            'more_url' => route('client.more'),
            'nutrition_adherence' => $nutritionAdherence,
            'membership_days_remaining' => $membershipDaysRemaining,
            'renew_url' => $renewUrl,
        ];
    }

    /**
     * @return array<int, array{id:int,title:string,slug:string,excerpt:?string,url:string}>
     */
    private function memberPagesFor(User $user): array
    {
        try {
            return Page::query()
                ->published()
                ->inMenu()
                ->accessibleBy($user)
                ->orderBy('menu_order')
                ->orderBy('title')
                ->limit(12)
                ->get(['id', 'title', 'slug', 'excerpt', 'access_level', 'required_membership_types', 'audience_gender', 'menu_order'])
                ->filter(fn (Page $page) => $page->canAccess($user))
                ->take(6)
                ->values()
                ->map(fn (Page $page) => [
                    'id' => $page->id,
                    'title' => $page->title,
                    'slug' => $page->slug,
                    'excerpt' => $page->excerpt,
                    'url' => route('client.pages.show', $page->slug),
                ])
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Enrich workout week overview with home UI indicators (workout/habit/rest).
     *
     * @param  array<int, array<string, mixed>>  $weekOverview
     * @return array<int, array<string, mixed>>
     */
    private function enrichWeekOverviewForHome(User $user, array $weekOverview): array
    {
        $shortLabels = ['س', 'ح', 'ن', 'ث', 'ر', 'خ', 'ج'];

        $dates = collect($weekOverview)->pluck('date')->filter()->values();
        $habitDoneByDate = [];
        $habitTotalByDate = [];

        if ($dates->isNotEmpty() && class_exists(\App\Models\HabitLog::class)) {
            $activeHabitIds = Habit::query()
                ->active()
                ->where('client_user_id', $user->id)
                ->pluck('id');

            $habitCount = max(1, $activeHabitIds->count());

            $logs = \App\Models\HabitLog::query()
                ->whereIn('habit_id', $activeHabitIds)
                ->whereBetween('logged_on', [$dates->first(), $dates->last()])
                ->get();

            foreach ($dates as $date) {
                $dayLogs = $logs->filter(fn ($log) => optional($log->logged_on)->toDateString() === $date);
                $habitDoneByDate[$date] = $dayLogs->where('is_completed', true)->count();
                $habitTotalByDate[$date] = $habitCount;
            }
        }

        return collect($weekOverview)->values()->map(function (array $day, int $index) use ($shortLabels, $habitDoneByDate, $habitTotalByDate) {
            $date = $day['date'] ?? null;
            $hasWorkout = (bool) ($day['has_workout'] ?? false);
            $workoutsCount = (int) ($day['workouts_count'] ?? 0);
            $completedWorkouts = (int) ($day['completed_count'] ?? 0);
            $skippedWorkouts = (int) ($day['skipped_count'] ?? 0);
            $isToday = (bool) ($day['is_today'] ?? false);
            $isFuture = $date ? \Carbon\Carbon::parse($date)->startOfDay()->gt(now()->startOfDay()) : false;
            $isPast = $date ? \Carbon\Carbon::parse($date)->startOfDay()->lt(now()->startOfDay()) : false;

            $habitsDone = (int) ($habitDoneByDate[$date] ?? 0);
            $habitsTotal = (int) ($habitTotalByDate[$date] ?? 0);

            $indicatorType = 'rest';
            $indicatorStatus = 'empty';
            $progressPercent = 0;
            $color = 'grey';

            if ($hasWorkout && $workoutsCount > 0) {
                $indicatorType = 'workout';
                $color = 'purple';

                if ($completedWorkouts >= $workoutsCount) {
                    $indicatorStatus = 'completed';
                    $progressPercent = 100;
                } elseif ($completedWorkouts > 0 || ($isToday && $skippedWorkouts < $workoutsCount)) {
                    $indicatorStatus = 'partial';
                    $progressPercent = (int) round(($completedWorkouts / max(1, $workoutsCount)) * 100);
                    if ($isToday && $progressPercent === 0) {
                        $progressPercent = 25; // يظهر حلقة جزئية لليوم الجاري
                    }
                } elseif ($isFuture) {
                    $indicatorStatus = 'empty';
                    $progressPercent = 0;
                } elseif ($skippedWorkouts >= $workoutsCount) {
                    $indicatorType = 'rest';
                    $indicatorStatus = 'empty';
                    $color = 'grey';
                } else {
                    // يوم ماضٍ فيه تمرين غير منجز
                    $indicatorStatus = 'empty';
                    $progressPercent = 0;
                }
            } elseif ($habitsDone > 0) {
                $indicatorType = 'habit';
                $color = 'green';
                if ($habitsTotal > 0 && $habitsDone >= $habitsTotal) {
                    $indicatorStatus = 'completed';
                    $progressPercent = 100;
                } else {
                    $indicatorStatus = 'partial';
                    $progressPercent = (int) round(($habitsDone / max(1, $habitsTotal)) * 100);
                }
            } else {
                $indicatorType = 'rest';
                $indicatorStatus = 'empty';
                $color = 'grey';
                if ($isPast || $isToday) {
                    // راحة / لا نشاط
                    $indicatorStatus = 'empty';
                }
            }

            return array_merge($day, [
                'day_label_short' => $shortLabels[$index] ?? mb_substr((string) ($day['day_label'] ?? ''), 0, 1),
                'indicator_type' => $indicatorType,
                'indicator_status' => $indicatorStatus,
                'progress_percent' => $progressPercent,
                'color_key' => $color,
                'habits_completed_count' => $habitsDone,
                'habits_total_count' => $habitsTotal,
                'ui' => [
                    'show_check' => $indicatorStatus === 'completed',
                    'show_ring' => $indicatorStatus === 'partial',
                    'show_empty' => $indicatorStatus === 'empty',
                ],
            ]);
        })->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function coachMessagePayload(?Message $message): ?array
    {
        if (! $message) {
            return null;
        }

        $sentAt = $message->sent_at ?? $message->created_at;
        $sender = $message->sender;

        return [
            'id' => $message->id,
            'title' => 'رسالة المدرب',
            'body' => $message->body,
            'conversation_id' => $message->conversation_id,
            'sent_at' => optional($sentAt)->toIso8601String(),
            'sent_at_label' => $this->relativeTimeLabel($sentAt),
            'coach' => [
                'id' => $sender?->id,
                'name' => $sender?->name,
                'avatar_url' => $sender?->profile_photo_url,
            ],
            'actions' => [
                'open_thread_url' => url('/api/v1/messages/threads/'.$message->conversation_id),
                'open_label' => 'عرض المحادثة',
            ],
        ];
    }

    private function relativeTimeLabel($sentAt): ?string
    {
        if (! $sentAt) {
            return null;
        }

        $carbon = \Carbon\Carbon::parse($sentAt);
        $minutes = (int) $carbon->diffInMinutes(now());

        if ($minutes < 1) {
            return 'الآن';
        }
        if ($minutes < 60) {
            return "منذ {$minutes} دقيقة";
        }

        $hours = (int) $carbon->diffInHours(now());
        if ($hours < 24) {
            return $hours === 1 ? 'منذ ساعة' : "منذ {$hours} ساعة";
        }

        $days = (int) $carbon->diffInDays(now());
        if ($days === 1) {
            return 'منذ يوم';
        }
        if ($days < 7) {
            return "منذ {$days} أيام";
        }

        return $carbon->format('d/m/Y');
    }
}
