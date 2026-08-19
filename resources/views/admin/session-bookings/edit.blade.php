@extends('layouts.admin')

@section('title', 'تعديل الحجز')
@section('header', 'تعديل حجز جلسة التدريب')

@section('header_actions')
<div class="flex gap-2">
    @if($sessionBooking->canBeCancelled())
        <a href="{{ route('training-sessions.reschedule-form', $sessionBooking) }}" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">إعادة جدولة</a>
    @endif
    <a href="{{ route('admin.session-bookings.index') }}" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">العودة للحجوزات</a>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white shadow rounded-lg p-6">
        <form method="POST" action="{{ route('admin.session-bookings.update', $sessionBooking) }}" class="space-y-6">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">حالة الحجز</label>
                    <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach(['pending' => 'في الانتظار', 'confirmed' => 'مؤكد', 'completed' => 'مكتمل', 'cancelled' => 'ملغي'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $sessionBooking->status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">حالة الدفع</label>
                    <select name="payment_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach(['pending' => 'في الانتظار', 'paid' => 'مدفوع', 'failed' => 'فشل', 'refunded' => 'مسترد'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('payment_status', $sessionBooking->payment_status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">حالة الحضور</label>
                    <select name="attendance_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach(['scheduled' => 'مجدول', 'attended' => 'حضر', 'missed' => 'لم يحضر', 'late_cancelled' => 'إلغاء متأخر'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('attendance_status', $sessionBooking->attendance_status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">المبلغ</label>
                    <input type="text" disabled value="{{ number_format($sessionBooking->payment_amount, 2) }} ريال" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">رابط الاجتماع المرئي</label>
                <input type="url" name="video_meeting_url" value="{{ old('video_meeting_url', $sessionBooking->video_meeting_url) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="https://zoom.us/...">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">ملاحظات</label>
                <textarea name="notes" rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $sessionBooking->notes) }}</textarea>
            </div>

            <div class="flex justify-end gap-3">
                <button type="submit" class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">حفظ التعديلات</button>
            </div>
        </form>

        @if($sessionBooking->canBeCancelled())
            <form method="POST" action="{{ route('training-sessions.cancel', $sessionBooking) }}" class="mt-4 flex justify-end" onsubmit="return confirm('إلغاء هذا الحجز؟');">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100">إلغاء الحجز</button>
            </form>
        @endif
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900">ملخص الحجز</h3>
        <dl class="mt-4 space-y-3 text-sm">
            <div>
                <dt class="text-gray-500">العميل</dt>
                <dd class="font-medium text-gray-900">{{ $sessionBooking->user->name }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">الجلسة</dt>
                <dd class="font-medium text-gray-900">{{ $sessionBooking->trainingSession->title }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">الموعد</dt>
                <dd class="font-medium text-gray-900">{{ $sessionBooking->formatted_booking_datetime }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">مكان/نوع الجلسة</dt>
                <dd class="font-medium text-gray-900">{{ $sessionBooking->trainingSession->location ?: $sessionBooking->trainingSession->session_type }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">تم الإنشاء</dt>
                <dd class="font-medium text-gray-900">{{ $sessionBooking->created_at?->format('Y-m-d H:i') }}</dd>
            </div>
        </dl>
    </div>
</div>
@endsection
@extends('layouts.admin')

@section('title', 'تعديل الحجز')
@section('header', 'تعديل حجز جلسة التدريب')

@section('header_actions')
<div class="flex gap-2">
    @if($sessionBooking->canBeCancelled())
        <a href="{{ route('training-sessions.reschedule-form', $sessionBooking) }}" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">إعادة جدولة</a>
    @endif
    <a href="{{ route('admin.session-bookings.index') }}" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">العودة للحجوزات</a>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white shadow rounded-lg p-6">
        <form method="POST" action="{{ route('admin.session-bookings.update', $sessionBooking) }}" class="space-y-6">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">حالة الحجز</label>
                    <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach(['pending' => 'في الانتظار', 'confirmed' => 'مؤكد', 'completed' => 'مكتمل', 'cancelled' => 'ملغي'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $sessionBooking->status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">حالة الدفع</label>
                    <select name="payment_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach(['pending' => 'في الانتظار', 'paid' => 'مدفوع', 'failed' => 'فشل', 'refunded' => 'مسترد'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('payment_status', $sessionBooking->payment_status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">حالة الحضور</label>
                    <select name="attendance_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach(['scheduled' => 'مجدول', 'attended' => 'حضر', 'missed' => 'لم يحضر', 'late_cancelled' => 'إلغاء متأخر'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('attendance_status', $sessionBooking->attendance_status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">المبلغ</label>
                    <input type="text" disabled value="{{ number_format($sessionBooking->payment_amount, 2) }} ريال" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">ملاحظات</label>
                <textarea name="notes" rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $sessionBooking->notes) }}</textarea>
            </div>

            <div class="flex justify-end gap-3">
                @if($sessionBooking->canBeCancelled())
                    <form method="POST" action="{{ route('training-sessions.cancel', $sessionBooking) }}" onsubmit="return confirm('إلغاء هذا الحجز؟');">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100">إلغاء الحجز</button>
                    </form>
                @endif
                <button type="submit" class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">حفظ التعديلات</button>
            </div>
        </form>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900">ملخص الحجز</h3>
        <dl class="mt-4 space-y-3 text-sm">
            <div>
                <dt class="text-gray-500">العميل</dt>
                <dd class="font-medium text-gray-900">{{ $sessionBooking->user->name }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">الجلسة</dt>
                <dd class="font-medium text-gray-900">{{ $sessionBooking->trainingSession->title }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">الموعد</dt>
                <dd class="font-medium text-gray-900">{{ $sessionBooking->formatted_booking_datetime }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">مكان/نوع الجلسة</dt>
                <dd class="font-medium text-gray-900">{{ $sessionBooking->trainingSession->location ?: $sessionBooking->trainingSession->session_type }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">تم الإنشاء</dt>
                <dd class="font-medium text-gray-900">{{ $sessionBooking->created_at?->format('Y-m-d H:i') }}</dd>
            </div>
        </dl>
    </div>
</div>
@endsection
