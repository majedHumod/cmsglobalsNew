<?php

namespace App\Http\Controllers;

use App\Events\CheckInSubmitted;
use App\Models\ProgressCheckIn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientProgressWebController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'trainee']);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $checkIns = ProgressCheckIn::query()
            ->where('user_id', $user->id)
            ->latest('checked_in_at')
            ->limit(10)
            ->get();

        return view('client.progress.index', [
            'checkIns' => $checkIns,
        ]);
    }

    public function create(Request $request)
    {
        return view('client.progress.create');
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'checked_in_at' => 'required|date',
            'weight' => 'nullable|numeric|min:0|max:1000',
            'body_fat_percentage' => 'nullable|numeric|min:0|max:100',
            'waist_cm' => 'nullable|numeric|min:0|max:500',
            'energy_level' => 'nullable|integer|min:1|max:10',
            'training_adherence' => 'nullable|integer|min:1|max:10',
            'nutrition_adherence' => 'nullable|integer|min:1|max:10',
            'notes' => 'nullable|string|max:5000',
            'progress_photo' => 'nullable|image|max:4096',
        ]);

        if ($request->hasFile('progress_photo')) {
            $validated['progress_photo_path'] = $request->file('progress_photo')->store('progress-check-ins', 'public');
        }

        $checkIn = ProgressCheckIn::create([
            ...$validated,
            'user_id' => $user->id,
            'coach_id' => $user->coach_id ?: $user->id,
            'submitted_by_user_id' => $user->id,
        ]);

        event(new CheckInSubmitted($checkIn));

        return redirect()->route('client.progress.index')
            ->with('success', 'تم إرسال Check-in للمدرب بنجاح.');
    }
}
