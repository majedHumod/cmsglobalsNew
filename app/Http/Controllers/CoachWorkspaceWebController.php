<?php

namespace App\Http\Controllers;

use App\Models\CoachAvailability;
use App\Models\SessionBooking;
use App\Services\CoachRiskService;
use Illuminate\Http\Request;

class CoachWorkspaceWebController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|coach']);
    }

    public function __invoke(Request $request, CoachRiskService $coachRiskService)
    {
        $user = $request->user();
        $filter = $request->query('filter');

        $summary = $coachRiskService->summaryFor($user);
        $summary['upcomingBookings'] = SessionBooking::query()
            ->whereHas('trainingSession', function ($query) use ($user) {
                if ($user->hasRole('coach')) {
                    $query->where('user_id', $user->id);
                }
            })
            ->upcoming()
            ->count();

        return view('coach.workspace.index', [
            'summary' => $summary,
            'atRiskClients' => $coachRiskService->atRiskClients($user, 20, $filter),
            'filter' => $filter,
            'availabilities' => CoachAvailability::query()
                ->when($user->hasRole('coach'), fn ($query) => $query->where('user_id', $user->id))
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get(),
        ]);
    }
}
