<?php

namespace App\Http\Controllers;

use App\Events\BookingLifecycleChanged;
use App\Models\SessionBooking;
use App\Models\TrainingSession;
use App\Services\BookingSlotService;
use Illuminate\Http\Request;

class ClientBookingWebController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'trainee']);
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $upcoming = SessionBooking::query()
            ->with('trainingSession')
            ->where('user_id', $user->id)
            ->upcoming()
            ->orderBy('booking_date')
            ->orderBy('booking_time')
            ->get();

        $past = SessionBooking::query()
            ->with('trainingSession')
            ->where('user_id', $user->id)
            ->whereDate('booking_date', '<', now()->toDateString())
            ->orderByDesc('booking_date')
            ->limit(10)
            ->get();

        $sessions = TrainingSession::query()
            ->visible()
            ->visibleTo($user)
            ->ordered()
            ->get();

        return view('client.bookings.index', compact('upcoming', 'past', 'sessions'));
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $sessions = TrainingSession::query()
            ->visible()
            ->visibleTo($user)
            ->ordered()
            ->get();

        return view('client.bookings.create', [
            'sessions' => $sessions,
            'selectedSessionId' => $request->integer('session'),
            'selectedDate' => $request->input('date', now()->addDay()->toDateString()),
        ]);
    }

    public function slots(Request $request, TrainingSession $trainingSession, BookingSlotService $slotService)
    {
        abort_unless($trainingSession->is_visible && $trainingSession->matchesAudience($request->user()), 404);

        $validated = $request->validate([
            'date' => 'required|date|after:today',
        ]);

        return response()->json([
            'slots' => $slotService->slotsForSession($trainingSession, $validated['date'], $request->user()),
        ]);
    }

    public function store(Request $request, BookingSlotService $slotService)
    {
        $validated = $request->validate([
            'training_session_id' => 'required|exists:training_sessions,id',
            'booking_date' => 'required|date|after:today',
            'booking_time' => 'required|date_format:H:i',
            'notes' => 'nullable|string|max:500',
        ]);

        $session = TrainingSession::query()->findOrFail($validated['training_session_id']);
        $booking = $slotService->createBooking(
            $session,
            $request->user(),
            $validated['booking_date'],
            $validated['booking_time'],
            $validated['notes'] ?? null
        );

        event(new BookingLifecycleChanged($booking->loadMissing('trainingSession'), 'created'));

        if ((float) $session->price > 0) {
            return redirect()->route('training-sessions.payment', $booking)
                ->with('success', 'تم إنشاء الحجز. أكمل الدفع لتأكيده.');
        }

        return redirect()->route('client.bookings.index')
            ->with('success', 'تم تأكيد الحجز بنجاح.');
    }

    public function cancel(Request $request, SessionBooking $sessionBooking)
    {
        abort_unless($sessionBooking->user_id === $request->user()->id, 403);

        if (! $sessionBooking->canBeCancelled()) {
            return back()->with('error', 'لا يمكن إلغاء هذا الحجز.');
        }

        $sessionBooking->update([
            'status' => 'cancelled',
            'attendance_status' => 'late_cancelled',
            'cancelled_at' => now(),
            'cancelled_by_user_id' => $request->user()->id,
        ]);

        event(new BookingLifecycleChanged($sessionBooking->loadMissing('trainingSession'), 'cancelled'));

        return back()->with('success', 'تم إلغاء الحجز.');
    }

    public function reschedule(Request $request, SessionBooking $sessionBooking, BookingSlotService $slotService)
    {
        abort_unless($sessionBooking->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'booking_date' => 'required|date|after:today',
            'booking_time' => 'required|date_format:H:i',
        ]);

        if (! $sessionBooking->trainingSession->isAvailableAt($validated['booking_date'], $validated['booking_time'])) {
            return back()->withInput()->with('error', 'الموعد الجديد غير متاح.');
        }

        $sessionBooking->update([
            'booking_date' => $validated['booking_date'],
            'booking_time' => $validated['booking_time'],
            'attendance_status' => 'scheduled',
            'cancelled_at' => null,
            'cancelled_by_user_id' => null,
        ]);

        event(new BookingLifecycleChanged($sessionBooking->loadMissing('trainingSession'), 'rescheduled'));

        return redirect()->route('client.bookings.index')->with('success', 'تمت إعادة جدولة الحجز.');
    }
}
