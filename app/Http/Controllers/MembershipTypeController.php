<?php

namespace App\Http\Controllers;

use App\Models\MembershipType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

class MembershipTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index()
    {
        try {
            if (! Schema::hasTable('membership_types')) {
                $membershipTypes = collect([]);

                return view('membership-types.index', compact('membershipTypes'))
                    ->with('error', 'جدول أنواع العضويات غير موجود. يرجى تشغيل المايجريشن أولاً.');
            }

            try {
                $membershipTypes = MembershipType::query()
                    ->withCount(['subscriptionPlans', 'activeUserMemberships'])
                    ->with(['subscriptionPlans' => fn ($query) => $query->ordered()->limit(3)])
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get();
            } catch (QueryException $e) {
                Log::error('Database query error in MembershipTypeController@index', [
                    'error' => $e->getMessage(),
                    'code' => $e->getCode(),
                ]);

                $membershipTypes = collect([]);

                return view('membership-types.index', compact('membershipTypes'))
                    ->with('error', 'خطأ في قاعدة البيانات: '.$e->getMessage());
            }

            return view('membership-types.index', compact('membershipTypes'));
        } catch (\Exception $e) {
            Log::error('General error in MembershipTypeController@index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $membershipTypes = collect([]);

            return view('membership-types.index', compact('membershipTypes'))
                ->with('error', 'حدث خطأ أثناء تحميل أنواع العضويات: '.$e->getMessage());
        }
    }

    public function create()
    {
        return view('membership-types.create');
    }

    public function store(Request $request)
    {
        try {
            $validated = $this->validateAccessPath($request);

            $validated['slug'] = $this->uniqueSlug($validated['name']);
            $validated['is_active'] = $request->boolean('is_active');
            $validated['sort_order'] = $validated['sort_order'] ?? 0;
            // Commercial fields live on subscription plans; keep legacy columns stable.
            $validated['price'] = 0;
            $validated['duration_days'] = 30;
            $validated['features'] = null;

            $membershipType = MembershipType::create($validated);

            return redirect()
                ->route('subscription-plans.create', ['membership_type_id' => $membershipType->id])
                ->with('success', 'تم إنشاء مسار العضوية بنجاح. أضف الآن خطة اشتراك (سعر ومدة) لهذا المسار.');
        } catch (\Exception $e) {
            Log::error('Error creating membership type', ['error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'حدث خطأ أثناء إنشاء نوع العضوية: '.$e->getMessage());
        }
    }

    public function show(MembershipType $membershipType)
    {
        try {
            $membershipType->load([
                'userMemberships.user',
                'activeUserMemberships',
                'subscriptionPlans' => fn ($query) => $query->ordered(),
            ]);

            return view('membership-types.show', compact('membershipType'));
        } catch (\Exception $e) {
            Log::error('Error showing membership type', ['error' => $e->getMessage()]);

            return redirect()->route('membership-types.index')->with('error', 'حدث خطأ أثناء عرض تفاصيل العضوية.');
        }
    }

    public function edit(MembershipType $membershipType)
    {
        try {
            if ($membershipType->is_protected) {
                return redirect()->route('membership-types.index')->with('error', 'لا يمكن تعديل هذا النوع من العضوية لأنه محمي من النظام.');
            }

            $membershipType->load(['subscriptionPlans' => fn ($query) => $query->ordered()]);

            return view('membership-types.edit', compact('membershipType'));
        } catch (\Exception $e) {
            Log::error('Error in edit method', ['error' => $e->getMessage()]);

            return redirect()->route('membership-types.index')->with('error', 'حدث خطأ أثناء تحميل صفحة التعديل.');
        }
    }

    public function update(Request $request, MembershipType $membershipType)
    {
        try {
            if ($membershipType->is_protected) {
                return redirect()->route('membership-types.index')->with('error', 'لا يمكن تعديل هذا النوع من العضوية لأنه محمي من النظام.');
            }

            $validated = $this->validateAccessPath($request);

            if ($validated['name'] !== $membershipType->name) {
                $validated['slug'] = $this->uniqueSlug($validated['name'], $membershipType->id);
            }

            $validated['is_active'] = $request->boolean('is_active');
            $validated['sort_order'] = $validated['sort_order'] ?? 0;

            $membershipType->update($validated);

            return redirect()->route('membership-types.index')->with('success', 'تم تحديث مسار العضوية بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error updating membership type', ['error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'حدث خطأ أثناء تحديث نوع العضوية: '.$e->getMessage());
        }
    }

    public function destroy(MembershipType $membershipType)
    {
        try {
            if ($membershipType->is_protected) {
                return redirect()->route('membership-types.index')->with('error', 'لا يمكن حذف هذا النوع من العضوية لأنه محمي من النظام.');
            }

            if (! $membershipType->canBeDeleted()) {
                return redirect()->route('membership-types.index')->with('error', 'لا يمكن حذف هذا النوع من العضوية لوجود مشتركين به.');
            }

            $membershipType->delete();

            return redirect()->route('membership-types.index')->with('success', 'تم حذف نوع العضوية بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error deleting membership type', ['error' => $e->getMessage()]);

            return redirect()->route('membership-types.index')->with('error', 'حدث خطأ أثناء حذف نوع العضوية: '.$e->getMessage());
        }
    }

    public function toggleStatus(MembershipType $membershipType)
    {
        try {
            if ($membershipType->is_protected) {
                return redirect()->route('membership-types.index')->with('error', 'لا يمكن تعديل حالة هذا النوع من العضوية لأنه محمي من النظام.');
            }

            $membershipType->is_active = ! $membershipType->is_active;
            $membershipType->save();

            $status = $membershipType->is_active ? 'تم تفعيل' : 'تم إلغاء تفعيل';

            return redirect()->route('membership-types.index')->with('success', $status.' نوع العضوية بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error toggling membership type status', ['error' => $e->getMessage()]);

            return redirect()->route('membership-types.index')->with('error', 'حدث خطأ أثناء تغيير حالة العضوية: '.$e->getMessage());
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function validateAccessPath(Request $request): array
    {
        return $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
        ]);
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (
            MembershipType::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
