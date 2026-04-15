@extends('layouts.admin')

@section('title', 'الصفحة الرئيسية للوحة التحكم')

@section('header', 'الصفحة الرئيسية للوحة التحكم')

@section('header_actions')
<div class="flex items-center space-x-2 space-x-reverse">
    <a href="{{ route('pages.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        <svg class="-ml-1 ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        إنشاء صفحة جديدة
    </a>
</div>
@endsection

@php
    $bookingStatusLabels = [
        'pending' => 'في الانتظار',
        'confirmed' => 'مؤكد',
        'completed' => 'مكتمل',
        'cancelled' => 'ملغي',
    ];
    $paymentStatusLabels = [
        'pending' => 'في الانتظار',
        'paid' => 'مدفوع',
        'failed' => 'فشل',
        'refunded' => 'مسترد',
    ];
@endphp

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="dashboard-card bg-white shadow rounded-lg p-4 sm:p-6 xl:p-8 border-r-4 border-indigo-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <span class="text-2xl sm:text-3xl leading-none font-bold text-indigo-600">{{ $stats['users'] }}</span>
                <h3 class="text-base font-normal text-gray-500">المستخدمون</h3>
            </div>
            <div class="mr-5 w-0 flex items-center justify-end flex-1">
                <svg class="w-12 h-12 text-gray-300" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="dashboard-card bg-white shadow rounded-lg p-4 sm:p-6 xl:p-8 border-r-4 border-green-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <span class="text-2xl sm:text-3xl leading-none font-bold text-green-600">{{ $stats['session_bookings'] }}</span>
                <h3 class="text-base font-normal text-gray-500">حجوزات الجلسات</h3>
            </div>
            <div class="mr-5 w-0 flex items-center justify-end flex-1">
                <svg class="w-12 h-12 text-gray-300" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="dashboard-card bg-white shadow rounded-lg p-4 sm:p-6 xl:p-8 border-r-4 border-yellow-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <span class="text-2xl sm:text-3xl leading-none font-bold text-yellow-600">{{ $stats['meal_plans'] }}</span>
                <h3 class="text-base font-normal text-gray-500">الخطط الغذائية</h3>
            </div>
            <div class="mr-5 w-0 flex items-center justify-end flex-1">
                <svg class="w-12 h-12 text-gray-300" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="dashboard-card bg-white shadow rounded-lg p-4 sm:p-6 xl:p-8 border-r-4 border-purple-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <span class="text-2xl sm:text-3xl leading-none font-bold text-purple-600">{{ $stats['active_memberships'] }}</span>
                <h3 class="text-base font-normal text-gray-500">اشتراكات عضوية نشطة</h3>
            </div>
            <div class="mr-5 w-0 flex items-center justify-end flex-1">
                <svg class="w-12 h-12 text-gray-300" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Recent subscriptions & Quick Access -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    @role('admin')
    <div class="bg-white shadow rounded-lg p-4 sm:p-6 xl:p-8">
        <div class="mb-4 flex items-center justify-between gap-2 flex-wrap">
            <div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">آخر اشتراكات العضوية</h3>
                <span class="text-base font-normal text-gray-500">أحدث تسجيلات الاشتراك في الموقع</span>
            </div>
            <a href="{{ route('admin.user-memberships.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 rounded-lg p-2 whitespace-nowrap">عرض الكل</a>
        </div>
        <div class="flex flex-col mt-6">
            <div class="overflow-x-auto rounded-lg">
                <div class="align-middle inline-block min-w-full">
                    <div class="shadow overflow-hidden sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="p-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المشترك</th>
                                    <th scope="col" class="p-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">نوع العضوية</th>
                                    <th scope="col" class="p-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">التسجيل</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                @forelse($recentMemberships as $m)
                                    <tr class="{{ $loop->even ? 'bg-gray-50' : '' }}">
                                        <td class="p-4 whitespace-nowrap text-sm font-normal text-gray-900">
                                            {{ $m->user->name ?? '—' }}
                                        </td>
                                        <td class="p-4 whitespace-nowrap text-sm text-gray-700">
                                            {{ $m->membershipType->name ?? '—' }}
                                        </td>
                                        <td class="p-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $m->created_at?->diffForHumans() ?? '—' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="p-4 text-center text-sm text-gray-500">لا توجد اشتراكات حديثة</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endrole

    <div class="bg-white shadow rounded-lg p-4 sm:p-6 xl:p-8 {{ auth()->user()->hasRole('admin') ? '' : 'lg:col-span-2' }}">
        <h3 class="text-xl font-bold text-gray-900 mb-2">الوصول السريع</h3>
        <span class="text-base font-normal text-gray-500">أهم وظائف الإدارة</span>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mt-8">
            @hasanyrole('admin|page_manager')
            <a href="{{ route('pages.index') }}" class="dashboard-card p-4 bg-indigo-50 rounded-lg shadow-sm flex flex-col items-center justify-center hover:bg-indigo-100 text-center min-h-[100px]">
                <svg class="w-8 h-8 text-indigo-600 mb-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-sm font-medium text-gray-900">إدارة الصفحات</span>
            </a>
            @endhasanyrole

            @hasanyrole('admin|user')
            <a href="{{ route('meal-plans.index') }}" class="dashboard-card p-4 bg-yellow-50 rounded-lg shadow-sm flex flex-col items-center justify-center hover:bg-yellow-100 text-center min-h-[100px]">
                <svg class="w-8 h-8 text-yellow-600 mb-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                </svg>
                <span class="text-sm font-medium text-gray-900">الخطط الغذائية</span>
            </a>
            @endhasanyrole

            @role('admin')
            <a href="{{ route('admin.training-sessions.index') }}" class="dashboard-card p-4 bg-blue-50 rounded-lg shadow-sm flex flex-col items-center justify-center hover:bg-blue-100 text-center min-h-[100px]">
                <svg class="w-8 h-8 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                <span class="text-sm font-medium text-gray-900">جلسات التدريب</span>
            </a>

            <a href="{{ route('admin.session-bookings.index') }}" class="dashboard-card p-4 bg-teal-50 rounded-lg shadow-sm flex flex-col items-center justify-center hover:bg-teal-100 text-center min-h-[100px]">
                <svg class="w-8 h-8 text-teal-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a2 2 0 100-4 2 2 0 000 4zm0 0v4a2 2 0 002 2h6a2 2 0 002-2v-4"></path>
                </svg>
                <span class="text-sm font-medium text-gray-900">حجوزات الجلسات</span>
            </a>

            <a href="{{ route('membership-types.index') }}" class="dashboard-card p-4 bg-green-50 rounded-lg shadow-sm flex flex-col items-center justify-center hover:bg-green-100 text-center min-h-[100px]">
                <svg class="w-8 h-8 text-green-600 mb-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"></path>
                </svg>
                <span class="text-sm font-medium text-gray-900">إدارة العضويات</span>
            </a>

            <a href="{{ route('admin.settings.index') }}" class="dashboard-card p-4 bg-gray-50 rounded-lg shadow-sm flex flex-col items-center justify-center hover:bg-gray-100 text-center min-h-[100px]">
                <svg class="w-8 h-8 text-gray-700 mb-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-sm font-medium text-gray-900">إعدادات الموقع</span>
            </a>
            @endrole

            @hasanyrole('admin|user')
            @if(! auth()->user()->hasRole('admin'))
            <a href="{{ route('notes.index') }}" class="dashboard-card p-4 bg-green-50 rounded-lg shadow-sm flex flex-col items-center justify-center hover:bg-green-100 text-center min-h-[100px]">
                <svg class="w-8 h-8 text-green-600 mb-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-sm font-medium text-gray-900">الملاحظات</span>
            </a>
            @endif
            @endhasanyrole
        </div>
    </div>
</div>

@role('admin')
<!-- Booked sessions -->
<div class="bg-white shadow rounded-lg p-4 sm:p-6 xl:p-8">
    <div class="mb-4 flex items-center justify-between gap-2 flex-wrap">
        <div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">الجلسات المحجوزة</h3>
            <span class="text-base font-normal text-gray-500">آخر الحجوزات المسجّلة في النظام</span>
        </div>
        <a href="{{ route('admin.session-bookings.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 rounded-lg p-2 whitespace-nowrap">عرض الكل</a>
    </div>

    <div class="flex flex-col mt-6">
        <div class="overflow-x-auto rounded-lg">
            <div class="align-middle inline-block min-w-full">
                <div class="shadow overflow-hidden sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="p-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الجلسة</th>
                                <th scope="col" class="p-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المحجوز</th>
                                <th scope="col" class="p-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الموعد</th>
                                <th scope="col" class="p-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                                <th scope="col" class="p-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الدفع</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            @forelse($recentBookings as $b)
                                <tr class="{{ $loop->even ? 'bg-gray-50' : '' }}">
                                    <td class="p-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <a href="{{ route('admin.session-bookings.edit', $b) }}" class="text-indigo-600 hover:text-indigo-900">
                                            {{ $b->trainingSession->title ?? 'جلسة' }}
                                        </a>
                                    </td>
                                    <td class="p-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $b->user->name ?? '—' }}
                                    </td>
                                    <td class="p-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $b->formatted_booking_datetime }}
                                    </td>
                                    <td class="p-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $bookingStatusLabels[$b->status] ?? $b->status }}
                                    </td>
                                    <td class="p-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $paymentStatusLabels[$b->payment_status] ?? $b->payment_status }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-4 text-center text-sm text-gray-500">لا توجد حجوزات حديثة</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endrole
@endsection
