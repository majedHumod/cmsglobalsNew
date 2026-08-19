<?php

namespace App\Http\Controllers;

use App\Services\MealLogService;
use Illuminate\Http\Request;

class ClientNutritionWebController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'trainee']);
    }

    public function index(Request $request, MealLogService $mealLogService)
    {
        $user = $request->user();

        return view('client.nutrition.index', [
            'mealPlans' => $mealLogService->availablePlansFor($user),
            'todayLogs' => $mealLogService->todayLogsFor($user),
            'weeklyAdherence' => $mealLogService->weeklyAdherenceRate($user),
            'mealSlots' => \App\Models\MealLog::mealSlotLabels(),
        ]);
    }

    public function store(Request $request, MealLogService $mealLogService)
    {
        $validated = $request->validate([
            'meal_slot' => 'required|in:breakfast,lunch,dinner,snack',
            'meal_plan_id' => 'nullable|exists:meal_plans,id',
            'adherence_score' => 'required|integer|min:0|max:10',
            'notes' => 'nullable|string|max:1000',
            'logged_on' => 'nullable|date',
        ]);

        $mealLogService->logMeal($request->user(), $validated);

        return back()->with('success', 'تم تسجيل الوجبة بنجاح.');
    }
}
