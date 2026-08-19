<?php

namespace App\Http\Controllers;

use App\Models\MembershipType;
use App\Models\SupplementPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SupplementPlanController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|coach'])->except(['publicIndex']);
        $this->middleware('auth')->only(['publicIndex']);
    }

    public function index(Request $request)
    {
        try {
            $query = SupplementPlan::with('user');

            if (auth()->user()->hasRole('coach')) {
                $query->where('user_id', auth()->id());
            }

            if ($request->filled('type')) {
                $query->where('supplement_type', $request->type);
            }

            if ($request->filled('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('brand', 'like', '%' . $request->search . '%');
                });
            }

            $supplementPlans = $query->ordered()->paginate(15);
            $membershipTypes = MembershipType::active()->ordered()->get();

            return view('supplement-plans.index', compact('supplementPlans', 'membershipTypes'));
        } catch (\Exception $e) {
            Log::error('SupplementPlanController@index', ['error' => $e->getMessage()]);
            return back()->with('error', 'حدث خطأ أثناء تحميل خطط المكملات.');
        }
    }

    public function create()
    {
        $membershipTypes = MembershipType::active()->ordered()->get();
        return view('supplement-plans.create', compact('membershipTypes'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'                     => 'required|string|max:255',
                'description'              => 'nullable|string',
                'supplement_type'          => 'required|in:protein,vitamins,minerals,pre_workout,post_workout,omega,general',
                'dosage'                   => 'nullable|string|max:100',
                'timing'                   => 'required|in:morning,pre_workout,post_workout,night,with_meal',
                'brand'                    => 'nullable|string|max:150',
                'image'                    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                'instructions'             => 'nullable|string',
                'warnings'                 => 'nullable|string',
                'is_active'                => 'boolean',
                'audience_gender'          => 'nullable|in:all,male,female',
                'required_membership_types'=> 'nullable|array',
                'required_membership_types.*' => 'exists:membership_types,id',
                'sort_order'               => 'nullable|integer|min:0',
            ]);

            $validated['user_id']        = auth()->id();
            $validated['is_active']      = $request->boolean('is_active', true);
            $validated['audience_gender'] = $validated['audience_gender'] ?? 'all';
            $validated['required_membership_types'] = $request->input('required_membership_types', []);

            if ($request->hasFile('image')) {
                $validated['image'] = $request->file('image')->store('supplement-plans', 'public');
            }

            SupplementPlan::create($validated);

            return redirect()->route('supplement-plans.index')->with('success', 'تم إنشاء خطة المكمل بنجاح.');
        } catch (\Exception $e) {
            Log::error('SupplementPlanController@store', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'حدث خطأ أثناء إنشاء خطة المكمل: ' . $e->getMessage());
        }
    }

    public function show(SupplementPlan $supplementPlan)
    {
        if (! auth()->user()->hasAnyRole(['admin', 'coach']) &&
            (! $supplementPlan->is_active || ! $supplementPlan->matchesAudience(auth()->user()))) {
            abort(403, 'هذا المكمل غير متاح.');
        }

        return view('supplement-plans.show', compact('supplementPlan'));
    }

    public function edit(SupplementPlan $supplementPlan)
    {
        if (! $supplementPlan->canManage(auth()->user())) {
            abort(403, 'ليس لديك صلاحية لتعديل هذا المكمل.');
        }

        $membershipTypes = MembershipType::active()->ordered()->get();
        return view('supplement-plans.edit', compact('supplementPlan', 'membershipTypes'));
    }

    public function update(Request $request, SupplementPlan $supplementPlan)
    {
        try {
            if (! $supplementPlan->canManage(auth()->user())) {
                abort(403, 'ليس لديك صلاحية لتعديل هذا المكمل.');
            }

            $validated = $request->validate([
                'name'                     => 'required|string|max:255',
                'description'              => 'nullable|string',
                'supplement_type'          => 'required|in:protein,vitamins,minerals,pre_workout,post_workout,omega,general',
                'dosage'                   => 'nullable|string|max:100',
                'timing'                   => 'required|in:morning,pre_workout,post_workout,night,with_meal',
                'brand'                    => 'nullable|string|max:150',
                'image'                    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                'instructions'             => 'nullable|string',
                'warnings'                 => 'nullable|string',
                'is_active'                => 'boolean',
                'audience_gender'          => 'nullable|in:all,male,female',
                'required_membership_types'=> 'nullable|array',
                'required_membership_types.*' => 'exists:membership_types,id',
                'sort_order'               => 'nullable|integer|min:0',
            ]);

            $validated['is_active']      = $request->boolean('is_active', true);
            $validated['audience_gender'] = $validated['audience_gender'] ?? 'all';
            $validated['required_membership_types'] = $request->input('required_membership_types', []);

            if ($request->hasFile('image')) {
                if ($supplementPlan->image) {
                    Storage::disk('public')->delete($supplementPlan->image);
                }
                $validated['image'] = $request->file('image')->store('supplement-plans', 'public');
            }

            $supplementPlan->update($validated);

            return redirect()->route('supplement-plans.index')->with('success', 'تم تحديث خطة المكمل بنجاح.');
        } catch (\Exception $e) {
            Log::error('SupplementPlanController@update', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'حدث خطأ أثناء تحديث خطة المكمل: ' . $e->getMessage());
        }
    }

    public function destroy(SupplementPlan $supplementPlan)
    {
        try {
            if (! $supplementPlan->canManage(auth()->user())) {
                abort(403, 'ليس لديك صلاحية لحذف هذا المكمل.');
            }

            if ($supplementPlan->image) {
                Storage::disk('public')->delete($supplementPlan->image);
            }

            $supplementPlan->delete();

            return redirect()->route('supplement-plans.index')->with('success', 'تم حذف خطة المكمل بنجاح.');
        } catch (\Exception $e) {
            Log::error('SupplementPlanController@destroy', ['error' => $e->getMessage()]);
            return back()->with('error', 'حدث خطأ أثناء حذف خطة المكمل.');
        }
    }

    public function publicIndex(Request $request)
    {
        try {
            $supplementPlans = SupplementPlan::active()
                ->visibleTo(auth()->user())
                ->ordered()
                ->get()
                ->groupBy('supplement_type');

            return view('supplement-plans.public', compact('supplementPlans'));
        } catch (\Exception $e) {
            Log::error('SupplementPlanController@publicIndex', ['error' => $e->getMessage()]);
            return back()->with('error', 'حدث خطأ أثناء تحميل خطط المكملات.');
        }
    }
}
