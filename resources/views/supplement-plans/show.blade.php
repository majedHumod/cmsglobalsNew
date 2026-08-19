@extends('layouts.admin')

@section('title', $supplementPlan->name)

@section('header', $supplementPlan->name)

@section('header_actions')
<div class="flex gap-2">
    @if($supplementPlan->canManage(auth()->user()))
    <a href="{{ route('supplement-plans.edit', $supplementPlan) }}"
       class="inline-flex items-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium rounded-md shadow-sm">
        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
        تعديل
    </a>
    @endif
    <a href="{{ route('supplement-plans.index') }}"
       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
        <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        العودة للقائمة
    </a>
</div>
@endsection

@section('content')
<div class="max-w-3xl">
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        {{-- الصورة --}}
        @if($supplementPlan->image)
        <div class="h-56 bg-gray-100 overflow-hidden">
            <img src="{{ Storage::url($supplementPlan->image) }}" alt="{{ $supplementPlan->name }}"
                 class="w-full h-full object-cover">
        </div>
        @endif

        <div class="p-6 space-y-6">
            {{-- الشارات --}}
            <div class="flex flex-wrap gap-2">
                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-indigo-100 text-indigo-800">
                    {{ $supplementPlan->supplement_type_name }}
                </span>
                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-purple-100 text-purple-800">
                    {{ $supplementPlan->timing_name }}
                </span>
                @if($supplementPlan->brand)
                <span class="inline-flex px-3 py-1 text-sm rounded-full bg-gray-100 text-gray-700">
                    {{ $supplementPlan->brand }}
                </span>
                @endif
                <span class="inline-flex px-3 py-1 text-sm rounded-full bg-blue-100 text-blue-700">
                    {{ $supplementPlan->audience_gender_label }}
                </span>
                @if($supplementPlan->is_active)
                <span class="inline-flex px-3 py-1 text-sm rounded-full bg-green-100 text-green-800">نشط</span>
                @else
                <span class="inline-flex px-3 py-1 text-sm rounded-full bg-red-100 text-red-700">معطّل</span>
                @endif
            </div>

            {{-- الجرعة --}}
            @if($supplementPlan->dosage)
            <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-4">
                <p class="text-xs font-semibold text-indigo-600 mb-1 uppercase tracking-wide">الجرعة الموصى بها</p>
                <p class="text-indigo-900 text-xl font-bold">{{ $supplementPlan->dosage }}</p>
            </div>
            @endif

            {{-- الوصف --}}
            @if($supplementPlan->description)
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">الوصف</h3>
                <p class="text-gray-700 leading-relaxed text-sm">{{ $supplementPlan->description }}</p>
            </div>
            @endif

            {{-- التعليمات --}}
            @if($supplementPlan->instructions)
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">طريقة الاستخدام</h3>
                <div class="bg-green-50 border border-green-100 rounded-lg p-4 text-green-900 text-sm whitespace-pre-line leading-relaxed">
                    {{ $supplementPlan->instructions }}
                </div>
            </div>
            @endif

            {{-- التحذيرات --}}
            @if($supplementPlan->warnings)
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">تحذيرات وملاحظات</h3>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-yellow-900 text-sm whitespace-pre-line leading-relaxed">
                    <span class="font-bold">⚠️ تنبيه: </span>{{ $supplementPlan->warnings }}
                </div>
            </div>
            @endif

            {{-- حذف --}}
            @if($supplementPlan->canManage(auth()->user()))
            <div class="pt-4 border-t border-gray-200">
                <form method="POST" action="{{ route('supplement-plans.destroy', $supplementPlan) }}"
                      onsubmit="return confirm('هل أنت متأكد من حذف هذا المكمل نهائياً؟')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md">
                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        حذف المكمل
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
