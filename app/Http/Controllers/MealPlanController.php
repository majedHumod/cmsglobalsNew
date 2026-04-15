<?php

namespace App\Http\Controllers;

use App\Models\MealPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MealPlanController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|user']);
    }

    public function index()
    {
        $mealPlans = MealPlan::with('user')
            ->when(!auth()->user()->hasRole('admin'), function ($query) {
                return $query->where('user_id', auth()->id());
            })
            ->latest()
            ->get();

        return view('meal-plans.index', compact('mealPlans'));
    }

    public function create()
    {
        return view('meal-plans.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable|string',
            'meal_type' => 'required|in:breakfast,lunch,dinner,snack',
            'calories' => 'nullable|integer|min:0',
            'protein' => 'nullable|integer|min:0',
            'carbs' => 'nullable|integer|min:0',
            'fats' => 'nullable|integer|min:0',
            'ingredients' => 'required|string',
            'instructions' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'prep_time' => 'nullable|integer|min:0',
            'cook_time' => 'nullable|integer|min:0',
            'servings' => 'required|integer|min:1',
            'difficulty' => 'required|in:easy,medium,hard',
            'is_active' => 'nullable|boolean'
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('meal-plans', 'public');
            $validated['image'] = $imagePath;
        }

        $validated['user_id'] = auth()->id();
        $validated['is_active'] = $request->has('is_active') ? true : false;

        MealPlan::create($validated);

        return redirect()->route('meal-plans.index')->with('success', 'تم إنشاء الوجبة بنجاح.');
    }

    public function show(MealPlan $mealPlan)
    {
        // التحقق من الصلاحيات
        if (!auth()->user()->hasRole('admin') && $mealPlan->user_id !== auth()->id()) {
            abort(403);
        }

        return view('meal-plans.show', compact('mealPlan'));
    }

    public function edit(MealPlan $mealPlan)
    {
        // التحقق من الصلاحيات
        if (!auth()->user()->hasRole('admin') && $mealPlan->user_id !== auth()->id()) {
            abort(403);
        }

        return view('meal-plans.edit', compact('mealPlan'));
    }

    public function update(Request $request, MealPlan $mealPlan)
    {
        // التحقق من الصلاحيات
        if (!auth()->user()->hasRole('admin') && $mealPlan->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable|string',
            'meal_type' => 'required|in:breakfast,lunch,dinner,snack',
            'calories' => 'nullable|integer|min:0',
            'protein' => 'nullable|integer|min:0',
            'carbs' => 'nullable|integer|min:0',
            'fats' => 'nullable|integer|min:0',
            'ingredients' => 'required|string',
            'instructions' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'prep_time' => 'nullable|integer|min:0',
            'cook_time' => 'nullable|integer|min:0',
            'servings' => 'required|integer|min:1',
            'difficulty' => 'required|in:easy,medium,hard',
            'is_active' => 'nullable|boolean'
        ]);

        if ($request->hasFile('image')) {
            // حذف الصورة القديمة
            if ($mealPlan->image) {
                Storage::disk('public')->delete($mealPlan->image);
            }
            $imagePath = $request->file('image')->store('meal-plans', 'public');
            $validated['image'] = $imagePath;
        }

        $validated['is_active'] = $request->has('is_active') ? true : false;

        $mealPlan->update($validated);

        return redirect()->route('meal-plans.index')->with('success', 'تم تحديث الوجبة بنجاح.');
    }

    public function destroy(MealPlan $mealPlan)
    {
        // التحقق من الصلاحيات
        if (!auth()->user()->hasRole('admin') && $mealPlan->user_id !== auth()->id()) {
            abort(403);
        }

        if ($mealPlan->image) {
            Storage::disk('public')->delete($mealPlan->image);
        }

        $mealPlan->delete();

        return redirect()->route('meal-plans.index')->with('success', 'تم حذف الوجبة بنجاح.');
    }

    public function publicIndex()
    {
        $mealPlans = MealPlan::query()
            ->where('is_active', true)
            ->with('user')
            ->when(request('meal_type'), function ($q, $type) {
                $q->where('meal_type', $type);
            })
            ->when(request('difficulty'), function ($q, $difficulty) {
                $q->where('difficulty', $difficulty);
            })
            ->when(request('search'), function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('ingredients', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        return view('meal-plans.public', compact('mealPlans'));
    }

    /**
     * Display the specified meal plan for public viewing.
     */
    public function showPublic(MealPlan $mealPlan)
    {
        // Check if meal plan is active
        if (!$mealPlan->is_active) {
            abort(404);
        }

        return view('meal-plans.show-public', compact('mealPlan'));
    }
}