<?php

namespace App\Http\Controllers;

use App\Models\ChallengeParticipant;
use App\Models\UserBadge;
use App\Models\WeeklyChallenge;
use App\Services\GamificationService;
use Illuminate\Http\Request;

class ClientChallengeWebController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'trainee']);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $activeChallenge = WeeklyChallenge::query()->current()->first();
        $participant = null;

        if ($activeChallenge) {
            $participant = ChallengeParticipant::query()
                ->where('challenge_id', $activeChallenge->id)
                ->where('user_id', $user->id)
                ->first();
        }

        $gamification = app(GamificationService::class)->leaderboard($user);
        $badges = UserBadge::query()
            ->with('badge')
            ->where('user_id', $user->id)
            ->latest('awarded_at')
            ->get();

        return view('client.challenges.index', [
            'activeChallenge' => $activeChallenge,
            'participant' => $participant,
            'gamification' => $gamification,
            'badges' => $badges,
        ]);
    }
}
