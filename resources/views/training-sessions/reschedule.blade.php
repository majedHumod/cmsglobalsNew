@extends('layouts.admin')

@section('title', 'إعادة جدولة الحجز')
@section('header', 'إعادة جدولة الحجز')

@section('content')
<div class="bg-white shadow rounded-lg p-6 max-w-2xl">
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-900">{{ $booking->trainingSession->title ?? 'جلسة تدريب' }}</h3>
        <p class="mt-1 text-sm text-gray-500">الموعد الحالي: {{ $booking->formatted_booking_datetime }}</p>
        <div class="mt-2 flex flex-wrap gap-2">
            @if($booking->video_meeting_url)
                <a href="{{ $booking->video_meeting_url }}" target="_blank" class="text-sm text-indigo-600 hover:text-indigo-800">فتح رابط الاجتماع</a>
            @endif
            <a href="{{ route('session-bookings.calendar', $booking) }}" class="text-sm text-emerald-600 hover:text-emerald-800">تحميل التقويم (ICS)</a>
        </div>
    </div>

    <form method="POST" action="{{ route('training-sessions.reschedule', $booking) }}" class="space-y-6">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="booking_date" class="block text-sm font-medium text-gray-700">التاريخ الجديد</label>
                <input type="date" name="booking_date" id="booking_date" value="{{ old('booking_date', $booking->booking_date?->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>
            <div>
                <label for="booking_time" class="block text-sm font-medium text-gray-700">الوقت الجديد</label>
                <input type="time" name="booking_time" id="booking_time" value="{{ old('booking_time', $booking->booking_time?->format('H:i')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.session-bookings.edit', $booking) }}" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">إلغاء</a>
            <button type="submit" class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">حفظ الموعد الجديد</button>
        </div>
    </form>
</div>
@endsection
