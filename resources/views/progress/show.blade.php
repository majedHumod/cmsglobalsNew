@extends('layouts.admin')

@section('title', 'تفاصيل Check-in')
@section('header', 'تفاصيل Check-in للعميل: ' . $checkIn->user->name)

@section('content')
<div class="space-y-6">
    <div class="bg-white shadow rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
            <div>
                <div class="text-gray-500">التاريخ</div>
                <div class="mt-1 font-medium text-gray-900">{{ $checkIn->checked_in_at?->format('Y-m-d H:i') }}</div>
            </div>
            <div>
                <div class="text-gray-500">المدرب</div>
                <div class="mt-1 font-medium text-gray-900">{{ $checkIn->coach->name ?? 'غير محدد' }}</div>
            </div>
            <div>
                <div class="text-gray-500">تم الإرسال بواسطة</div>
                <div class="mt-1 font-medium text-gray-900">{{ $checkIn->submittedBy->name ?? 'غير محدد' }}</div>
            </div>
            <div>
                <div class="text-gray-500">الوزن</div>
                <div class="mt-1 font-medium text-gray-900">{{ $checkIn->weight ?? '—' }} كجم</div>
            </div>
            <div>
                <div class="text-gray-500">نسبة الدهون</div>
                <div class="mt-1 font-medium text-gray-900">{{ $checkIn->body_fat_percentage ?? '—' }}%</div>
            </div>
            <div>
                <div class="text-gray-500">مستوى الطاقة</div>
                <div class="mt-1 font-medium text-gray-900">{{ $checkIn->energy_level ?? '—' }}/10</div>
            </div>
            <div>
                <div class="text-gray-500">الالتزام بالتمرين</div>
                <div class="mt-1 font-medium text-gray-900">{{ $checkIn->training_adherence ?? '—' }}/10</div>
            </div>
            <div>
                <div class="text-gray-500">الالتزام الغذائي</div>
                <div class="mt-1 font-medium text-gray-900">{{ $checkIn->nutrition_adherence ?? '—' }}/10</div>
            </div>
            <div>
                <div class="text-gray-500">متوسط الالتزام</div>
                <div class="mt-1 font-medium text-gray-900">{{ $checkIn->average_adherence ?? '—' }}/10</div>
            </div>
        </div>
    </div>

    @if($checkIn->progress_photo_path)
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900">صورة التقدم</h3>
            <img src="{{ Storage::url($checkIn->progress_photo_path) }}" alt="Progress photo" class="mt-4 max-w-md rounded-lg shadow">
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900">الملاحظات</h3>
            <p class="mt-4 text-sm text-gray-700 whitespace-pre-line">{{ $checkIn->notes ?: 'لا توجد ملاحظات.' }}</p>
        </div>
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900">تغذية راجعة وخطوات قادمة</h3>
            <p class="mt-4 text-sm text-gray-700 whitespace-pre-line">{{ $checkIn->coach_feedback ?: 'لا توجد تغذية راجعة.' }}</p>
            <div class="mt-4 border-t border-gray-200 pt-4 text-sm text-gray-700 whitespace-pre-line">{{ $checkIn->next_steps ?: 'لا توجد خطوات قادمة.' }}</div>
        </div>
    </div>
</div>
@endsection
