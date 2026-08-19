<?php

namespace App\Http\Controllers;

use App\Models\MealPlan;
use App\Services\OpenCommercialImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MealPlanController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|coach|user|client']);
    }

    public function index(Request $request)
    {
        $tab = $request->string('tab')->toString();
        if (! in_array($tab, ['all', 'mine', 'stats'], true)) {
            $tab = 'all';
        }

        if ($tab === 'mine' && ! auth()->user()->hasRole('admin')) {
            $tab = 'all';
        }

        $scoped = function () {
            return MealPlan::query()
                ->when(! auth()->user()->hasRole('admin'), function ($query) {
                    return $query->where('user_id', auth()->id());
                });
        };

        $stats = [
            'total' => $scoped()->count(),
            'active' => $scoped()->where('is_active', true)->count(),
            'mine' => MealPlan::query()->where('user_id', auth()->id())->count(),
            'month' => $scoped()->where('created_at', '>=', now()->startOfMonth())->count(),
        ];

        $mealPlansQuery = MealPlan::with('user')
            ->when(! auth()->user()->hasRole('admin'), function ($query) {
                return $query->where('user_id', auth()->id());
            })
            ->when($tab === 'mine', function ($query) {
                return $query->where('user_id', auth()->id());
            })
            ->latest();

        $mealPlans = $tab === 'stats'
            ? $mealPlansQuery->take(6)->get()
            : $mealPlansQuery->paginate(12)->withQueryString();

        return view('meal-plans.index', compact('mealPlans', 'stats', 'tab'));
    }

    public function create()
    {
        return view('meal-plans.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'name_en' => 'nullable|max:255',
            'description' => 'nullable|string',
            'description_en' => 'nullable|string',
            'meal_type' => 'required|in:breakfast,lunch,dinner,snack',
            'calories' => 'nullable|integer|min:0',
            'protein' => 'nullable|integer|min:0',
            'carbs' => 'nullable|integer|min:0',
            'fats' => 'nullable|integer|min:0',
            'ingredients' => 'required|string',
            'ingredients_en' => 'nullable|string',
            'instructions' => 'nullable|string',
            'instructions_en' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'prep_time' => 'nullable|integer|min:0',
            'cook_time' => 'nullable|integer|min:0',
            'servings' => 'required|integer|min:1',
            'difficulty' => 'required|in:easy,medium,hard',
            'is_active' => 'nullable|boolean',
            'nutrition_is_estimated' => 'nullable|boolean',
            'audience_gender' => 'nullable|in:all,male,female',
            'required_membership_types' => 'nullable|array',
            'required_membership_types.*' => 'exists:membership_types,id',
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('meal-plans', 'public');
            $validated['image'] = $imagePath;
        }

        $validated['user_id'] = auth()->id();
        $validated['source'] = MealPlan::SOURCE_CUSTOM;
        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['audience_gender'] = $validated['audience_gender'] ?? 'all';
        $validated['required_membership_types'] = $request->input('required_membership_types', []);
        $validated['nutrition_is_estimated'] = $request->boolean('nutrition_is_estimated');

        MealPlan::create($validated);

        return redirect()->route('meal-plans.index')->with('success', 'تم إنشاء الوجبة بنجاح.');
    }

    /**
     * JSON search for meal library picker (clients + coaches).
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:120',
            'meal_type' => 'nullable|in:breakfast,lunch,dinner,snack',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $plans = MealPlan::query()
            ->where('is_active', true)
            ->visibleTo($request->user())
            ->searchLibrary($validated['q'] ?? null, $validated['meal_type'] ?? null)
            ->orderBy('name')
            ->limit($validated['limit'] ?? 20)
            ->get();

        return response()->json([
            'data' => $plans->map(fn (MealPlan $plan) => [
                'id' => $plan->id,
                'name' => $plan->localized_name,
                'name_ar' => $plan->name,
                'name_en' => $plan->name_en,
                'meal_type' => $plan->meal_type,
                'meal_type_label' => $plan->meal_type_name,
                'calories' => $plan->calories,
                'protein' => $plan->protein,
                'carbs' => $plan->carbs,
                'fats' => $plan->fats,
                'nutrition_is_estimated' => (bool) $plan->nutrition_is_estimated,
                'nutrition_disclaimer' => $plan->nutrition_is_estimated ? $plan->nutrition_disclaimer : null,
                'image_url' => $plan->image_url,
                'source' => $plan->source,
            ]),
        ]);
    }

    public function show(MealPlan $mealPlan)
    {
        if (! $mealPlan->canManage(auth()->user())) {
            abort(403);
        }

        return view('meal-plans.show', compact('mealPlan'));
    }

    public function edit(MealPlan $mealPlan)
    {
        if (! $mealPlan->canManage(auth()->user())) {
            abort(403);
        }

        return view('meal-plans.edit', compact('mealPlan'));
    }

    public function update(Request $request, MealPlan $mealPlan)
    {
        if (! $mealPlan->canManage(auth()->user())) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|max:255',
            'name_en' => 'nullable|max:255',
            'description' => 'nullable|string',
            'description_en' => 'nullable|string',
            'meal_type' => 'required|in:breakfast,lunch,dinner,snack',
            'calories' => 'nullable|integer|min:0',
            'protein' => 'nullable|integer|min:0',
            'carbs' => 'nullable|integer|min:0',
            'fats' => 'nullable|integer|min:0',
            'ingredients' => 'required|string',
            'ingredients_en' => 'nullable|string',
            'instructions' => 'nullable|string',
            'instructions_en' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'prep_time' => 'nullable|integer|min:0',
            'cook_time' => 'nullable|integer|min:0',
            'servings' => 'required|integer|min:1',
            'difficulty' => 'required|in:easy,medium,hard',
            'is_active' => 'nullable|boolean',
            'nutrition_is_estimated' => 'nullable|boolean',
            'audience_gender' => 'nullable|in:all,male,female',
            'required_membership_types' => 'nullable|array',
            'required_membership_types.*' => 'exists:membership_types,id',
        ]);

        if ($request->hasFile('image')) {
            if ($mealPlan->image && ! str_starts_with((string) $mealPlan->image, 'http')) {
                Storage::disk('public')->delete($mealPlan->image);
            }
            $imagePath = $request->file('image')->store('meal-plans/reviewed', 'public');
            $validated['image'] = $imagePath;
            $validated['image_attribution'] = 'Uploaded by '.auth()->user()->name;
            $validated['image_attribution_url'] = null;
        }

        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['audience_gender'] = $validated['audience_gender'] ?? 'all';
        $validated['required_membership_types'] = $request->input('required_membership_types', []);
        $validated['nutrition_is_estimated'] = $request->boolean('nutrition_is_estimated');

        $mealPlan->update($validated);

        return redirect()->route('meal-plans.index')->with('success', 'تم تحديث الوجبة بنجاح.');
    }

    /**
     * Library image review queue for coaches/admins.
     */
    public function libraryReview(Request $request, OpenCommercialImageService $stockImages)
    {
        abort_unless(auth()->user()->hasAnyRole(['admin', 'coach']), 403);

        $mealPlans = MealPlan::query()
            ->where('source', MealPlan::SOURCE_ARABIC_LIBRARY)
            ->when($request->filled('meal_type'), fn ($q) => $q->where('meal_type', $request->string('meal_type')))
            ->when($request->filled('q'), fn ($q) => $q->searchLibrary((string) $request->input('q')))
            ->orderBy('id')
            ->paginate(24)
            ->withQueryString();

        return view('meal-plans.library-review', [
            'mealPlans' => $mealPlans,
            'stockProviders' => $stockImages->availableProviders(),
        ]);
    }

    /**
     * Search commercially-usable open stock images.
     */
    public function searchStockImages(Request $request, OpenCommercialImageService $stockImages)
    {
        abort_unless(auth()->user()->hasAnyRole(['admin', 'coach']), 403);

        $validated = $request->validate([
            'q' => 'required|string|min:2|max:120',
            'provider' => 'nullable|in:openverse,pexels,unsplash',
            'per_page' => 'nullable|integer|min:1|max:20',
        ]);

        $provider = $validated['provider'] ?? 'openverse';
        $available = $stockImages->availableProviders();
        if (empty($available[$provider])) {
            return response()->json([
                'message' => 'هذا المصدر غير مفعّل. أضف مفتاح API في .env أو استخدم Openverse.',
                'data' => [],
                'providers' => $available,
            ], 422);
        }

        $results = $stockImages->search(
            $validated['q'],
            $provider,
            $validated['per_page'] ?? 12
        );

        return response()->json([
            'data' => $results,
            'providers' => $available,
            'provider' => $provider,
        ]);
    }

    /**
     * Apply a selected stock image to a meal (download + store locally).
     */
    public function applyStockImage(Request $request, MealPlan $mealPlan, OpenCommercialImageService $stockImages)
    {
        abort_unless($mealPlan->canReplaceImage(auth()->user()), 403);

        $validated = $request->validate([
            'image_url' => 'required|url|max:2048',
            'attribution' => 'nullable|string|max:255',
            'attribution_url' => 'nullable|url|max:2048',
            'provider' => 'nullable|string|max:40',
        ]);

        $stored = $stockImages->downloadAndStore(
            $validated['image_url'],
            ($mealPlan->external_id ?: 'meal-'.$mealPlan->id),
            $validated['attribution'] ?? ('Open stock image'.($validated['provider'] ? ' ('.$validated['provider'].')' : '')),
            $validated['attribution_url'] ?? null
        );

        if (! $stored) {
            return back()->with('error', 'تعذر تنزيل الصورة من المصدر المفتوح.');
        }

        if ($mealPlan->image && ! str_starts_with((string) $mealPlan->image, 'http')) {
            Storage::disk('public')->delete($mealPlan->image);
        }

        $mealPlan->update([
            'image' => $stored['path'],
            'image_attribution' => $stored['attribution'],
            'image_attribution_url' => $stored['attribution_url'],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'ok',
                'image_url' => $mealPlan->fresh()->image_url,
                'message' => 'تم حفظ الصورة محليًا من المصدر المفتوح.',
            ]);
        }

        return back()->with('success', 'تم اختيار صورة مفتوحة المصدر وحفظها للوجبة: '.$mealPlan->name);
    }

    /**
     * Quick image replace from the review screen.
     */
    public function updateImage(Request $request, MealPlan $mealPlan)
    {
        abort_unless($mealPlan->canReplaceImage(auth()->user()), 403);

        $validated = $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        if ($mealPlan->image && ! str_starts_with((string) $mealPlan->image, 'http')) {
            Storage::disk('public')->delete($mealPlan->image);
        }

        $path = $request->file('image')->store('meal-plans/reviewed', 'public');
        $mealPlan->update([
            'image' => $path,
            'image_attribution' => 'Reviewed by '.auth()->user()->name,
            'image_attribution_url' => null,
        ]);

        return back()->with('success', 'تم استبدال صورة الوجبة: '.$mealPlan->name);
    }

    public function destroy(MealPlan $mealPlan)
    {
        if (! $mealPlan->canManage(auth()->user())) {
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
            ->visibleTo(auth()->user())
            ->with('user')
            ->when(request('meal_type'), function ($q, $type) {
                $q->where('meal_type', $type);
            })
            ->when(request('difficulty'), function ($q, $difficulty) {
                $q->where('difficulty', $difficulty);
            })
            ->when(request('search'), function ($q, $search) {
                $q->searchLibrary($search);
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
        if (!$mealPlan->is_active || !$mealPlan->matchesAudience(auth()->user())) {
            abort(404);
        }

        return view('meal-plans.show-public', compact('mealPlan'));
    }
}