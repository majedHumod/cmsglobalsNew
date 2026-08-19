@extends('layouts.client')

@section('title', 'الحجوزات')

@section('content')
<div class="space-y-4" x-data="clientBookings()" x-init="init()">
    <div class="flex items-center justify-between">
        <h1 class="text-lg font-semibold text-slate-900">حجوزاتي</h1>
        <a href="{{ route('client.bookings.create') }}" class="text-sm bg-indigo-600 text-white px-3 py-2 rounded-lg">حجز جديد</a>
    </div>

    <section class="rounded-2xl bg-white p-5 shadow-sm">
        <h2 class="font-semibold text-slate-900 mb-3">القادمة</h2>
        @forelse($upcoming as $booking)
            <div class="border border-slate-200 rounded-xl p-4 mb-3">
                <div class="font-medium">{{ $booking->trainingSession->title ?? 'جلسة' }}</div>
                <div class="text-sm text-slate-500 mt-1">{{ $booking->formatted_booking_datetime }}</div>
                <div class="text-xs text-slate-400 mt-1">{{ $booking->status }}</div>
                <div class="flex flex-wrap gap-2 mt-3">
                    <a href="{{ route('session-bookings.calendar', $booking) }}" class="text-xs px-2 py-1 rounded-lg bg-slate-100 text-slate-700">تقويم</a>
                    @if($booking->canBeCancelled())
                        <form method="POST" action="{{ route('client.bookings.cancel', $booking) }}" onsubmit="return confirm('إلغاء الحجز؟')">
                            @csrf
                            <button type="submit" class="text-xs px-2 py-1 rounded-lg bg-rose-50 text-rose-700">إلغاء</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-500">لا توجد حجوزات قادمة.</p>
        @endforelse
    </section>

    @if($past->isNotEmpty())
        <section class="rounded-2xl bg-white p-5 shadow-sm">
            <h2 class="font-semibold text-slate-900 mb-3">السابقة</h2>
            @foreach($past as $booking)
                <div class="border border-slate-100 rounded-xl p-3 mb-2 text-sm text-slate-600">
                    {{ $booking->trainingSession->title ?? 'جلسة' }} — {{ $booking->formatted_booking_datetime }}
                </div>
            @endforeach
        </section>
    @endif
</div>
@endsection

@push('head')
<script>
    document.addEventListener('alpine:init', () => {
        window.Alpine.data('clientBookings', () => ({
            init() {},
        }));
    });
</script>
@endpush
