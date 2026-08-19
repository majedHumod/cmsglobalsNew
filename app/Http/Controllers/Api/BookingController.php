<?php

namespace App\Http\Controllers\Api;

use App\Events\BookingLifecycleChanged;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BookingResource;
use App\Http\Resources\Api\TrainingSessionResource;
use App\Models\SessionBooking;
use App\Models\TrainingSession;
use App\Services\BookingSlotService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($user->hasAnyRole(['user', 'client']), 403);

        $bookings = SessionBooking::query()
            ->with(['trainingSession.user:id,name'])
            ->where('user_id', $user->id)
            ->orderBy('booking_date')
            ->orderBy('booking_time')
            ->get();

        $upcoming = $bookings
            ->filter(fn (SessionBooking $booking) => $this->isListUpcoming($booking))
            ->values();

        $past = $bookings
            ->filter(fn (SessionBooking $booking) => ! $this->isListUpcoming($booking))
            ->sortByDesc(fn (SessionBooking $booking) =>
                optional($booking->booking_date)->toDateString().' '.optional($booking->booking_time)->format('H:i')
            )
            ->values()
            ->take(20);

        $tab = $request->query('tab', 'upcoming');
        if (! in_array($tab, ['upcoming', 'past'], true)) {
            $tab = 'upcoming';
        }

        return response()->json([
            'screen' => [
                'title' => 'حجوزاتي',
                'add_label' => 'حجز جديد',
                'hint' => 'لإدارة أو إلغاء حجز، اضغط على أي حجز من القائمة',
                'empty_upcoming' => 'لا توجد حجوزات قادمة',
                'empty_past' => 'لا توجد حجوزات سابقة',
            ],
            'tabs' => [
                [
                    'key' => 'upcoming',
                    'label' => 'المقبلة',
                    'count' => $upcoming->count(),
                    'active' => $tab === 'upcoming',
                ],
                [
                    'key' => 'past',
                    'label' => 'السابقة',
                    'count' => $past->count(),
                    'active' => $tab === 'past',
                ],
            ],
            'active_tab' => $tab,
            'upcoming' => BookingResource::collection($upcoming),
            'past' => BookingResource::collection($past),
            'bookings' => BookingResource::collection($tab === 'past' ? $past : $upcoming),
            'actions' => [
                'create_url' => url('/api/v1/bookings/sessions'),
                'store_url' => url('/api/v1/bookings'),
                'list_url' => url('/api/v1/bookings'),
            ],
        ]);
    }

    public function sessions(Request $request)
    {
        $user = $request->user();
        abort_unless($user->hasAnyRole(['user', 'client']), 403);

        $sessions = TrainingSession::query()
            ->with('user:id,name')
            ->visible()
            ->visibleTo($user)
            ->ordered()
            ->get();

        $minDate = now()->addDay()->toDateString();

        return response()->json([
            'screen' => [
                'title' => 'حجز جلسة جديدة',
                'subtitle' => 'اختر الجلسة ثم التاريخ والوقت المتاح',
                'submit_label' => 'تأكيد الحجز',
            ],
            'form' => [
                'method' => 'POST',
                'endpoint' => '/api/v1/bookings',
                'fields' => [
                    [
                        'key' => 'training_session_id',
                        'label' => 'الجلسة',
                        'type' => 'select',
                        'required' => true,
                        'placeholder' => 'اختر الجلسة',
                    ],
                    [
                        'key' => 'booking_date',
                        'label' => 'التاريخ',
                        'type' => 'date',
                        'required' => true,
                        'min' => $minDate,
                        'hint' => 'الحجز متاح من الغد فصاعداً',
                    ],
                    [
                        'key' => 'booking_time',
                        'label' => 'الوقت',
                        'type' => 'select',
                        'required' => true,
                        'placeholder' => 'اختر الوقت',
                        'depends_on' => ['training_session_id', 'booking_date'],
                        'slots_endpoint_template' => '/api/v1/bookings/sessions/{training_session_id}/slots?date={booking_date}',
                    ],
                    [
                        'key' => 'notes',
                        'label' => 'ملاحظات',
                        'type' => 'textarea',
                        'required' => false,
                        'max_length' => 500,
                    ],
                ],
            ],
            'sessions' => TrainingSessionResource::collection($sessions),
            'min_booking_date' => $minDate,
            'actions' => [
                'store_url' => url('/api/v1/bookings'),
                'list_url' => url('/api/v1/bookings'),
            ],
        ]);
    }

    public function slots(Request $request, TrainingSession $trainingSession, BookingSlotService $slotService)
    {
        $user = $request->user();
        abort_unless($user->hasAnyRole(['user', 'client']), 403);
        abort_unless($trainingSession->is_visible && $trainingSession->matchesAudience($user), 404);

        $validated = $request->validate([
            'date' => 'required|date|after:today',
        ]);

        $slots = $slotService->slotsForSession($trainingSession, $validated['date'], $user)
            ->map(function (array $slot) {
                $label = Carbon::parse($slot['time'])->locale('ar')->translatedFormat('g:i A');

                return [
                    'time' => $slot['time'],
                    'label' => $label,
                    'available' => (bool) $slot['available'],
                    'disabled' => ! $slot['available'],
                ];
            })
            ->values();

        return response()->json([
            'training_session_id' => $trainingSession->id,
            'date' => $validated['date'],
            'date_label' => Carbon::parse($validated['date'])->locale('ar')->translatedFormat('l d F'),
            'slots' => $slots,
            'available_count' => $slots->where('available', true)->count(),
        ]);
    }

    public function store(Request $request, BookingSlotService $slotService)
    {
        $user = $request->user();
        abort_unless($user->hasAnyRole(['user', 'client']), 403);

        $validated = $request->validate([
            'training_session_id' => 'required|exists:training_sessions,id',
            'booking_date' => 'required|date|after:today',
            'booking_time' => 'required|date_format:H:i',
            'notes' => 'nullable|string|max:500',
        ]);

        $session = TrainingSession::query()->with('user:id,name')->findOrFail($validated['training_session_id']);
        $booking = $slotService->createBooking(
            $session,
            $user,
            $validated['booking_date'],
            $validated['booking_time'],
            $validated['notes'] ?? null
        )->load(['trainingSession.user:id,name']);

        event(new BookingLifecycleChanged($booking, 'created'));

        $paymentRequired = (float) $session->price > 0;

        return response()->json([
            'status' => 'ok',
            'message' => 'تم تأكيد الحجز بنجاح.',
            'booking' => new BookingResource($booking),
            'payment_required' => $paymentRequired,
            'payment_url' => $paymentRequired
                ? route('training-sessions.payment', $booking)
                : null,
        ], 201);
    }

    public function cancel(Request $request, SessionBooking $sessionBooking)
    {
        $user = $request->user();
        abort_unless($user->hasAnyRole(['user', 'client']), 403);
        abort_unless((int) $sessionBooking->user_id === (int) $user->id, 403);

        if (! $sessionBooking->canBeCancelled()) {
            return response()->json(['message' => 'لا يمكن إلغاء هذا الحجز.'], 422);
        }

        $sessionBooking->update([
            'status' => 'cancelled',
            'attendance_status' => 'late_cancelled',
            'cancelled_at' => now(),
            'cancelled_by_user_id' => $user->id,
        ]);

        event(new BookingLifecycleChanged($sessionBooking->loadMissing('trainingSession.user'), 'cancelled'));

        return response()->json([
            'status' => 'ok',
            'message' => 'تم إلغاء الحجز.',
            'booking' => new BookingResource($sessionBooking->fresh(['trainingSession.user:id,name'])),
        ]);
    }

    public function reschedule(Request $request, SessionBooking $sessionBooking, BookingSlotService $slotService)
    {
        $user = $request->user();
        abort_unless($user->hasAnyRole(['user', 'client']), 403);
        abort_unless((int) $sessionBooking->user_id === (int) $user->id, 403);

        $validated = $request->validate([
            'booking_date' => 'required|date|after:today',
            'booking_time' => 'required|date_format:H:i',
        ]);

        $session = $sessionBooking->trainingSession;
        abort_unless($session, 404);

        if (! $session->isAvailableAt($validated['booking_date'], $validated['booking_time'])) {
            return response()->json(['message' => 'الموعد الجديد غير متاح.'], 422);
        }

        $sessionBooking->update([
            'booking_date' => $validated['booking_date'],
            'booking_time' => $validated['booking_time'],
            'status' => $sessionBooking->status === 'cancelled' ? 'confirmed' : $sessionBooking->status,
            'attendance_status' => 'scheduled',
            'cancelled_at' => null,
            'cancelled_by_user_id' => null,
        ]);

        event(new BookingLifecycleChanged($sessionBooking->loadMissing('trainingSession.user'), 'rescheduled'));

        return response()->json([
            'status' => 'ok',
            'message' => 'تمت إعادة جدولة الحجز.',
            'booking' => new BookingResource($sessionBooking->fresh(['trainingSession.user:id,name'])),
        ]);
    }

    private function isListUpcoming(SessionBooking $booking): bool
    {
        if (in_array($booking->status, ['cancelled', 'completed'], true)) {
            return false;
        }

        return (bool) $booking->is_upcoming;
    }
}
