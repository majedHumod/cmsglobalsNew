{{-- Visible portal card for logged-in trainees on the coach public homepage --}}
@php
    use App\Services\MembershipAccessService;
    use App\Models\Habit;
    use App\Models\SessionBooking;
    use App\Models\WorkoutLog;
    use Illuminate\Support\Facades\Schema;

    $viewer = auth()->user();
    $showTraineePortal = $viewer
        && MembershipAccessService::hasTraineeRole($viewer)
        && ! $viewer->hasAnyRole(['admin', 'coach']);

    $portal = null;
    if ($showTraineePortal) {
        $today = now()->toDateString();
        $weekStart = now()->copy()->startOfWeek(\Carbon\Carbon::SATURDAY)->toDateString();

        $habitsDone = 0;
        $habitsTotal = 0;
        $bookingsToday = 0;
        $unreadMessages = 0;
        $workoutDone = false;

        try {
            if (Schema::hasTable('habits')) {
                $habits = Habit::query()
                    ->active()
                    ->where('client_user_id', $viewer->id)
                    ->with(['logs' => fn ($q) => $q->whereBetween('logged_on', [$weekStart, $today])])
                    ->get();
                $habitsTotal = max(1, $habits->count() * 7);
                $habitsDone = $habits->sum(fn ($habit) => $habit->logs->where('is_completed', true)->count());
            }

            if (Schema::hasTable('session_bookings')) {
                $bookingsToday = SessionBooking::query()
                    ->where('user_id', $viewer->id)
                    ->whereDate('booking_date', $today)
                    ->count();
            }

            if (Schema::hasTable('workout_logs')) {
                $workoutDone = WorkoutLog::query()
                    ->where('user_id', $viewer->id)
                    ->whereDate('scheduled_on', $today)
                    ->where('status', 'completed')
                    ->exists();
            }

            try {
                $unreadMessages = app(\App\Services\MessagingService::class)->unreadCountFor($viewer);
            } catch (\Throwable $e) {
                $unreadMessages = 0;
            }
        } catch (\Throwable $e) {
            // Keep defaults; still show the portal entry.
        }

        $habitsPct = $habitsTotal > 0 ? round(($habitsDone / $habitsTotal) * 100) : 0;
        $score = (int) round(($habitsPct * 0.5) + ($workoutDone ? 50 : 0));

        $nextAction = match (true) {
            ! $workoutDone => 'لديك تمرين أو نشاط يومي بانتظارك — افتح مساحتك الآن',
            $habitsPct < 70 => 'سجّل عادات اليوم لرفع التزامك الأسبوعي',
            $bookingsToday > 0 => 'راجع حجوزات اليوم من مساحتك التدريبية',
            $unreadMessages > 0 => 'لديك رسائل جديدة من المدرب',
            default => 'أنت على المسار — تابع تقدمك من مساحتك',
        };

        $portal = [
            'name' => $viewer->name,
            'score' => $score,
            'next_action' => $nextAction,
            'habits' => $habitsPct,
            'workout_done' => $workoutDone,
            'bookings_today' => $bookingsToday,
            'unread_messages' => $unreadMessages,
            'url' => route('client.home'),
        ];
    }
@endphp

@if($showTraineePortal && $portal)
<section class="relative z-10 px-4 sm:px-6 lg:px-8 py-6 bg-white" dir="rtl" aria-label="مساحة المتدرب">
    <a
        href="{{ $portal['url'] }}"
        class="block max-w-7xl mx-auto rounded-2xl bg-white text-slate-900 border border-slate-200 shadow-sm hover:shadow-md transition-shadow focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand overflow-hidden"
    >
        <div class="p-5 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="min-w-0">
                    <div class="text-xs sm:text-sm text-brand font-medium">مساحتك التدريبية</div>
                    <h2 class="mt-1 text-xl sm:text-2xl font-bold truncate text-slate-900">مرحباً {{ $portal['name'] }}</h2>
                    <p class="mt-2 text-sm sm:text-base text-slate-600 line-clamp-2">{{ $portal['next_action'] }}</p>
                </div>
                <div class="shrink-0 inline-flex items-center justify-center gap-2 rounded-xl bg-brand text-white px-4 py-2.5 text-sm font-semibold">
                    افتح التفاصيل
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                <div class="rounded-xl bg-slate-50 border border-slate-100 px-3 py-3">
                    <div class="text-lg font-bold text-brand">{{ $portal['score'] }}%</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">تقدم اليوم</div>
                </div>
                <div class="rounded-xl bg-slate-50 border border-slate-100 px-3 py-3">
                    <div class="text-lg font-bold text-brand">{{ $portal['habits'] }}%</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">العادات</div>
                </div>
                <div class="rounded-xl bg-slate-50 border border-slate-100 px-3 py-3">
                    <div class="text-lg font-bold text-brand">{{ $portal['workout_done'] ? 'تم' : 'مطلوب' }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">تمرين اليوم</div>
                </div>
                <div class="rounded-xl bg-slate-50 border border-slate-100 px-3 py-3">
                    <div class="text-lg font-bold text-brand">{{ $portal['bookings_today'] }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">حجوزات اليوم</div>
                </div>
            </div>

            @if(($portal['unread_messages'] ?? 0) > 0)
                <div class="mt-3 text-xs sm:text-sm bg-slate-50 border border-slate-100 text-slate-700 rounded-lg px-3 py-2 inline-flex items-center gap-2">
                    لديك {{ $portal['unread_messages'] }} رسالة غير مقروءة من المدرب
                </div>
            @endif
        </div>
    </a>
</section>
@endif
