@extends('layouts.admin')

@section('title', 'إدارة الجداول الغذائية')

@section('header', 'إدارة الجداول الغذائية')

@section('header_actions')
<div class="flex space-x-2">
    <a href="{{ route('meal-plans.public') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
        <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
        </svg>
        عرض الجداول العامة
    </a>
    <a href="{{ route('meal-plans.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        إضافة وجبة جديدة
    </a>
</div>
@endsection

@section('content')
<div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
    <div class="p-6">
        <div class="mb-6">
            <h2 class="text-lg font-medium text-gray-900">قائمة الجداول الغذائية</h2>
            <p class="mt-1 text-sm text-gray-500">إدارة وتنظيم الوجبات والجداول الغذائية من هنا.</p>
            @if(auth()->user()->hasRole('admin'))
                <p class="mt-1 text-xs text-blue-600">عرض جميع الوجبات كمدير</p>
            @endif
        </div>

        <!-- Tabs -->
        <div class="border-b border-gray-200 mb-6">
            <ul class="flex flex-wrap -mb-px" id="mealPlansTabs" role="tablist">
                <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 border-indigo-600 rounded-t-lg text-indigo-600 active" id="all-meals-tab" data-tabs-target="#all-meals" type="button" role="tab" aria-controls="all-meals" aria-selected="true">
                        <svg class="w-5 h-5 mr-2 inline-block" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                        </svg>
                        جميع الوجبات
                    </button>
                </li>
                @if(auth()->user()->hasRole('admin'))
                <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300" id="my-meals-tab" data-tabs-target="#my-meals" type="button" role="tab" aria-controls="my-meals" aria-selected="false">
                        <svg class="w-5 h-5 mr-2 inline-block" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                        </svg>
                        وجباتي فقط
                    </button>
                </li>
                @endif
                <li role="presentation">
                    <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300" id="statistics-tab" data-tabs-target="#statistics" type="button" role="tab" aria-controls="statistics" aria-selected="false">
                        <svg class="w-5 h-5 mr-2 inline-block" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path>
                            <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"></path>
                        </svg>
                        الإحصائيات
                    </button>
                </li>
            </ul>
        </div>

        <!-- Tab Content -->
        <div id="mealPlansTabContent">
            <!-- All Meals Tab -->
            <div class="block" id="all-meals" role="tabpanel" aria-labelledby="all-meals-tab">
                @if($mealPlans->isEmpty())
                    <div class="text-center py-12">
                        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-gray-100 mb-4">
                            <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">لا توجد وجبات</h3>
                        <p class="text-sm text-gray-500 mb-6">ابدأ بإنشاء وجبة جديدة لبناء جدولك الغذائي.</p>
                        <a href="{{ route('meal-plans.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            إضافة وجبة جديدة
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($mealPlans as $mealPlan)
                            <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                                @if($mealPlan->image)
                                    <img src="{{ Storage::url($mealPlan->image) }}" alt="{{ $mealPlan->name }}" class="w-full h-48 object-cover">
                                @else
                                    <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                        <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif
                                
                                <div class="p-6">
                                    <div class="flex items-start justify-between mb-4">
                                        <h3 class="text-lg font-semibold text-gray-900 line-clamp-2">{{ $mealPlan->name }}</h3>
                                        @if(auth()->user()->hasRole('admin') || $mealPlan->user_id === auth()->id())
                                            <div class="flex space-x-1 ml-2">
                                                <a href="{{ route('meal-plans.edit', $mealPlan) }}" class="text-indigo-600 hover:text-indigo-900 p-1" title="تعديل">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </a>
                                                <form action="{{ route('meal-plans.destroy', $mealPlan) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 p-1" onclick="return confirm('هل أنت متأكد من حذف هذه الوجبة؟')" title="حذف">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- نوع الوجبة والصعوبة -->
                                    <div class="flex items-center space-x-2 mb-3">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ $mealPlan->meal_type_name }}
                                        </span>
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            {{ $mealPlan->difficulty_name }}
                                        </span>
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $mealPlan->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $mealPlan->is_active ? 'نشط' : 'غير نشط' }}
                                        </span>
                                    </div>
                                    
                                    @if($mealPlan->description)
                                        <p class="text-gray-600 text-sm mb-4 line-clamp-3">{{ Str::limit($mealPlan->description, 150) }}</p>
                                    @endif
                                    
                                    <!-- معلومات الوجبة -->
                                    <div class="space-y-2 mb-4">
                                        @if($mealPlan->calories)
                                            <div class="flex items-center justify-between text-sm">
                                                <span class="text-gray-500">السعرات:</span>
                                                <span class="font-medium">{{ $mealPlan->calories }} سعرة</span>
                                            </div>
                                        @endif
                                        @if($mealPlan->protein)
                                            <div class="flex items-center justify-between text-sm">
                                                <span class="text-gray-500">البروتين:</span>
                                                <span class="font-medium">{{ $mealPlan->protein }}ج</span>
                                            </div>
                                        @endif
                                        @if($mealPlan->carbs)
                                            <div class="flex items-center justify-between text-sm">
                                                <span class="text-gray-500">الكربوهيدرات:</span>
                                                <span class="font-medium">{{ $mealPlan->carbs }}ج</span>
                                            </div>
                                        @endif
                                        @if($mealPlan->fats)
                                            <div class="flex items-center justify-between text-sm">
                                                <span class="text-gray-500">الدهون:</span>
                                                <span class="font-medium">{{ $mealPlan->fats }}ج</span>
                                            </div>
                                        @endif
                                        @if($mealPlan->total_time > 0)
                                            <div class="flex items-center justify-between text-sm">
                                                <span class="text-gray-500">الوقت الكلي:</span>
                                                <span class="font-medium">{{ $mealPlan->total_time }} دقيقة</span>
                                            </div>
                                        @endif
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-gray-500">عدد الحصص:</span>
                                            <span class="font-medium">{{ $mealPlan->servings }} حصة</span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center justify-between text-xs text-gray-500 mb-4">
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ $mealPlan->user->name }}
                                        </span>
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ $mealPlan->created_at ? $mealPlan->created_at->format('d/m/Y') : '—' }}
                                        </span>
                                    </div>
                                    
                                    @if(auth()->user()->hasRole('admin') || $mealPlan->user_id === auth()->id())
                                        <div class="flex space-x-2">
                                            <a href="{{ route('meal-plans.show', $mealPlan) }}" class="flex-1 text-center bg-blue-600 hover:bg-blue-700 text-white text-sm py-2 px-3 rounded-md transition-colors">
                                                عرض
                                            </a>
                                            <a href="{{ route('meal-plans.edit', $mealPlan) }}" class="flex-1 text-center bg-yellow-600 hover:bg-yellow-700 text-white text-sm py-2 px-3 rounded-md transition-colors">
                                                تعديل
                                            </a>
                                            <form action="{{ route('meal-plans.destroy', $mealPlan) }}" method="POST" class="flex-1">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white text-sm py-2 px-3 rounded-md transition-colors" onclick="return confirm('هل أنت متأكد من حذف هذه الوجبة؟')">
                                                    حذف
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            
            <!-- My Meals Tab (Admin Only) -->
            @if(auth()->user()->hasRole('admin'))
            <div class="hidden" id="my-meals" role="tabpanel" aria-labelledby="my-meals-tab">
                @php
                    $myMeals = $mealPlans->where('user_id', auth()->id());
                @endphp
                
                @if($myMeals->isEmpty())
                    <div class="text-center py-12">
                        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-gray-100 mb-4">
                            <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">لا توجد وجبات شخصية</h3>
                        <p class="text-sm text-gray-500 mb-6">ابدأ بإنشاء وجبة جديدة.</p>
                        <a href="{{ route('meal-plans.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            إضافة وجبة جديدة
                        </a>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">اسم الوجبة</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">النوع</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">السعرات</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">البروتين</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الكربوهيدرات</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الدهون</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحصص</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاريخ الإنشاء</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($myMeals as $mealPlan)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $mealPlan->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $mealPlan->difficulty_name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ $mealPlan->meal_type_name }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $mealPlan->calories ?? '—' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $mealPlan->protein ?? '—' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $mealPlan->carbs ?? '—' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $mealPlan->fats ?? '—' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $mealPlan->servings }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $mealPlan->created_at ? $mealPlan->created_at->format('Y-m-d H:i') : '—' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('meal-plans.show', $mealPlan) }}" class="text-blue-600 hover:text-blue-900">عرض</a>
                                                <a href="{{ route('meal-plans.edit', $mealPlan) }}" class="text-indigo-600 hover:text-indigo-900">تعديل</a>
                                                <form action="{{ route('meal-plans.destroy', $mealPlan) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('هل أنت متأكد من حذف هذه الوجبة؟')">
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
                @endif
            </div>
            @endif
            
            <!-- Statistics Tab -->
            <div class="hidden" id="statistics" role="tabpanel" aria-labelledby="statistics-tab">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-blue-50 rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">إجمالي الوجبات</dt>
                                    <dd class="text-lg font-semibold text-gray-900">{{ $mealPlans->count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class="bg-green-50 rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">وجباتي</dt>
                                    <dd class="text-lg font-semibold text-gray-900">{{ $mealPlans->where('user_id', auth()->id())->count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class="bg-yellow-50 rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">هذا الشهر</dt>
                                    <dd class="text-lg font-semibold text-gray-900">{{ $mealPlans->where('created_at', '>=', now()->startOfMonth())->count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">النشاط الأخير</h3>
                    <div class="space-y-3">
                        @foreach($mealPlans->take(5) as $mealPlan)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-2 h-2 bg-indigo-500 rounded-full mr-3"></div>
                                    <span class="text-sm text-gray-700">تم إنشاء "{{ Str::limit($mealPlan->name, 30) }}"</span>
                                </div>
                                <span class="text-xs text-gray-500">{{ $mealPlan->created_at ? $mealPlan->created_at->diffForHumans() : '—' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tabs functionality
        const tabButtons = document.querySelectorAll('[role="tab"]');
        const tabPanels = document.querySelectorAll('[role="tabpanel"]');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Hide all tab panels
                tabPanels.forEach(panel => {
                    panel.classList.add('hidden');
                });
                
                // Show the selected tab panel
                const panelId = button.getAttribute('data-tabs-target').substring(1);
                document.getElementById(panelId).classList.remove('hidden');
                
                // Update active state for tab buttons
                tabButtons.forEach(btn => {
                    btn.setAttribute('aria-selected', 'false');
                    btn.classList.remove('border-indigo-600', 'text-indigo-600');
                    btn.classList.add('border-transparent', 'hover:text-gray-600', 'hover:border-gray-300');
                });
                
                button.setAttribute('aria-selected', 'true');
                button.classList.remove('border-transparent', 'hover:text-gray-600', 'hover:border-gray-300');
                button.classList.add('border-indigo-600', 'text-indigo-600');
            });
        });
    });
</script>
@endsection