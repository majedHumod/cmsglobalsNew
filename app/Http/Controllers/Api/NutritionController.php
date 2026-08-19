<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\MealLogResource;
use App\Http\Resources\Api\MealPlanResource;
use App\Models\MealLog;
use App\Services\MealLogService;
use Illuminate\Http\Request;

class NutritionController extends Controller
{
    public function index(Request $request, MealLogService $mealLogService)
    {
        $user = $request->user();
        abort_unless($user->hasAnyRole(['user', 'client']), 403);

        $plans = $mealLogService->availablePlansFor($user);
        $todayLogs = $mealLogService->todayLogsFor($user);
        $weeklyAdherence = $mealLogService->weeklyAdherenceRate($user);
        $slotLabels = MealLog::mealSlotLabels();

        $plansBySlot = [];
        foreach ($slotLabels as $slot => $label) {
            $log = $todayLogs->firstWhere('meal_slot', $slot);
            $plansBySlot[] = [
                'meal_slot' => $slot,
                'meal_slot_label' => $label,
                'plans' => MealPlanResource::collection(
                    $plans->where('meal_type', $slot)->values()
                ),
                'today_log' => $log ? new MealLogResource($log) : null,
                'is_logged' => $log !== null,
            ];
        }

        return response()->json([
            'date' => now()->toDateString(),
            'screen' => [
                'title' => 'التغذية',
                'subtitle' => 'خطة وجباتك ومتابعة الالتزام اليومي',
                'adherence_label' => 'التزامك الغذائي هذا الأسبوع',
                'log_section_title' => 'سجّل وجبة اليوم',
                'today_section_title' => 'وجبات اليوم',
                'plans_section_title' => 'خطة التغذية',
                'library_section_title' => 'اختر من مكتبة الوجبات',
                'save_label' => 'حفظ',
                'nutrition_disclaimer' => config('meal_library.nutrition_disclaimer_ar'),
            ],
            'weekly_adherence' => $weeklyAdherence,
            'weekly_adherence_label' => round($weeklyAdherence).'%',
            'meal_slots' => collect($slotLabels)->map(fn ($label, $key) => [
                'value' => $key,
                'label' => $label,
            ])->values(),
            'meal_plans' => MealPlanResource::collection($plans),
            'plans_by_slot' => $plansBySlot,
            'today_logs' => MealLogResource::collection($todayLogs),
            'actions' => [
                'log_url' => url('/api/v1/nutrition'),
                'search_url' => url('/api/v1/nutrition/search'),
                'home_url' => url('/api/v1/client/home'),
            ],
        ]);
    }

    public function search(Request $request, MealLogService $mealLogService)
    {
        $user = $request->user();
        abort_unless($user->hasAnyRole(['user', 'client']), 403);

        $validated = $request->validate([
            'q' => 'nullable|string|max:120',
            'meal_type' => 'nullable|in:breakfast,lunch,dinner,snack',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $plans = $mealLogService->searchPlansFor(
            $user,
            $validated['q'] ?? null,
            $validated['meal_type'] ?? null,
            $validated['limit'] ?? 20
        );

        return response()->json([
            'data' => MealPlanResource::collection($plans),
            'nutrition_disclaimer' => config('meal_library.nutrition_disclaimer_ar'),
        ]);
    }

    public function store(Request $request, MealLogService $mealLogService)
    {
        $user = $request->user();
        abort_unless($user->hasAnyRole(['user', 'client']), 403);

        $validated = $request->validate([
            'meal_slot' => 'required|in:breakfast,lunch,dinner,snack',
            'meal_plan_id' => 'nullable|exists:meal_plans,id',
            'adherence_score' => 'required|integer|min:0|max:10',
            'notes' => 'nullable|string|max:1000',
            'logged_on' => 'nullable|date',
        ]);

        $log = $mealLogService->logMeal($user, $validated)->load('mealPlan:id,name');

        return response()->json([
            'status' => 'ok',
            'message' => 'تم حفظ الوجبة.',
            'log' => new MealLogResource($log),
            'weekly_adherence' => $mealLogService->weeklyAdherenceRate($user),
            'weekly_adherence_label' => round($mealLogService->weeklyAdherenceRate($user)).'%',
        ], 201);
    }
}
