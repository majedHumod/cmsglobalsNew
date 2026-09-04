<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MealPlan;
use App\Models\Message;
use App\Models\ConversationParticipant;
use App\Models\CommunityPost;
use App\Models\UserBadge;
use App\Models\ProgressCheckIn;
use App\Models\SessionBooking;
use App\Models\TrainingSession;
use App\Models\Habit;
use App\Models\HabitLog;
use App\Models\User;
use App\Models\UserMembership;
use App\Services\TenantCache;
use App\Services\WorkoutScheduleService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();

        if ($user->hasAnyRole(['admin', 'coach'])) {
            return redirect()->to(\App\Support\LegacyAdminFilamentMap::PANEL);
        }

        if ($user->hasTraineeRole()) {
            return $this->clientDashboard($user);
        }

        $stats = [
            'users' => $this->safeCount('users', User::class),
            'meal_plans' => $this->safeCount('meal_plans', MealPlan::class),
            'session_bookings' => $this->safeCount('session_bookings', SessionBooking::class),
            'active_memberships' => $this->safeActiveMembershipsCount(),
            'training_sessions' => $this->safeCount('training_sessions', TrainingSession::class),
        ];

        $recentMemberships = collect();
        if (Schema::hasTable('user_memberships')) {
            try {
                $recentMemberships = UserMembership::query()
                    ->with(['user', 'membershipType'])
                    ->latest()
                    ->take(8)
                    ->get();
            } catch (\Throwable) {
            }
        }

        $recentBookings = collect();
        if (Schema::hasTable('session_bookings')) {
            try {
                $recentBookings = SessionBooking::query()
                    ->with(['trainingSession', 'user'])
                    ->latest()
                    ->take(8)
                    ->get();
            } catch (\Throwable) {
            }
        }

        $mode = 'admin';
        $recentCheckIns = collect();
        $clientsNeedingCheckIn = collect();
        $engagementStats = $this->emptyEngagementStats();
        $weeklyActivity = $this->weeklyBookingSeries();

        return view('admin.dashboard', compact(
            'stats',
            'recentMemberships',
            'recentBookings',
            'recentCheckIns',
            'clientsNeedingCheckIn',
            'mode',
            'engagementStats',
            'weeklyActivity'
        ));
    }

    private function coachDashboard(User $user)
    {
        $stats = [
            'users' => User::query()->clients()->where('coach_id', $user->id)->count(),
            'meal_plans' => Schema::hasTable('meal_plans') ? MealPlan::query()->where('user_id', $user->id)->count() : 0,
            'session_bookings' => Schema::hasTable('session_bookings')
                ? SessionBooking::query()->whereHas('trainingSession', fn ($query) => $query->where('user_id', $user->id))->upcoming()->count()
                : 0,
            'active_memberships' => Schema::hasTable('user_memberships')
                ? UserMembership::query()
                    ->whereHas('user', fn ($query) => $query->where('coach_id', $user->id))
                    ->where('is_active', true)
                    ->where('expires_at', '>', now())
                    ->count()
                : 0,
            'training_sessions' => Schema::hasTable('training_sessions')
                ? TrainingSession::query()->where('user_id', $user->id)->count()
                : 0,
        ];

        $recentCheckIns = Schema::hasTable('progress_check_ins')
            ? ProgressCheckIn::query()
                ->with(['user', 'coach'])
                ->where('coach_id', $user->id)
                ->latest('checked_in_at')
                ->take(8)
                ->get()
            : collect();

        $recentBookings = Schema::hasTable('session_bookings')
            ? SessionBooking::query()
                ->with(['trainingSession', 'user'])
                ->whereHas('trainingSession', fn ($query) => $query->where('user_id', $user->id))
                ->latest()
                ->take(8)
                ->get()
            : collect();

        $clientsNeedingCheckIn = User::query()
            ->clients()
            ->where('coach_id', $user->id)
            ->with(['progressCheckIns' => fn ($query) => $query->latest('checked_in_at')->limit(1)])
            ->whereDoesntHave('progressCheckIns', fn ($query) => $query->where('checked_in_at', '>=', now()->subDays(14)))
            ->take(8)
            ->get();

        $engagementStats = $this->coachEngagementStats($user);
        $recentMemberships = collect();
        $mode = 'coach';
        $weeklyActivity = $this->weeklyBookingSeries($user->id);

        return view('admin.dashboard', compact(
            'stats',
            'recentMemberships',
            'recentBookings',
            'recentCheckIns',
            'clientsNeedingCheckIn',
            'mode',
            'engagementStats',
            'weeklyActivity'
        ));
    }

    private function clientDashboard(User $user)
    {
        return redirect()->route('client.home');
    }

    private function safeCount(string $table, string $modelClass): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }
        try {
            return $modelClass::query()->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function safeActiveMembershipsCount(): int
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

    private function coachEngagementStats(User $coach): array
    {
        $cacheKey = TenantCache::key('coach_engagement:' . $coach->id);

        return Cache::remember($cacheKey, now()->addMinutes(3), function () use ($coach) {
            $clientsQuery = User::query()->clients()->where('coach_id', $coach->id);
            $clientIds = $clientsQuery->pluck('id');
            $totalClients = max(1, $clientIds->count());

            $activeClients = ProgressCheckIn::query()
                ->whereIn('user_id', $clientIds)
                ->where('checked_in_at', '>=', now()->subDays(7))
                ->distinct('user_id')
                ->count('user_id');

            $activeClientsRate = round(($activeClients / $totalClients) * 100, 1);

            $habitIds = Habit::query()->whereIn('client_user_id', $clientIds)->pluck('id');
            $habitCompletionRate = 0.0;
            if ($habitIds->isNotEmpty()) {
                $done = HabitLog::query()
                    ->whereIn('habit_id', $habitIds)
                    ->where('logged_on', '>=', now()->subDays(6)->toDateString())
                    ->where('is_completed', true)
                    ->count();
                $habitCompletionRate = round(($done / max(1, $habitIds->count() * 7)) * 100, 1);
            }

            $messageReplyRate = 0.0;
            $unreadMessages = 0;
            $conversationIds = ConversationParticipant::query()
                ->whereIn('user_id', $clientIds)
                ->pluck('conversation_id')
                ->unique();

            if ($conversationIds->isNotEmpty()) {
                $coachMessages = Message::query()
                    ->whereIn('conversation_id', $conversationIds)
                    ->where('sender_user_id', $coach->id)
                    ->where('sent_at', '>=', now()->subDays(7))
                    ->count();

                $clientReplies = Message::query()
                    ->whereIn('conversation_id', $conversationIds)
                    ->whereIn('sender_user_id', $clientIds)
                    ->where('sent_at', '>=', now()->subDays(7))
                    ->count();

                $messageReplyRate = round(($clientReplies / max(1, $coachMessages)) * 100, 1);

                $unreadMessages = Message::query()
                    ->whereIn('conversation_id', $conversationIds)
                    ->whereIn('sender_user_id', $clientIds)
                    ->where('sent_at', '>=', now()->subDays(7))
                    ->count();
            }

            $workoutCompletionRate = app(WorkoutScheduleService::class)->complianceRateForClients($clientIds);

            return [
                'active_clients_rate' => $activeClientsRate,
                'habit_completion_rate' => $habitCompletionRate,
                'workout_completion_rate' => $workoutCompletionRate,
                'clients_low_workout_compliance' => User::query()
                    ->clients()
                    ->where('coach_id', $coach->id)
                    ->get()
                    ->filter(fn (User $client) => app(WorkoutScheduleService::class)->complianceRateForClient($client) < 50)
                    ->count(),
                'checkin_late_count' => $clientsQuery
                    ->whereDoesntHave('progressCheckIns', fn ($query) => $query->where('checked_in_at', '>=', now()->subDays(14)))
                    ->count(),
                'message_reply_rate' => $messageReplyRate,
                'unread_messages' => $unreadMessages,
                'community_posts_week' => CommunityPost::query()
                    ->where('created_at', '>=', now()->subDays(7))
                    ->count(),
                'badges_awarded_week' => UserBadge::query()
                    ->where('awarded_at', '>=', now()->subDays(7))
                    ->count(),
            ];
        });
    }

    private function weeklyBookingSeries(?int $coachId = null): array
    {
        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $count = 0;
            if (Schema::hasTable('session_bookings')) {
                try {
                    $query = SessionBooking::query()->whereDate('created_at', $date->toDateString());
                    if ($coachId) {
                        $query->whereHas('trainingSession', fn ($q) => $q->where('user_id', $coachId));
                    }
                    $count = $query->count();
                } catch (\Throwable) {
                    $count = 0;
                }
            }
            $days[] = [
                'label' => $date->translatedFormat('D'),
                'value' => $count,
            ];
        }

        return $days;
    }

    private function emptyEngagementStats(): array
    {
        return [
            'active_clients_rate' => 0,
            'habit_completion_rate' => 0,
            'workout_completion_rate' => 0,
            'clients_low_workout_compliance' => 0,
            'checkin_late_count' => 0,
            'message_reply_rate' => 0,
            'unread_messages' => 0,
            'community_posts_week' => 0,
            'badges_awarded_week' => 0,
        ];
    }
}
