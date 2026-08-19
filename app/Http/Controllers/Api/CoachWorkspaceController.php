<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CoachWorkspaceResource;
use App\Models\CoachAvailability;
use App\Models\ProgressCheckIn;
use App\Models\SessionBooking;
use App\Models\User;
use App\Services\CoachRiskService;
use App\Services\WorkoutScheduleService;
use Illuminate\Http\Request;

class CoachWorkspaceController extends Controller
{
    public function index(Request $request, CoachRiskService $coachRiskService, WorkoutScheduleService $scheduleService)
    {
        $user = $request->user();
        abort_unless($user && $user->hasAnyRole(['admin', 'coach']), 403);

        $filter = $request->query('filter');
        $clientsQuery = $coachRiskService->clientsQuery($user, $filter)->orderBy('name');
        $clientIds = (clone $clientsQuery)->pluck('id');
        $clients = $clientsQuery->paginate(10);

        $clients->getCollection()->transform(function (User $client) use ($scheduleService, $coachRiskService) {
            $assessment = $coachRiskService->assessClient($client);
            $client->setAttribute('workout_completion_rate', $assessment['workout_completion_rate']);
            $client->setAttribute('risk_score', $assessment['risk_score']);
            $client->setAttribute('risk_reasons', $assessment['risk_reasons']);
            $client->setAttribute('priority', $assessment['priority']);

            return $client;
        });

        $summary = $coachRiskService->summaryFor($user);
        $summary['upcomingBookings'] = SessionBooking::query()
            ->whereHas('trainingSession', function ($query) use ($user) {
                if ($user->hasRole('coach')) {
                    $query->where('user_id', $user->id);
                }
            })
            ->upcoming()
            ->count();

        return new CoachWorkspaceResource([
            'summary' => $summary,
            'at_risk_clients' => $coachRiskService->atRiskClients($user, 20, $filter),
            'clients' => $clients,
            'availabilities' => CoachAvailability::query()
                ->when($user->hasRole('coach'), fn ($query) => $query->where('user_id', $user->id))
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get(),
        ]);
    }

    public function client(Request $request, User $user, WorkoutScheduleService $scheduleService)
    {
        abort_unless($user->hasTraineeRole(), 404);
        $currentUser = $request->user();
        abort_unless(
            $currentUser->hasRole('admin') || $currentUser->id === $user->id || $currentUser->isCoachOf($user),
            403
        );

        return response()->json([
            'client' => $user->load('coach', 'clientProfile'),
            'checkIns' => ProgressCheckIn::query()
                ->where('user_id', $user->id)
                ->with('coach', 'submittedBy')
                ->latest('checked_in_at')
                ->limit(20)
                ->get(),
            'upcomingBookings' => SessionBooking::query()
                ->where('user_id', $user->id)
                ->with('trainingSession')
                ->upcoming()
                ->latest('booking_date')
                ->get(),
            'workout_completion_rate' => $scheduleService->complianceRateForClient($user),
            'week_overview' => $scheduleService->weekOverviewFor($user),
        ]);
    }
}
