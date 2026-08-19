@extends('layouts.admin')

@section('title', 'إدارة حجوزات جلسات التدريب')

@section('header', 'إدارة حجوزات جلسات التدريب')

@section('header_actions')
<div class="flex space-x-2">
    <a href="{{ route('admin.training-sessions.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
        <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
        </svg>
        إدارة الجلسات
    </a>
</div>
@endsection

@section('content')
@php
    $attendanceLabels = [
        'scheduled' => 'مجدول',
        'attended' => 'حضر',
        'missed' => 'لم يحضر',
        'late_cancelled' => 'إلغاء متأخر',
    ];
@endphp
<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <div class="p-6">
        <div class="mb-6">
            <h2 class="text-lg font-medium text-gray-900">قائمة حجوزات جلسات التدريب</h2>
            <p class="mt-1 text-sm text-gray-500">إدارة ومتابعة جميع حجوزات جلسات التدريب الخاصة.</p>
        </div>

        @if($bookings->isEmpty())
            <div class="text-center py-12">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-gray-100 mb-4">
                    <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a2 2 0 100-4 2 2 0 000 4zm0 0v4a2 2 0 002 2h6a2 2 0 002-2v-4" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">لا توجد حجوزات</h3>
                <p class="text-sm text-gray-500">لم يتم حجز أي جلسات تدريب بعد.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">العميل</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الجلسة</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">التاريخ والوقت</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المبلغ</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">حالة الحجز</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">حالة الدفع</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحضور</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($bookings as $booking)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full" src="{{ $booking->user->profile_photo_url }}" alt="{{ $booking->user->name }}">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $booking->user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $booking->user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $booking->trainingSession->title }}</div>
                                    <div class="text-sm text-gray-500">{{ $booking->trainingSession->duration_text }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $booking->formatted_booking_datetime }}</div>
                                    @if($booking->is_upcoming)
                                        <div class="text-xs text-green-600">قادم</div>
                                    @else
                                        <div class="text-xs text-gray-500">منتهي</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($booking->payment_amount, 2) }} ريال
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {!! $booking->status_badge !!}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {!! $booking->payment_status_badge !!}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $attendanceLabels[$booking->attendance_status ?? 'scheduled'] ?? ($booking->attendance_status ?? 'scheduled') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('admin.session-bookings.edit', $booking) }}" class="text-indigo-600 hover:text-indigo-900">تعديل</a>
                                        @if($booking->canBeCancelled())
                                            <a href="{{ route('training-sessions.reschedule-form', $booking) }}" class="text-blue-600 hover:text-blue-900">إعادة جدولة</a>
                                        @endif
                                        
                                        @if($booking->canBeCancelled())
                                            <form action="{{ route('admin.session-bookings.update-status', $booking) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('هل أنت متأكد من إلغاء هذا الحجز؟')">
                                                    إلغاء
                                                </button>
                                            </form>
                                        @endif
                                        
                                        <form action="{{ route('admin.session-bookings.destroy', $booking) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('هل أنت متأكد من حذف هذا الحجز؟')">
                                                حذف
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
</div>
@endsection