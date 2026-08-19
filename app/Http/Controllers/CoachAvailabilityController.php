<?php

namespace App\Http\Controllers;

use App\Models\CoachAvailability;
use App\Models\User;
use Illuminate\Http\Request;

class CoachAvailabilityController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|coach']);
    }

    public function index(Request $request)
    {
        $currentUser = auth()->user();

        $query = CoachAvailability::query()->with('user');
        if ($currentUser->hasRole('coach')) {
            $query->where('user_id', $currentUser->id);
        } elseif ($request->filled('coach_id')) {
            $query->where('user_id', $request->integer('coach_id'));
        }

        $availabilities = $query->orderBy('day_of_week')->orderBy('start_time')->get();
        $coaches = User::query()->coaches()->orderBy('name')->get();

        return view('coach-availabilities.index', compact('availabilities', 'coaches'));
    }

    public function create()
    {
        $coaches = User::query()->coaches()->orderBy('name')->get();

        return view('coach-availabilities.create', compact('coaches'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateAvailability($request);

        if (auth()->user()->hasRole('coach') && ! auth()->user()->hasRole('admin')) {
            $validated['user_id'] = auth()->id();
        }

        $validated['is_active'] = $request->boolean('is_active');
        $validated['buffer_minutes'] = (int) ($validated['buffer_minutes'] ?? 0);

        CoachAvailability::create($validated);

        return redirect()->route('coach-availabilities.index')->with('success', 'تم حفظ التوفر الأسبوعي بنجاح.');
    }

    public function edit(CoachAvailability $coachAvailability)
    {
        $this->ensureCanManage($coachAvailability);
        $coaches = User::query()->coaches()->orderBy('name')->get();

        return view('coach-availabilities.edit', compact('coachAvailability', 'coaches'));
    }

    public function update(Request $request, CoachAvailability $coachAvailability)
    {
        $this->ensureCanManage($coachAvailability);
        $validated = $this->validateAvailability($request);

        if (auth()->user()->hasRole('coach') && ! auth()->user()->hasRole('admin')) {
            $validated['user_id'] = auth()->id();
        }

        $validated['is_active'] = $request->boolean('is_active');
        $validated['buffer_minutes'] = (int) ($validated['buffer_minutes'] ?? 0);

        $coachAvailability->update($validated);

        return redirect()->route('coach-availabilities.index')->with('success', 'تم تحديث التوفر الأسبوعي بنجاح.');
    }

    public function destroy(CoachAvailability $coachAvailability)
    {
        $this->ensureCanManage($coachAvailability);
        $coachAvailability->delete();

        return redirect()->route('coach-availabilities.index')->with('success', 'تم حذف فترة التوفر.');
    }

    private function validateAvailability(Request $request): array
    {
        // Browsers / MySQL TIME often submit H:i:s; strict H:i alone fails silently (no @error on form).
        $request->merge([
            'start_time' => $this->normalizeTime($request->input('start_time')),
            'end_time' => $this->normalizeTime($request->input('end_time')),
        ]);

        $isCoachOnly = auth()->user()->hasRole('coach') && ! auth()->user()->hasRole('admin');

        return $request->validate([
            'user_id' => $isCoachOnly ? 'nullable' : 'required|exists:users,id',
            'day_of_week' => 'required|integer|min:0|max:6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'slot_duration_minutes' => 'required|integer|min:15|max:480',
            'buffer_minutes' => 'nullable|integer|min:0|max:240',
            'location' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:1|max:100',
            'is_active' => 'nullable|boolean',
        ]);
    }

    private function normalizeTime(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = trim((string) $value);

        if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $value)) {
            [$h, $m] = array_map('intval', explode(':', $value));

            return sprintf('%02d:%02d', $h, $m);
        }

        return $value;
    }

    private function ensureCanManage(CoachAvailability $coachAvailability): void
    {
        $currentUser = auth()->user();
        abort_unless($currentUser->hasRole('admin') || $coachAvailability->user_id === $currentUser->id, 403);
    }
}
