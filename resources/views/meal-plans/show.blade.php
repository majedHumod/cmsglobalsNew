@extends('layouts.app')

@section('title', 'تفاصيل الوجبة: ' . $mealPlan->name)

@section('content')
<!-- Page Header -->
<div class="bg-white shadow-sm">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">تفاصيل الوجبة: {{ $mealPlan->name }}</h1>
                <nav class="flex mt-2" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3 space-x-reverse">
                        <li class="inline-flex items-center">
                            <a href="{{ route('home') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-indigo-600">
                                <svg class="w-4 h-4 ml-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                                </svg>
                                الرئيسية
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <a href="{{ route('meal-plans.public') }}" class="ml-1 md:ml-2 text-sm font-medium text-gray-700 hover:text-indigo-600">الجداول الغذائية</a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-1 md:ml-2 text-sm font-medium text-gray-500">{{ $mealPlan->name }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex space-x-2 space-x-reverse">
                @if(auth()->user() && (auth()->user()->hasRole('admin') || $mealPlan->user_id === auth()->id()))
                    <a href="{{ route('meal-plans.edit', $mealPlan) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        تعديل
                    </a>
                @endif
                <a href="{{ route('meal-plans.public') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    العودة للقائمة
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
<!-- Main Content Container - Unified Layout -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
    <article>
        <!-- Meal Plan Image - Moved to top for better layout -->
        @if($mealPlan->image)
            <div class="w-full h-64 md:h-80 mb-8 rounded-lg overflow-hidden">
                <img src="{{ Storage::url($mealPlan->image) }}" alt="{{ $mealPlan->name }}" class="w-full h-full object-cover" loading="lazy" decoding="async">
            </div>
        @endif

        <header class="mb-8">
            <div class="flex justify-between items-start mb-4">
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900">{{ $mealPlan->name }}</h1>
            </div>
            
            <!-- Meal Type and Status Badges -->
            <div class="flex items-center space-x-4 space-x-reverse mb-6">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                    {{ $mealPlan->meal_type_name }}
                </span>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                    {{ $mealPlan->difficulty_name }}
                </span>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $mealPlan->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $mealPlan->is_active ? 'نشط' : 'غير نشط' }}
                </span>
            </div>
            
            @if($mealPlan->description)
                <p class="text-xl text-gray-600 leading-relaxed mb-6">{{ $mealPlan->description }}</p>
            @endif

            <!-- Meal Plan Details Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
                @if($mealPlan->calories)
                    <div class="bg-green-50 p-4 rounded-lg text-center">
                        <div class="text-2xl font-bold text-green-600">{{ $mealPlan->calories }}</div>
                        <div class="text-sm text-gray-600">سعرة حرارية</div>
                    </div>
                @endif
                
                @if($mealPlan->total_time > 0)
                    <div class="bg-blue-50 p-4 rounded-lg text-center">
                        <div class="text-2xl font-bold text-blue-600">{{ $mealPlan->total_time }}</div>
                        <div class="text-sm text-gray-600">دقيقة</div>
                    </div>
                @endif
                
                <div class="bg-purple-50 p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ $mealPlan->servings }}</div>
                    <div class="text-sm text-gray-600">حصة</div>
                </div>
                
                @if($mealPlan->prep_time)
                    <div class="bg-orange-50 p-4 rounded-lg text-center">
                        <div class="text-2xl font-bold text-orange-600">{{ $mealPlan->prep_time }}</div>
                        <div class="text-sm text-gray-600">وقت التحضير</div>
                    </div>
                @endif
            </div>

            <!-- Nutrition Information -->
            @if($mealPlan->protein || $mealPlan->carbs || $mealPlan->fats)
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">المعلومات الغذائية</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @if($mealPlan->protein)
                            <div class="bg-red-50 p-4 rounded-lg text-center">
                                <div class="text-2xl font-bold text-red-600">{{ $mealPlan->protein }}ج</div>
                                <div class="text-sm text-gray-600">بروتين</div>
                            </div>
                        @endif
                        
                        @if($mealPlan->carbs)
                            <div class="bg-orange-50 p-4 rounded-lg text-center">
                                <div class="text-2xl font-bold text-orange-600">{{ $mealPlan->carbs }}ج</div>
                                <div class="text-sm text-gray-600">كربوهيدرات</div>
                            </div>
                        @endif
                        
                        @if($mealPlan->fats)
                            <div class="bg-yellow-50 p-4 rounded-lg text-center">
                                <div class="text-2xl font-bold text-yellow-600">{{ $mealPlan->fats }}ج</div>
                                <div class="text-sm text-gray-600">دهون</div>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Macro Percentages -->
                    @if($mealPlan->total_macros > 0)
                        <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-sm font-medium text-gray-900 mb-3">توزيع المغذيات الكبرىببب</h3>
                            <div class="space-y-2">
                                @if($mealPlan->protein)
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">البروتين</span>
                                        <div class="flex items-center">
                                            <div class="w-32 bg-gray-200 rounded-full h-2 mr-2">
                                                <div class="bg-red-500 h-2 rounded-full" style="width: {{ isset($mealPlan->macro_percentages['protein']) ? $mealPlan->macro_percentages['protein'] : round(($mealPlan->protein * 4 / $mealPlan->calories) * 100) }}%"></div>
                                            </div>
                                            <span class="text-sm font-medium">{{ isset($mealPlan->macro_percentages['protein']) ? $mealPlan->macro_percentages['protein'] : round(($mealPlan->protein * 4 / $mealPlan->calories) * 100) }}%</span>
                                        </div>
                                    </div>
                                @endif
                                
                                @if($mealPlan->carbs)
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">الكربوهيدرات</span>
                                        <div class="flex items-center">
                                            <div class="w-32 bg-gray-200 rounded-full h-2 mr-2">
                                                <div class="bg-orange-500 h-2 rounded-full" style="width: {{ isset($mealPlan->macro_percentages['carbs']) ? $mealPlan->macro_percentages['carbs'] : round(($mealPlan->carbs * 4 / $mealPlan->calories) * 100) }}%"></div>
                                            </div>
                                            <span class="text-sm font-medium">{{ isset($mealPlan->macro_percentages['carbs']) ? $mealPlan->macro_percentages['carbs'] : round(($mealPlan->carbs * 4 / $mealPlan->calories) * 100) }}%</span>
                                        </div>
                                    </div>
                                @endif
                                
                                @if($mealPlan->fats)
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">الدهون</span>
                                        <div class="flex items-center">
                                            <div class="w-32 bg-gray-200 rounded-full h-2 mr-2">
                                                <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ isset($mealPlan->macro_percentages['fats']) ? $mealPlan->macro_percentages['fats'] : round(($mealPlan->fats * 9 / $mealPlan->calories) * 100) }}%"></div>
                                            </div>
                                            <span class="text-sm font-medium">{{ isset($mealPlan->macro_percentages['fats']) ? $mealPlan->macro_percentages['fats'] : round(($mealPlan->fats * 9 / $mealPlan->calories) * 100) }}%</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </header>

        <!-- Ingredients and Instructions Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- المكونات -->
            <div class="bg-gray-50 p-6 rounded-lg">
                <h2 class="text-xl font-bold text-gray-900 mb-4">المكونات</h2>
                <ul class="space-y-3">
                    @foreach(explode("\n", $mealPlan->ingredients) as $ingredient)
                        @if(trim($ingredient))
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-700">{{ trim($ingredient) }}</span>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
            
            <!-- معلومات الوجبة -->
            <div class="bg-gray-50 p-6 rounded-lg">
                <h2 class="text-xl font-bold text-gray-900 mb-4">معلومات الوجبة</h2>
                <dl class="space-y-4">
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">المؤلف:</dt>
                        <dd class="text-sm text-gray-900 font-medium">{{ $mealPlan->user->name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">تاريخ الإنشاء:55</dt>
                        <dd class="text-sm text-gray-900">{{ $mealPlan->created_at ? $mealPlan->created_at->format('d/m/Y H:i') : '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">آخر تحديث:</dt>
                        <dd class="text-sm text-gray-900">{{ $mealPlan->updated_at ? $mealPlan->updated_at->format('d/m/Y H:i') : '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">يمكن تعديلها:</dt>
                        <dd class="text-sm text-gray-900">{{ (auth()->user()->hasRole('admin') || $mealPlan->user_id === auth()->id()) ? '✅ نعم' : '❌ لا' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- طريقة التحضير -->
        @if($mealPlan->instructions)
            <div class="bg-gray-50 p-6 rounded-lg">
                <h2 class="text-xl font-bold text-gray-900 mb-4">طريقة التحضير</h2>
                <div class="prose max-w-none text-right text-gray-700 leading-relaxed">
                    {!! nl2br(e($mealPlan->instructions)) !!}
                </div>
            </div>
        @else
            <!-- لا توجد تعليمات -->
            <div class="bg-gray-50 p-6 rounded-lg text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-gray-200 mb-4">
                    <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">لا توجد تعليمات تحضير</h3>
                <p class="text-sm text-gray-500 mb-6">لم يتم إضافة تعليمات تحضير لهذه الوجبة بعد.</p>
                
                @if(auth()->user()->hasRole('admin') || $mealPlan->user_id === auth()->id())
                    <a href="{{ route('meal-plans.edit', $mealPlan) }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        إضافة تعليمات التحضير
                    </a>
                @endif
            </div>
        @endif
    </article>
</div>
</div>

<!-- Footer -->
@include('layouts.footer')

@endsection

