<?php

namespace App\Http\Controllers;

use App\Events\HabitLogRecorded;
use App\Models\Habit;
use App\Models\HabitLog;
use App\Models\UserBadge;
use App\Models\WeeklyChallenge;
use App\Services\GamificationService;
use App\Services\HabitInsightsService;
use Illuminate\Http\Request;

class ClientHabitWebController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'trainee']);
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $habits = Habit::query()
            ->active()
            ->where('client_user_id', $user->id)
            ->with(['logs' => fn ($query) => $query->whereBetween('logged_on', [now()->subDays(6)->toDateString(), now()->toDateString()])])
            ->orderBy('name')
            ->get();

        $insights = app(HabitInsightsService::class)->summarize($habits);
        $gamification = app(GamificationService::class)->leaderboard($user);
        $badges = UserBadge::query()->with('badge')->where('user_id', $user->id)->latest('awarded_at')->limit(6)->get();
        $activeChallenge = WeeklyChallenge::query()->current()->with(['participants' => fn ($query) => $query->where('user_id', $user->id)])->first();
        $challengeProgress = optional(optional(optional($activeChallenge)->participants)->first())->progress_value ?? 0;

        return view('client.habits.index', [
            'habits' => $habits,
            'weeklyCompletion' => $this->weeklyCompletion($habits),
            'insights' => $insights,
            'gamification' => $gamification,
            'badges' => $badges,
            'activeChallenge' => $activeChallenge,
            'challengeProgress' => $challengeProgress,
        ]);
    }

    public function log(Request $request, Habit $habit)
    {
        abort_unless((int) $habit->client_user_id === (int) $request->user()->id, 403);

        $validated = $request->validate([
            'logged_on' => 'nullable|date',
            'value' => 'nullable|integer|min:0|max:100000',
            'is_completed' => 'nullable|boolean',
        ]);

        $loggedOn = $validated['logged_on'] ?? now()->toDateString();
        $value = (int) ($validated['value'] ?? $habit->target_value);
        $completed = array_key_exists('is_completed', $validated)
            ? (bool) $validated['is_completed']
            : ($value >= $habit->target_value);

        $log = HabitLog::updateOrCreate(
            ['habit_id' => $habit->id, 'logged_on' => $loggedOn],
            [
                'user_id' => $request->user()->id,
                'value' => $value,
                'is_completed' => $completed,
            ]
        );

        event(new HabitLogRecorded($habit, $request->user()));

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'ok',
                'message' => $completed ? 'تم تسجيل العادة.' : 'تم حفظ القيمة.',
                'log' => $log,
            ]);
        }

        return back()->with('success', 'تم تسجيل العادة بنجاح.');
    }

    private function weeklyCompletion($habits): float
    {
        $total = 0;
        $done = 0;

        foreach ($habits as $habit) {
            $logs = $habit->logs ?? collect();
            $total += 7;
            $done += $logs->where('is_completed', true)->count();
        }

        if ($total === 0) {
            return 0.0;
        }

        return round(($done / $total) * 100, 1);
    }
}
