<?php

namespace App\Http\Controllers;

use App\Models\Habit;
use App\Models\HabitLog;
use App\Models\UserBadge;
use App\Models\User;
use App\Models\WeeklyChallenge;
use App\Events\HabitLogRecorded;
use App\Services\GamificationService;
use App\Services\HabitInsightsService;
use Illuminate\Http\Request;

class HabitController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|coach|user|client']);
    }

    public function index(Request $request)
    {
        $currentUser = $request->user();
        $clientId = (int) $request->query('client_id', 0);
        if ($clientId === 0 && $currentUser->hasAnyRole(['admin', 'coach'])) {
            $clientId = (int) User::query()
                ->clients()
                ->when($currentUser->hasRole('coach'), fn ($query) => $query->where('coach_id', $currentUser->id))
                ->value('id');
        }
        if ($clientId === 0) {
            $clientId = (int) $currentUser->id;
        }
        $client = User::findOrFail($clientId);
        abort_unless($this->canAccessClient($currentUser, $client), 403);

        $habits = Habit::query()
            ->where('client_user_id', $client->id)
            ->with(['logs' => fn ($query) => $query->whereBetween('logged_on', [now()->subDays(6)->toDateString(), now()->toDateString()])])
            ->orderBy('name')
            ->get();

        $insights = app(HabitInsightsService::class)->summarize($habits);
        $gamification = app(GamificationService::class)->leaderboard($client);
        $badges = UserBadge::query()->with('badge')->where('user_id', $client->id)->latest('awarded_at')->limit(6)->get();
        $activeChallenge = WeeklyChallenge::query()->current()->with(['participants' => fn ($query) => $query->where('user_id', $client->id)])->first();
        $challengeProgress = optional(optional(optional($activeChallenge)->participants)->first())->progress_value ?? 0;

        return view('habits.index', [
            'habits' => $habits,
            'client' => $client,
            'weeklyCompletion' => $this->weeklyCompletion($habits),
            'insights' => $insights,
            'gamification' => $gamification,
            'badges' => $badges,
            'activeChallenge' => $activeChallenge,
            'challengeProgress' => $challengeProgress,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:120',
            'unit' => 'nullable|string|max:50',
            'target_value' => 'nullable|integer|min:1|max:100000',
        ]);

        $currentUser = $request->user();
        $client = User::findOrFail((int) $validated['client_user_id']);
        abort_unless($currentUser->hasAnyRole(['admin', 'coach']) && $this->canAccessClient($currentUser, $client), 403);

        Habit::create([
            'client_user_id' => $client->id,
            'created_by_user_id' => $currentUser->id,
            'name' => $validated['name'],
            'unit' => $validated['unit'] ?? null,
            'target_value' => $validated['target_value'] ?? 1,
            'is_active' => true,
        ]);

        return back()->with('success', 'تمت إضافة العادة بنجاح.');
    }

    public function toggle(Request $request, Habit $habit)
    {
        abort_unless($this->canAccessClient($request->user(), $habit->client), 403);
        $habit->update(['is_active' => ! $habit->is_active]);

        return back()->with('success', 'تم تحديث حالة العادة.');
    }

    public function log(Request $request, Habit $habit)
    {
        $validated = $request->validate([
            'logged_on' => 'nullable|date',
            'value' => 'nullable|integer|min:0|max:100000',
            'is_completed' => 'nullable|boolean',
        ]);

        $currentUser = $request->user();
        abort_unless($this->canAccessClient($currentUser, $habit->client), 403);

        if ($currentUser->hasAnyRole(['user', 'client'])) {
            abort_unless((int) $habit->client_user_id === (int) $currentUser->id, 403);
        }

        $loggedOn = $validated['logged_on'] ?? now()->toDateString();
        $value = $validated['value'] ?? $habit->target_value;
        $completed = (bool) ($validated['is_completed'] ?? ($value >= $habit->target_value));

        HabitLog::updateOrCreate(
            ['habit_id' => $habit->id, 'logged_on' => $loggedOn],
            [
                'user_id' => $currentUser->id,
                'value' => $value,
                'is_completed' => $completed,
            ]
        );

        event(new HabitLogRecorded($habit, $currentUser));

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

    private function canAccessClient(User $currentUser, User $client): bool
    {
        if ($currentUser->hasRole('admin')) {
            return true;
        }

        if ((int) $currentUser->id === (int) $client->id) {
            return true;
        }

        return $currentUser->isCoachOf($client);
    }
}
