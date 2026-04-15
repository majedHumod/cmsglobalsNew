@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">خصومات المراكز الغذائية</h1>
        <p class="text-lg text-gray-600">عروض خاصة من مراكزنا الغذائية الشريكة</p>
    </div>

    @if($discounts->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($discounts as $discount)
                <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition duration-300 transform hover:-translate-y-1">
                    @if($discount->image)
                        <div class="relative">
                            <img src="{{ $discount->image_url }}" alt="{{ $discount->name }}" 
                                 class="w-full h-64 object-cover">
                            <div class="absolute top-4 right-4">
                                <span class="bg-green-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                                    {{ $discount->discount_percentage }}% OFF
                                </span>
                            </div>
                        </div>
                    @else
                        <div class="w-full h-64 bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center relative">
                            <div class="text-white text-center">
                                <div class="text-6xl font-bold mb-2">{{ $discount->discount_percentage }}%</div>
                                <div class="text-xl">OFF</div>
                            </div>
                            <div class="absolute top-4 right-4">
                                <span class="bg-white text-gray-800 px-3 py-1 rounded-full text-sm font-semibold">
                                    {{ $discount->discount_percentage }}% OFF
                                </span>
                            </div>
                        </div>
                    @endif
                    
                    <div class="p-6">
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">{{ $discount->name }}</h3>
                        
                        <div class="flex items-center justify-between mb-4">
                            <div class="text-3xl font-bold text-green-600">
                                {{ $discount->discount_percentage }}%
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-gray-600">خصم</div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 rounded-lg p-4 mb-4">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600">صالح من:</span>
                                <span class="font-semibold">{{ $discount->start_date->format('Y/m/d') }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm mt-2">
                                <span class="text-gray-600">صالح حتى:</span>
                                <span class="font-semibold">{{ $discount->end_date->format('Y/m/d') }}</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-center">
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                <svg class="w-4 h-4 ml-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                نشط الآن
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-16">
            <div class="text-gray-500 text-xl mb-4">لا توجد خصومات نشطة متاحة في الوقت الحالي.</div>
            <p class="text-gray-400">تحقق لاحقاً للحصول على عروض جديدة!</p>
        </div>
    @endif
</div>
@endsection
