@extends('layouts.admin')

@section('title', 'تفاصيل جدولة التمرين')

@section('header', 'تفاصيل جدولة التمرين')

@section('header_actions')
<div class="flex space-x-2">
    @if($workoutSchedule->canEdit(auth()->user()))
        <a href="{{ route('workout-schedules.edit', $workoutSchedule) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
            تعديل
        </a>
    @endif
    <a href="{{ route('workout-schedules.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
        العودة للجدول
    </a>
</div>
@endsection

@section('content')
<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <div class="p-6 space-y-6">
        <div>
            <h2 class="text-lg font-medium text-gray-900">{{ $workoutSchedule->workout->name ?? 'تمرين غير معروف' }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ $workoutSchedule->week_name }} — {{ $workoutSchedule->session_name }}</p>
        </div>

        <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <dt class="text-sm font-medium text-gray-500">المدة</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $workoutSchedule->workout->duration ?? '—' }} دقيقة</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">الصعوبة</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $workoutSchedule->workout->difficulty_name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">الحالة</dt>
                <dd class="mt-1">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $workoutSchedule->status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $workoutSchedule->status_name }}
                    </span>
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">المدرب</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $workoutSchedule->user->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">الجمهور حسب الجنس</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    @php
                        $genderLabels = ['all' => 'الجميع', 'male' => 'رجال', 'female' => 'نساء'];
                    @endphp
                    {{ $genderLabels[$workoutSchedule->audience_gender ?? 'all'] ?? 'الجميع' }}
                </dd>
            </div>
            <div class="md:col-span-2">
                <dt class="text-sm font-medium text-gray-500">الملاحظات</dt>
                <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ $workoutSchedule->notes ?: 'لا توجد ملاحظات' }}</dd>
            </div>
            @if($workoutSchedule->workout?->description)
                <div class="md:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">وصف التمرين</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $workoutSchedule->workout->description }}</dd>
                </div>
            @endif
        </dl>
    </div>
</div>
@endsection
