<?php

namespace App\Http\Controllers;

use App\Models\ClientMealPlan;
use App\Models\MealPlan;
use App\Models\SessionBooking;
use App\Models\User;
use App\Services\CoachRiskService;
use App\Services\NotificationFeedService;
use App\Services\MessagingService;
use App\Services\WorkoutScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class CoachClientController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|coach']);
    }

    public function index(Request $request, CoachRiskService $coachRiskService)
    {
        $currentUser = auth()->user();
        $filter = $request->query('filter');
        $coachId = ($request->filled('coach_id') && $currentUser->hasRole('admin'))
            ? $request->integer('coach_id')
            : null;

        $clientsQuery = $coachRiskService->clientsQuery($currentUser, $filter, $coachId)
            ->with(['coach', 'clientProfile', 'progressCheckIns' => fn ($query) => $query->latest('checked_in_at')->limit(1)]);

        if ($request->filled('search')) {
            $search = (string) $request->input('search');
            $clientsQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $clients = $clientsQuery->orderBy('name')->paginate(12)->withQueryString();
        $coaches = User::query()->coaches()->orderBy('name')->get();
        $summary = $coachRiskService->summaryFor($currentUser, $coachId);

        return view('coach.clients.index', [
            'clients' => $clients,
            'coaches' => $coaches,
            'summary' => $summary,
            'filter' => $filter,
        ]);
    }

    public function show(User $user, WorkoutScheduleService $scheduleService, CoachRiskService $coachRiskService)
    {
        abort_unless($user->hasTraineeRole(), 404);
        $this->ensureCanAccessClient($user);

        $relations = [
            'coach',
            'clientProfile',
            'progressCheckIns' => fn ($query) => $query->latest('checked_in_at')->limit(10),
        ];

        if (Schema::hasTable('client_meal_plans')) {
            $relations[] = 'clientMealAssignments.mealPlan';
        }

        $user->load($relations);

        $upcomingBookings = SessionBooking::query()
            ->with('trainingSession')
            ->where('user_id', $user->id)
            ->upcoming()
            ->latest('booking_date')
            ->limit(5)
            ->get();

        $libraryMeals = MealPlan::query()
            ->where('is_active', true)
            ->orderBy('meal_type')
            ->orderBy('name')
            ->limit(500)
            ->get(['id', 'name', 'name_en', 'meal_type', 'calories', 'source']);

        $mealAssignments = Schema::hasTable('client_meal_plans')
            ? $user->clientMealAssignments()->with('mealPlan')->orderBy('meal_slot')->orderBy('sort_order')->get()
            : collect();

        return view('coach.clients.show', [
            'user' => $user,
            'upcomingBookings' => $upcomingBookings,
            'workoutCompletionRate' => $scheduleService->complianceRateForClient($user),
            'weekOverview' => $scheduleService->weekOverviewFor($user),
            'riskAssessment' => $coachRiskService->assessClient($user),
            'mealAssignments' => $mealAssignments,
            'libraryMeals' => $libraryMeals,
            'mealSlots' => [
                '' => 'أي وقت',
                'breakfast' => 'إفطار',
                'lunch' => 'غداء',
                'dinner' => 'عشاء',
                'snack' => 'سناك',
            ],
        ]);
    }

    public function remind(User $user, NotificationFeedService $notificationFeedService, MessagingService $messagingService)
    {
        abort_unless($user->hasTraineeRole(), 404);
        $this->ensureCanAccessClient($user);

        $coach = auth()->user();
        $messageBody = 'مدربك يطلب منك متابعة برنامجك اليومي وإرسال تحديث.';

        if ($user->coach_id) {
            $conversation = $messagingService->findOrCreateDirectConversation($coach, $user, 'تذكير من المدرب');
            $messagingService->sendMessage($conversation, $coach, $messageBody);
        }

        $notificationFeedService->pushToUser(
            $user->id,
            'coach.reminder',
            'تذكير من المدرب',
            $messageBody,
            [
                'coach_id' => $coach->id,
                'messages_url' => route('client.messages.index'),
            ]
        );

        return back()->with('success', 'تم إرسال التذكير والرسالة للعميل.');
    }

    public function updateAssignment(Request $request, User $user)
    {
        abort_unless($user->hasTraineeRole(), 404);

        $validated = $request->validate([
            'coach_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $currentUser = auth()->user();
        if ($currentUser->hasRole('coach')) {
            $validated['coach_id'] = $currentUser->id;
        }

        if ($validated['coach_id']) {
            $coach = User::query()->coaches()->findOrFail($validated['coach_id']);
            $validated['coach_id'] = $coach->id;
        }

        $user->update(['coach_id' => $validated['coach_id'] ?? null]);

        return back()->with('success', 'تم تحديث المدرب المسؤول عن العميل بنجاح.');
    }

    public function assignMeals(Request $request, User $user)
    {
        abort_unless($user->hasTraineeRole(), 404);
        $this->ensureCanAccessClient($user);

        $validated = $request->validate([
            'meal_plan_ids' => ['required', 'array', 'min:1'],
            'meal_plan_ids.*' => ['integer', 'exists:meal_plans,id'],
            'meal_slot' => ['nullable', 'in:breakfast,lunch,dinner,snack'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $slot = $validated['meal_slot'] ?? null;
        $assigned = 0;

        foreach ($validated['meal_plan_ids'] as $mealPlanId) {
            ClientMealPlan::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'meal_plan_id' => (int) $mealPlanId,
                    'meal_slot' => $slot,
                ],
                [
                    'assigned_by' => auth()->id(),
                    'notes' => $validated['notes'] ?? null,
                    'is_active' => true,
                ]
            );
            $assigned++;
        }

        return back()->with('success', "تم تعيين {$assigned} وجبة ضمن نظام العميل الغذائي.");
    }

    public function unassignMeal(User $user, ClientMealPlan $assignment)
    {
        abort_unless($user->hasTraineeRole(), 404);
        $this->ensureCanAccessClient($user);
        abort_unless((int) $assignment->user_id === (int) $user->id, 404);

        $assignment->delete();

        return back()->with('success', 'تم إزالة الوجبة من نظام العميل.');
    }

    private function ensureCanAccessClient(User $client): void
    {
        $currentUser = auth()->user();

        if ($currentUser->hasRole('admin')) {
            return;
        }

        abort_unless($currentUser->isCoachOf($client), 403);
    }
}
