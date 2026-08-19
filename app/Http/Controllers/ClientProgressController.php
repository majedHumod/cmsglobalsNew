<?php

namespace App\Http\Controllers;

use App\Models\ClientProfile;
use App\Models\ProgressCheckIn;
use App\Models\User;
use App\Events\CheckInSubmitted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientProgressController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|coach|user|client']);
    }

    public function index(User $user)
    {
        abort_unless($user->hasTraineeRole(), 404);
        $this->ensureCanAccessClient($user);

        $user->load([
            'coach',
            'clientProfile',
            'progressCheckIns' => fn ($query) => $query->with(['coach', 'submittedBy'])->latest('checked_in_at'),
        ]);

        return view('progress.index', [
            'client' => $user,
            'profile' => $user->clientProfile ?: new ClientProfile(['user_id' => $user->id]),
            'checkIns' => $user->progressCheckIns,
        ]);
    }

    public function create(User $user)
    {
        abort_unless($user->hasTraineeRole(), 404);
        $this->ensureCanAccessClient($user);

        return view('progress.create', ['client' => $user]);
    }

    public function store(Request $request, User $user)
    {
        abort_unless($user->hasTraineeRole(), 404);
        $this->ensureCanAccessClient($user);

        $validated = $request->validate([
            'checked_in_at' => 'required|date',
            'weight' => 'nullable|numeric|min:0|max:1000',
            'body_fat_percentage' => 'nullable|numeric|min:0|max:100',
            'waist_cm' => 'nullable|numeric|min:0|max:500',
            'chest_cm' => 'nullable|numeric|min:0|max:500',
            'hips_cm' => 'nullable|numeric|min:0|max:500',
            'arm_cm' => 'nullable|numeric|min:0|max:500',
            'thigh_cm' => 'nullable|numeric|min:0|max:500',
            'energy_level' => 'nullable|integer|min:1|max:10',
            'training_adherence' => 'nullable|integer|min:1|max:10',
            'nutrition_adherence' => 'nullable|integer|min:1|max:10',
            'notes' => 'nullable|string|max:5000',
            'coach_feedback' => 'nullable|string|max:5000',
            'next_steps' => 'nullable|string|max:5000',
            'progress_photo' => 'nullable|image|max:4096',
        ]);

        if ($request->hasFile('progress_photo')) {
            $validated['progress_photo_path'] = $request->file('progress_photo')->store('progress-check-ins', 'public');
        }

        $validated['user_id'] = $user->id;
        $validated['coach_id'] = $user->coach_id ?: auth()->id();
        $validated['submitted_by_user_id'] = auth()->id();

        $checkIn = ProgressCheckIn::create($validated);
        event(new CheckInSubmitted($checkIn));

        return redirect()->route('clients.progress.index', $user)->with('success', 'تم حفظ التحديث الدوري بنجاح.');
    }

    public function show(ProgressCheckIn $progressCheckIn)
    {
        $progressCheckIn->load(['user.coach', 'coach', 'submittedBy']);
        $this->ensureCanAccessClient($progressCheckIn->user);

        return view('progress.show', ['checkIn' => $progressCheckIn]);
    }

    public function updateProfile(Request $request, User $user)
    {
        abort_unless($user->hasTraineeRole(), 404);
        $this->ensureCanAccessClient($user);

        $validated = $request->validate([
            'fitness_goal' => 'nullable|string|max:5000',
            'target_weight' => 'nullable|numeric|min:0|max:1000',
            'activity_level' => 'required|in:beginner,intermediate,advanced',
            'preferred_contact_method' => 'required|in:whatsapp,sms,email,phone',
            'injuries' => 'nullable|string|max:5000',
            'medical_notes' => 'nullable|string|max:5000',
            'onboarding_notes' => 'nullable|string|max:5000',
            'current_program_week' => 'nullable|integer|min:1|max:52',
            'program_started_at' => 'nullable|date',
            'week_advance_mode' => 'nullable|in:auto,manual',
        ]);

        if (! auth()->user()->hasAnyRole(['admin', 'coach'])) {
            unset($validated['current_program_week'], $validated['program_started_at'], $validated['week_advance_mode']);
        } else {
            $validated['current_program_week'] = $validated['current_program_week'] ?? 1;
        }

        $profile = $user->clientProfile ?: new ClientProfile(['user_id' => $user->id]);
        $profile->fill($validated);
        $profile->user()->associate($user);
        $profile->save();

        return redirect()->route('clients.progress.index', $user)->with('success', 'تم تحديث ملف العميل بنجاح.');
    }

    public function destroy(ProgressCheckIn $progressCheckIn)
    {
        $this->ensureCanAccessClient($progressCheckIn->user);

        if ($progressCheckIn->progress_photo_path) {
            Storage::disk('public')->delete($progressCheckIn->progress_photo_path);
        }

        $progressCheckIn->delete();

        return back()->with('success', 'تم حذف التحديث الدوري.');
    }

    private function ensureCanAccessClient(User $client): void
    {
        $currentUser = auth()->user();

        if ($currentUser->hasRole('admin')) {
            return;
        }

        if ($currentUser->id === $client->id) {
            return;
        }

        abort_unless($currentUser->isCoachOf($client), 403);
    }
}
