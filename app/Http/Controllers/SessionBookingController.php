<?php

namespace App\Http\Controllers;

use App\Models\SessionBooking;
use App\Models\TrainingSession;
use App\Events\BookingLifecycleChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SessionBookingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|coach']);
    }

    /**
     * Display a listing of bookings
     */
    public function index()
    {
        $query = SessionBooking::with(['trainingSession', 'user']);

        if (auth()->user()->hasRole('coach')) {
            $query->whereHas('trainingSession', fn ($sessionQuery) => $sessionQuery->where('user_id', auth()->id()));
        }

        $bookings = $query
            ->latest()
            ->paginate(20);

        return view('admin.session-bookings.index', compact('bookings'));
    }

    /**
     * Show the form for editing the booking
     */
    public function edit(SessionBooking $sessionBooking)
    {
        abort_unless($sessionBooking->canManage(auth()->user()), 403);

        return view('admin.session-bookings.edit', compact('sessionBooking'));
    }

    /**
     * Update the booking
     */
    public function update(Request $request, SessionBooking $sessionBooking)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled',
            'payment_status' => 'required|in:pending,paid,failed,refunded',
            'attendance_status' => 'nullable|in:scheduled,attended,missed,late_cancelled',
            'video_meeting_url' => 'nullable|url|max:2048',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            abort_unless($sessionBooking->canManage(auth()->user()), 403);
            $sessionBooking->update($validated);
            event(new BookingLifecycleChanged($sessionBooking->loadMissing('trainingSession'), 'updated'));

            return redirect()->route('admin.session-bookings.index')
                ->with('success', 'تم تحديث الحجز بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error updating booking: ' . $e->getMessage());
            return back()->withInput()->with('error', 'حدث خطأ أثناء تحديث الحجز: ' . $e->getMessage());
        }
    }

    /**
     * Remove the booking
     */
    public function destroy(SessionBooking $sessionBooking)
    {
        try {
            abort_unless($sessionBooking->canManage(auth()->user()), 403);
            $sessionBooking->delete();

            return redirect()->route('admin.session-bookings.index')
                ->with('success', 'تم حذف الحجز بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error deleting booking: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء حذف الحجز: ' . $e->getMessage());
        }
    }

    /**
     * Update booking status
     */
    public function updateStatus(Request $request, SessionBooking $sessionBooking)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled'
        ]);

        try {
            abort_unless($sessionBooking->canManage(auth()->user()), 403);
            $sessionBooking->update($validated);
            event(new BookingLifecycleChanged($sessionBooking->loadMissing('trainingSession'), 'status_updated'));

            $statusText = [
                'pending' => 'في الانتظار',
                'confirmed' => 'مؤكد',
                'completed' => 'مكتمل',
                'cancelled' => 'ملغي'
            ];

            return redirect()->route('admin.session-bookings.index')
                ->with('success', 'تم تحديث حالة الحجز إلى: ' . $statusText[$validated['status']]);
        } catch (\Exception $e) {
            Log::error('Error updating booking status: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء تحديث حالة الحجز: ' . $e->getMessage());
        }
    }
}