<?php

namespace App\Http\Controllers;

use App\Models\Workout;
use App\Models\WorkoutSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WorkoutScheduleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|coach|user|client']);
    }

    /**
     * عرض الجدول الأسبوعي للتمارين
     */
    public function index(Request $request)
    {
        try {
            $query = WorkoutSchedule::with(['workout', 'user']);

            // تطبيق الصلاحيات
            if (!auth()->user()->hasRole('admin')) {
                if (auth()->user()->hasRole('coach')) {
                    // المدرب يرى جدولته فقط
                    $query->where('user_id', auth()->id());
                } elseif (auth()->user()->hasTraineeRole()) {
                    // المتدرب يرى الجدولة النشطة المطابقة لمساره الحالي
                    $query->where('status', true)->visibleTo(auth()->user());
                }
            }

            // فلترة حسب الأسبوع
            if ($request->filled('week')) {
                $query->where('week_number', $request->week);
            }

            // فلترة حسب التمرين
            if ($request->filled('workout_id')) {
                $query->where('workout_id', $request->workout_id);
            }

            $schedules = $query->orderBy('week_number')
                              ->orderBy('session_number')
                              ->paginate(20);

            // جلب التمارين للفلترة
            $workouts = Workout::active()->get();

            return view('workout-schedules.index', compact('schedules', 'workouts'));
        } catch (\Exception $e) {
            Log::error('Error in WorkoutScheduleController@index', ['error' => $e->getMessage()]);
            return back()->with('error', 'حدث خطأ أثناء تحميل جدول التمارين: ' . $e->getMessage());
        }
    }

    /**
     * عرض نموذج إنشاء جدولة جديدة
     */
    public function create()
    {
        // التحقق من الصلاحيات
        if (!auth()->user()->hasAnyRole(['admin', 'coach'])) {
            abort(403, 'ليس لديك صلاحية لإنشاء جدولة التمارين.');
        }

        $workouts = Workout::active()->get();
        return view('workout-schedules.create', compact('workouts'));
    }

    /**
     * حفظ جدولة جديدة
     */
    public function store(Request $request)
    {
        try {
            // التحقق من الصلاحيات
            if (!auth()->user()->hasAnyRole(['admin', 'coach'])) {
                abort(403, 'ليس لديك صلاحية لإنشاء جدولة التمارين.');
            }

            $validated = $request->validate([
                'workout_id' => 'required|exists:workouts,id',
                'week_number' => 'required|integer|min:1|max:52',
                'session_number' => 'required|integer|min:1|max:7',
                'notes' => 'nullable|string',
                'audience_gender' => 'nullable|in:all,male,female',
                'required_membership_types' => 'nullable|array',
                'required_membership_types.*' => 'exists:membership_types,id',
            ]);

            $validated['user_id'] = auth()->id();
            $validated['status'] = $request->has('status');
            $validated['audience_gender'] = $validated['audience_gender'] ?? 'all';
            $validated['required_membership_types'] = $request->input('required_membership_types', []);

            WorkoutSchedule::create($validated);

            return redirect()->route('workout-schedules.index')->with('success', 'تم إنشاء جدولة التمرين بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error creating workout schedule', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'حدث خطأ أثناء إنشاء جدولة التمرين: ' . $e->getMessage());
        }
    }

    /**
     * عرض تفاصيل الجدولة
     */
    public function show(WorkoutSchedule $workoutSchedule)
    {
        try {
            if (!auth()->user()->hasAnyRole(['admin', 'coach']) &&
                (!$workoutSchedule->status || !$workoutSchedule->matchesAudience(auth()->user()))) {
                abort(403, 'هذه الجدولة غير متاحة.');
            }

            $workoutSchedule->load(['workout', 'user']);
            
            return view('workout-schedules.show', compact('workoutSchedule'));
        } catch (\Exception $e) {
            Log::error('Error showing workout schedule', ['error' => $e->getMessage()]);
            return back()->with('error', 'حدث خطأ أثناء عرض جدولة التمرين.');
        }
    }

    /**
     * عرض نموذج تعديل الجدولة
     */
    public function edit(WorkoutSchedule $workoutSchedule)
    {
        // التحقق من الصلاحيات
        if (!$workoutSchedule->canEdit(auth()->user())) {
            abort(403, 'ليس لديك صلاحية لتعديل هذه الجدولة.');
        }

        $workouts = Workout::active()->get();
        return view('workout-schedules.edit', compact('workoutSchedule', 'workouts'));
    }

    /**
     * تحديث الجدولة
     */
    public function update(Request $request, WorkoutSchedule $workoutSchedule)
    {
        try {
            // التحقق من الصلاحيات
            if (!$workoutSchedule->canEdit(auth()->user())) {
                abort(403, 'ليس لديك صلاحية لتعديل هذه الجدولة.');
            }

            $validated = $request->validate([
                'workout_id' => 'required|exists:workouts,id',
                'week_number' => 'required|integer|min:1|max:52',
                'session_number' => 'required|integer|min:1|max:7',
                'notes' => 'nullable|string',
                'audience_gender' => 'nullable|in:all,male,female',
                'required_membership_types' => 'nullable|array',
                'required_membership_types.*' => 'exists:membership_types,id',
            ]);

            $validated['status'] = $request->has('status');
            $validated['audience_gender'] = $validated['audience_gender'] ?? 'all';
            $validated['required_membership_types'] = $request->input('required_membership_types', []);

            $workoutSchedule->update($validated);

            return redirect()->route('workout-schedules.index')->with('success', 'تم تحديث جدولة التمرين بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error updating workout schedule', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'حدث خطأ أثناء تحديث جدولة التمرين: ' . $e->getMessage());
        }
    }

    /**
     * حذف الجدولة
     */
    public function destroy(WorkoutSchedule $workoutSchedule)
    {
        try {
            // التحقق من الصلاحيات
            if (!$workoutSchedule->canDelete(auth()->user())) {
                abort(403, 'ليس لديك صلاحية لحذف هذه الجدولة.');
            }

            $workoutSchedule->delete();

            return redirect()->route('workout-schedules.index')->with('success', 'تم حذف جدولة التمرين بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error deleting workout schedule', ['error' => $e->getMessage()]);
            return back()->with('error', 'حدث خطأ أثناء حذف جدولة التمرين: ' . $e->getMessage());
        }
    }

    /**
     * عرض الجدول الأسبوعي
     */
    public function weeklyView(Request $request)
    {
        try {
            $weekNumber = $request->get('week', 1);
            
            $query = WorkoutSchedule::with(['workout', 'user'])
                ->where('week_number', $weekNumber);

            // تطبيق الصلاحيات
            if (!auth()->user()->hasRole('admin')) {
                if (auth()->user()->hasRole('coach')) {
                    $query->where('user_id', auth()->id());
                } elseif (auth()->user()->hasTraineeRole()) {
                    $query->where('status', true)->visibleTo(auth()->user());
                }
            }

            $schedules = $query->orderBy('session_number')->get();

            // تنظيم البيانات حسب الجلسات
            $weeklySchedule = [];
            for ($session = 1; $session <= 7; $session++) {
                $weeklySchedule[$session] = $schedules->where('session_number', $session);
            }

            return view('workout-schedules.weekly', compact('weeklySchedule', 'weekNumber'));
        } catch (\Exception $e) {
            Log::error('Error in weekly view', ['error' => $e->getMessage()]);
            return back()->with('error', 'حدث خطأ أثناء عرض الجدول الأسبوعي.');
        }
    }
}