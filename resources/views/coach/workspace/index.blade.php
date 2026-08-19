@extends('layouts.admin')

@section('title', 'مساحة عمل المدرب')
@section('header', 'مساحة عمل المدرب')

@section('header_actions')
    <a href="{{ route('coach.clients.index') }}" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">كل العملاء</a>
    <a href="{{ route('coach-availabilities.index') }}" class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">إدارة التوفر</a>
@endsection

@section('content')
@php
    $reasonLabels = [
        'checkin_overdue' => 'Check-in متأخر',
        'low_compliance' => 'التزام تمارين منخفض',
        'low_habits' => 'عادات منخفضة',
        'low_nutrition' => 'التزام غذائي منخفض',
        'expiring_soon' => 'عضوية تنتهي قريباً',
    ];
    $priorityClasses = [
        'high' => 'bg-red-100 text-red-800',
        'medium' => 'bg-amber-100 text-amber-800',
        'low' => 'bg-emerald-100 text-emerald-800',
    ];
@endphp

<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
        <div class="bg-white shadow rounded-lg p-5">
            <div class="text-sm text-gray-500">إجمالي العملاء</div>
            <div class="mt-2 text-3xl font-bold text-indigo-600">{{ $summary['clients'] }}</div>
        </div>
        <div class="bg-white shadow rounded-lg p-5">
            <div class="text-sm text-gray-500">معرّضون للانقطاع</div>
            <div class="mt-2 text-3xl font-bold text-red-600">{{ $summary['atRiskCount'] }}</div>
        </div>
        <div class="bg-white shadow rounded-lg p-5">
            <div class="text-sm text-gray-500">Check-in متأخر</div>
            <div class="mt-2 text-3xl font-bold text-orange-600">{{ $summary['clientsNeedingCheckIn'] }}</div>
        </div>
        <div class="bg-white shadow rounded-lg p-5">
            <div class="text-sm text-gray-500">التزام تمارين &lt; 50%</div>
            <div class="mt-2 text-3xl font-bold text-yellow-600">{{ $summary['clientsLowWorkoutCompliance'] }}</div>
        </div>
        <div class="bg-white shadow rounded-lg p-5">
            <div class="text-sm text-gray-500">التزام غذائي منخفض</div>
            <div class="mt-2 text-3xl font-bold text-teal-600">{{ $summary['clientsLowNutrition'] ?? 0 }}</div>
        </div>
        <div class="bg-white shadow rounded-lg p-5">
            <div class="text-sm text-gray-500">حجوزات قادمة</div>
            <div class="mt-2 text-3xl font-bold text-blue-600">{{ $summary['upcomingBookings'] }}</div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-4">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('coach.workspace') }}" class="px-3 py-1.5 rounded-full text-sm {{ empty($filter) ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }}">الكل</a>
            <a href="{{ route('coach.workspace', ['filter' => 'checkin_overdue']) }}" class="px-3 py-1.5 rounded-full text-sm {{ $filter === 'checkin_overdue' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }}">Check-in متأخر</a>
            <a href="{{ route('coach.workspace', ['filter' => 'low_compliance']) }}" class="px-3 py-1.5 rounded-full text-sm {{ $filter === 'low_compliance' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }}">التزام منخفض</a>
            <a href="{{ route('coach.workspace', ['filter' => 'low_nutrition']) }}" class="px-3 py-1.5 rounded-full text-sm {{ $filter === 'low_nutrition' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }}">غذاء منخفض</a>
            <a href="{{ route('coach.workspace', ['filter' => 'expiring']) }}" class="px-3 py-1.5 rounded-full text-sm {{ $filter === 'expiring' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }}">عضوية تنتهي</a>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">عملاء يحتاجون متابعة</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">العميل</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">درجة المخاطر</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">التزام التمارين</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">الأسباب</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($atRiskClients as $client)
                        <tr>
                            <td class="px-4 py-4">
                                <div class="font-medium text-gray-900">{{ $client['name'] }}</div>
                                <div class="text-sm text-gray-500">{{ $client['email'] }}</div>
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold {{ $priorityClasses[$client['priority']] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ $client['risk_score'] }}%
                                </span>
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-700">{{ $client['workout_completion_rate'] }}%</td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($client['risk_reasons'] as $reason)
                                        <span class="inline-flex px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-700">{{ $reasonLabels[$reason] ?? $reason }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-4 text-sm font-medium">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ $client['profile_url'] }}" class="text-indigo-600 hover:text-indigo-800">الملف</a>
                                    <a href="{{ $client['progress_url'] }}" class="text-green-600 hover:text-green-800">التقدم</a>
                                    <form method="POST" action="{{ route('coach.clients.remind', $client['user_id']) }}">
                                        @csrf
                                        <button type="submit" class="text-amber-600 hover:text-amber-800">تذكير</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">لا يوجد عملاء معرّضون للانقطاع حالياً.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
