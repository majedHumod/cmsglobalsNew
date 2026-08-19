@extends('layouts.admin')

@section('title', 'خطط المكملات الغذائية')

@section('header', 'خطط المكملات الغذائية الخاصة بك')

@section('content')
@if($supplementPlans->isEmpty())
    <div class="bg-white shadow-md rounded-lg p-12 text-center">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
        </svg>
        <p class="text-gray-500 text-lg">لا توجد خطط مكملات متاحة لك حالياً</p>
        <p class="text-gray-400 text-sm mt-1">تواصل مع مدربك لإضافة خطط المكملات الخاصة بك</p>
    </div>
@else
    @php
        $typeNames = [
            'protein'      => 'بروتين',
            'vitamins'     => 'فيتامينات',
            'minerals'     => 'معادن',
            'pre_workout'  => 'ما قبل التمرين',
            'post_workout' => 'ما بعد التمرين',
            'omega'        => 'أوميغا',
            'general'      => 'عام',
        ];
        $typeIcons = [
            'protein'      => '🥩',
            'vitamins'     => '💊',
            'minerals'     => '🪨',
            'pre_workout'  => '⚡',
            'post_workout' => '🔄',
            'omega'        => '🐟',
            'general'      => '📦',
        ];
    @endphp

    @foreach($supplementPlans as $type => $plans)
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-4">
            <span class="text-2xl">{{ $typeIcons[$type] ?? '💊' }}</span>
            <h3 class="text-lg font-bold text-gray-800">{{ $typeNames[$type] ?? $type }}</h3>
            <span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded-full">{{ $plans->count() }} عنصر</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($plans as $plan)
            <div class="bg-white shadow-md rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-200">
                {{-- الصورة --}}
                @if($plan->image)
                <div class="h-40 overflow-hidden bg-gray-100">
                    <img src="{{ Storage::url($plan->image) }}" alt="{{ $plan->name }}"
                         class="w-full h-full object-cover">
                </div>
                @else
                <div class="h-24 bg-gradient-to-br from-indigo-50 to-purple-50 flex items-center justify-center">
                    <span class="text-4xl">{{ $typeIcons[$type] ?? '💊' }}</span>
                </div>
                @endif

                <div class="p-4">
                    <h4 class="font-bold text-gray-900 text-base mb-1">{{ $plan->name }}</h4>
                    @if($plan->brand)
                        <p class="text-xs text-gray-400 mb-2">{{ $plan->brand }}</p>
                    @endif

                    @if($plan->dosage)
                    <div class="bg-indigo-50 border border-indigo-100 rounded-lg px-3 py-2 mb-3">
                        <p class="text-xs text-indigo-600 font-semibold mb-0.5">الجرعة</p>
                        <p class="text-indigo-900 font-bold text-sm">{{ $plan->dosage }}</p>
                    </div>
                    @endif

                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded-full font-medium">
                            {{ $plan->timing_name }}
                        </span>
                    </div>

                    @if($plan->description)
                    <p class="text-sm text-gray-600 line-clamp-2 mb-3">{{ $plan->description }}</p>
                    @endif

                    <a href="{{ route('supplement-plans.show', $plan) }}"
                       class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                        عرض التفاصيل
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
@endif
@endsection
