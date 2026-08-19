<?php

namespace App\Http\Controllers;

use App\Models\TrainingSession;
use App\Models\SessionBooking;
use App\Events\BookingLifecycleChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class TrainingSessionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|coach'])->except(['show', 'book', 'processPayment', 'paymentSuccess', 'cancel', 'rescheduleForm', 'reschedule']);
        $this->middleware('auth')->only(['book', 'processPayment', 'paymentSuccess', 'cancel', 'rescheduleForm', 'reschedule']);
    }

    /**
     * Display a listing of training sessions for admin
     */
    public function index()
    {
        $query = TrainingSession::with('user')->ordered();

        if (auth()->user()->hasRole('coach')) {
            $query->where('user_id', auth()->id());
        }

        $sessions = $query->get();
        return view('admin.training-sessions.index', compact('sessions'));
    }

    /**
     * Show the form for creating a new training session
     */
    public function create()
    {
        return view('admin.training-sessions.create');
    }

    /**
     * Store a newly created training session
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'duration_hours' => 'required|integer|min:1|max:8',
            'session_type' => 'required|in:online,in_person,hybrid',
            'capacity' => 'required|integer|min:1|max:100',
            'location' => 'nullable|string|max:255',
            'video_meeting_url' => 'nullable|url|max:2048',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'sort_order' => 'nullable|integer|min:0',
            'audience_gender' => 'nullable|in:all,male,female',
            'required_membership_types' => 'nullable|array',
            'required_membership_types.*' => 'exists:membership_types,id',
        ]);

        try {
            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('training-sessions', 'public');
                $validated['image'] = $imagePath;
            }

            // Set default values
            $validated['user_id'] = auth()->id();
            $validated['is_visible'] = $request->has('is_visible') ? true : false;
            $validated['sort_order'] = $validated['sort_order'] ?? 0;
            $validated['audience_gender'] = $validated['audience_gender'] ?? 'all';
            $validated['required_membership_types'] = $request->input('required_membership_types', []);

            // Create training session
            TrainingSession::create($validated);

            // Clear cache
            TrainingSession::clearCache();

            return redirect()->route('admin.training-sessions.index')
                ->with('success', 'تم إنشاء جلسة التدريب بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error creating training session: ' . $e->getMessage());
            return back()->withInput()->with('error', 'حدث خطأ أثناء إنشاء جلسة التدريب: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified training session for booking
     */
    public function show(TrainingSession $trainingSession)
    {
        if (!$trainingSession->is_visible || !$trainingSession->matchesAudience(auth()->user())) {
            abort(404);
        }

        // Optimize query by selecting only needed fields
        $trainingSession = TrainingSession::select([
            'id', 'title', 'description', 'price', 'duration_hours', 'session_type', 'capacity',
            'location', 'video_meeting_url', 'image', 'is_visible', 'user_id', 'audience_gender', 'required_membership_types'
        ])->findOrFail($trainingSession->id);
        return view('training-sessions.show', compact('trainingSession'));
    }

    /**
     * Show the form for editing the training session
     */
    public function edit(TrainingSession $trainingSession)
    {
        abort_unless($trainingSession->canManage(auth()->user()), 403);

        return view('admin.training-sessions.edit', compact('trainingSession'));
    }

    /**
     * Update the training session
     */
    public function update(Request $request, TrainingSession $trainingSession)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'duration_hours' => 'required|integer|min:1|max:8',
            'session_type' => 'required|in:online,in_person,hybrid',
            'capacity' => 'required|integer|min:1|max:100',
            'location' => 'nullable|string|max:255',
            'video_meeting_url' => 'nullable|url|max:2048',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'sort_order' => 'nullable|integer|min:0',
            'audience_gender' => 'nullable|in:all,male,female',
            'required_membership_types' => 'nullable|array',
            'required_membership_types.*' => 'exists:membership_types,id',
        ]);

        try {
            abort_unless($trainingSession->canManage(auth()->user()), 403);

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($trainingSession->image) {
                    Storage::disk('public')->delete($trainingSession->image);
                }
                $imagePath = $request->file('image')->store('training-sessions', 'public');
                $validated['image'] = $imagePath;
            }

            // Set boolean values
            $validated['is_visible'] = $request->has('is_visible') ? true : false;
            $validated['sort_order'] = $validated['sort_order'] ?? 0;
            $validated['audience_gender'] = $validated['audience_gender'] ?? 'all';
            $validated['required_membership_types'] = $request->input('required_membership_types', []);

            // Update training session
            $trainingSession->update($validated);

            // Clear cache
            TrainingSession::clearCache();

            return redirect()->route('admin.training-sessions.index')
                ->with('success', 'تم تحديث جلسة التدريب بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error updating training session: ' . $e->getMessage());
            return back()->withInput()->with('error', 'حدث خطأ أثناء تحديث جلسة التدريب: ' . $e->getMessage());
        }
    }

    /**
     * Remove the training session
     */
    public function destroy(TrainingSession $trainingSession)
    {
        try {
            abort_unless($trainingSession->canManage(auth()->user()), 403);

            // Check if there are any bookings
            if ($trainingSession->bookings()->count() > 0) {
                return back()->with('error', 'لا يمكن حذف جلسة التدريب لوجود حجوزات مرتبطة بها.');
            }

            // Delete image if exists
            if ($trainingSession->image) {
                Storage::disk('public')->delete($trainingSession->image);
            }

            $trainingSession->delete();

            // Clear cache
            TrainingSession::clearCache();

            return redirect()->route('admin.training-sessions.index')
                ->with('success', 'تم حذف جلسة التدريب بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error deleting training session: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء حذف جلسة التدريب: ' . $e->getMessage());
        }
    }

    /**
     * Toggle session visibility
     */
    public function toggleVisibility(TrainingSession $trainingSession)
    {
        try {
            abort_unless($trainingSession->canManage(auth()->user()), 403);

            $trainingSession->update(['is_visible' => !$trainingSession->is_visible]);

            // Clear cache
            TrainingSession::clearCache();

            $status = $trainingSession->is_visible ? 'إظهار' : 'إخفاء';
            return redirect()->route('admin.training-sessions.index')
                ->with('success', "تم {$status} جلسة التدريب بنجاح.");
        } catch (\Exception $e) {
            Log::error('Error toggling training session visibility: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء تغيير حالة جلسة التدريب: ' . $e->getMessage());
        }
    }

    /**
     * Book a training session
     */
    public function book(Request $request, TrainingSession $trainingSession)
    {
        $validated = $request->validate([
            'booking_date' => 'required|date|after:today',
            'booking_time' => 'required|date_format:H:i',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            if (!$trainingSession->matchesAudience(auth()->user())) {
                abort(403, 'هذه الجلسة غير متاحة لمسارك الحالي.');
            }

            // Check if slot is available
            if (!$trainingSession->isAvailableAt($validated['booking_date'], $validated['booking_time'])) {
                return back()->with('error', 'هذا الموعد محجوز بالفعل. يرجى اختيار موعد آخر.');
            }

            // Create booking
            $booking = SessionBooking::create([
                'training_session_id' => $trainingSession->id,
                'user_id' => auth()->id(),
                'booking_date' => $validated['booking_date'],
                'booking_time' => $validated['booking_time'],
                'video_meeting_url' => $trainingSession->video_meeting_url,
                'payment_amount' => $trainingSession->price,
                'notes' => $validated['notes'],
                'status' => 'pending',
                'payment_status' => $trainingSession->price > 0 ? 'pending' : 'paid',
                'attendance_status' => 'scheduled',
            ]);

            event(new BookingLifecycleChanged($booking->loadMissing('trainingSession'), 'created'));

            // If free session, confirm immediately
            if ($trainingSession->price == 0) {
                $booking->update(['status' => 'confirmed']);
                
                // Send confirmation email
                $this->sendBookingConfirmationEmail($booking);
                
                return redirect()->route('training-sessions.booking-success', $booking)
                    ->with('success', 'تم تأكيد حجز الجلسة المجانية بنجاح.');
            }

            // Redirect to payment
            return redirect()->route('training-sessions.payment', $booking);

        } catch (\Exception $e) {
            Log::error('Error booking training session: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء حجز الجلسة: ' . $e->getMessage());
        }
    }

    /**
     * Process payment for booking
     */
    public function processPayment(Request $request, SessionBooking $sessionBooking)
    {
        try {
            abort_unless($sessionBooking->user_id === auth()->id(), 403);

            // Initialize Stripe
            Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

            // Create payment intent
            $paymentIntent = PaymentIntent::create([
                'amount' => $sessionBooking->payment_amount * 100, // Convert to cents
                'currency' => 'sar',
                'metadata' => [
                    'booking_id' => $sessionBooking->id,
                    'user_id' => $sessionBooking->user_id,
                    'session_id' => $sessionBooking->training_session_id
                ]
            ]);

            // Update booking with payment intent ID
            $sessionBooking->update(['stripe_payment_intent_id' => $paymentIntent->id]);

            return view('training-sessions.payment', ['booking' => $sessionBooking, 'paymentIntent' => $paymentIntent]);

        } catch (\Exception $e) {
            Log::error('Error processing payment: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء معالجة الدفع: ' . $e->getMessage());
        }
    }

    /**
     * Handle successful payment
     */
    public function paymentSuccess(SessionBooking $sessionBooking)
    {
        try {
            abort_unless($sessionBooking->user_id === auth()->id(), 403);

            // Update booking status
            $sessionBooking->update([
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'attendance_status' => 'scheduled',
            ]);

            event(new BookingLifecycleChanged($sessionBooking->loadMissing('trainingSession'), 'confirmed'));

            // Send confirmation email
            $this->sendBookingConfirmationEmail($sessionBooking);

            return view('training-sessions.booking-success', ['booking' => $sessionBooking]);

        } catch (\Exception $e) {
            Log::error('Error handling payment success: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء تأكيد الحجز.');
        }
    }

    public function cancel(SessionBooking $sessionBooking)
    {
        abort_unless($sessionBooking->canManage(auth()->user()), 403);

        if (! $sessionBooking->canBeCancelled()) {
            return back()->with('error', 'لا يمكن إلغاء هذا الحجز في حالته الحالية.');
        }

        $sessionBooking->update([
            'status' => 'cancelled',
            'attendance_status' => 'late_cancelled',
            'cancelled_at' => now(),
            'cancelled_by_user_id' => auth()->id(),
        ]);

        event(new BookingLifecycleChanged($sessionBooking->loadMissing('trainingSession'), 'cancelled'));

        return back()->with('success', 'تم إلغاء الحجز بنجاح.');
    }

    public function rescheduleForm(SessionBooking $sessionBooking)
    {
        abort_unless($sessionBooking->canManage(auth()->user()), 403);

        return view('training-sessions.reschedule', ['booking' => $sessionBooking->load('trainingSession', 'user')]);
    }

    public function reschedule(Request $request, SessionBooking $sessionBooking)
    {
        abort_unless($sessionBooking->canManage(auth()->user()), 403);

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

        if (auth()->user()?->hasTraineeRole()) {
            return redirect()->route('client.bookings.index')->with('success', 'تمت إعادة جدولة الحجز بنجاح.');
        }

        return redirect()->route('admin.session-bookings.edit', $sessionBooking)->with('success', 'تمت إعادة جدولة الحجز بنجاح.');
    }

    /**
     * Send booking confirmation email
     */
    private function sendBookingConfirmationEmail(SessionBooking $booking)
    {
        try {
            // Here you would implement email sending
            // For now, we'll just log it
            Log::info('Booking confirmation email sent', [
                'booking_id' => $booking->id,
                'user_email' => $booking->user->email,
                'session_title' => $booking->trainingSession->title
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending confirmation email: ' . $e->getMessage());
        }
    }
}