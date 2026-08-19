@extends('layouts.admin')

@section('title', 'تعديل الوجبة')

@section('header', 'تعديل الوجبة: ' . $mealPlan->name)

@section('header_actions')
<div class="flex space-x-2">
    <a href="{{ route('meal-plans.show', $mealPlan) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
        <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
        </svg>
        عرض الوجبة
    </a>
    <a href="{{ route('meal-plans.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
        <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        العودة للقائمة
    </a>
</div>
@endsection

@section('content')
<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <div class="p-6">
        <div class="mb-6">
            <h2 class="text-lg font-medium text-gray-900">تعديل الوجبة</h2>
            <p class="mt-1 text-sm text-gray-500">قم بتعديل معلومات ومكونات الوجبة.</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <strong class="font-bold">خطأ في البيانات!</strong>
                        <ul class="mt-2 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('meal-plans.update', $mealPlan) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            
            <!-- معلومات الوجبة الأساسية -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900">معلومات الوجبة الأساسية</h3>
                <p class="mt-1 text-sm text-gray-500">تعديل المعلومات الأساسية للوجبة.</p>
                
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- اسم الوجبة -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">اسم الوجبة (عربي) *</label>
                        <input type="text" name="name" id="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('name', $mealPlan->name) }}" required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="name_en" class="block text-sm font-medium text-gray-700">اسم الوجبة (English)</label>
                        <input type="text" name="name_en" id="name_en" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('name_en', $mealPlan->name_en) }}">
                        @error('name_en')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- نوع الوجبة -->
                    <div>
                        <label for="meal_type" class="block text-sm font-medium text-gray-700">نوع الوجبة *</label>
                        <select name="meal_type" id="meal_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            <option value="">اختر نوع الوجبة</option>
                            <option value="breakfast" {{ old('meal_type', $mealPlan->meal_type) == 'breakfast' ? 'selected' : '' }}>إفطار</option>
                            <option value="lunch" {{ old('meal_type', $mealPlan->meal_type) == 'lunch' ? 'selected' : '' }}>غداء</option>
                            <option value="dinner" {{ old('meal_type', $mealPlan->meal_type) == 'dinner' ? 'selected' : '' }}>عشاء</option>
                            <option value="snack" {{ old('meal_type', $mealPlan->meal_type) == 'snack' ? 'selected' : '' }}>وجبة خفيفة</option>
                        </select>
                        @error('meal_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- وصف الوجبة -->
                <div class="mt-6">
                    <label for="description" class="block text-sm font-medium text-gray-700">وصف الوجبة</label>
                    <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $mealPlan->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <!-- تفاصيل التغذية والوقت -->
            <div class="border-b border-gray-200 py-6">
                <h3 class="text-lg font-medium text-gray-900">تفاصيل التغذية والوقت</h3>
                <p class="mt-1 text-sm text-gray-500">تعديل المعلومات الغذائية وأوقات التحضير.</p>
                
                <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-6">
                    <!-- السعرات الحرارية -->
                    <div>
                        <label for="calories" class="block text-sm font-medium text-gray-700">السعرات الحرارية</label>
                        <input type="number" name="calories" id="calories" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('calories', $mealPlan->calories) }}">
                        @error('calories')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- البروتين -->
                    <div>
                        <label for="protein" class="block text-sm font-medium text-gray-700">البروتين (جرام)</label>
                        <input type="number" name="protein" id="protein" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('protein', $mealPlan->protein) }}" placeholder="25">
                        @error('protein')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- الكربوهيدرات -->
                    <div>
                        <label for="carbs" class="block text-sm font-medium text-gray-700">الكربوهيدرات (جرام)</label>
                        <input type="number" name="carbs" id="carbs" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('carbs', $mealPlan->carbs) }}" placeholder="30">
                        @error('carbs')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- الدهون -->
                    <div>
                        <label for="fats" class="block text-sm font-medium text-gray-700">الدهون (جرام)</label>
                        <input type="number" name="fats" id="fats" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('fats', $mealPlan->fats) }}" placeholder="10">
                        @error('fats')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-4 rounded-md bg-amber-50 border border-amber-200 p-3">
                    <label class="inline-flex items-start gap-2 text-sm text-amber-900">
                        <input type="checkbox" name="nutrition_is_estimated" value="1" class="mt-1 rounded border-amber-300" {{ old('nutrition_is_estimated', $mealPlan->nutrition_is_estimated) ? 'checked' : '' }}>
                        <span>
                            القيم الغذائية تقديرية
                            <span class="block text-xs text-amber-700 mt-1">{{ config('meal_library.nutrition_disclaimer_ar') }}</span>
                        </span>
                    </label>
                </div>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- وقت التحضير -->
                    <div>
                        <label for="prep_time" class="block text-sm font-medium text-gray-700">وقت التحضير (دقيقة)</label>
                        <input type="number" name="prep_time" id="prep_time" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('prep_time', $mealPlan->prep_time) }}">
                        @error('prep_time')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- وقت الطبخ -->
                    <div>
                        <label for="cook_time" class="block text-sm font-medium text-gray-700">وقت الطبخ (دقيقة)</label>
                        <input type="number" name="cook_time" id="cook_time" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('cook_time', $mealPlan->cook_time) }}">
                        @error('cook_time')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- عدد الحصص -->
                    <div>
                        <label for="servings" class="block text-sm font-medium text-gray-700">عدد الحصص *</label>
                        <input type="number" name="servings" id="servings" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('servings', $mealPlan->servings) }}" required>
                        @error('servings')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- مستوى الصعوبة -->
                    <div>
                        <label for="difficulty" class="block text-sm font-medium text-gray-700">مستوى الصعوبة *</label>
                        <select name="difficulty" id="difficulty" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            <option value="">اختر مستوى الصعوبة</option>
                            <option value="easy" {{ old('difficulty', $mealPlan->difficulty) == 'easy' ? 'selected' : '' }}>سهل</option>
                            <option value="medium" {{ old('difficulty', $mealPlan->difficulty) == 'medium' ? 'selected' : '' }}>متوسط</option>
                            <option value="hard" {{ old('difficulty', $mealPlan->difficulty) == 'hard' ? 'selected' : '' }}>صعب</option>
                        </select>
                        @error('difficulty')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- صورة الوجبة -->
                    <div>
                        <label for="image" class="block text-sm font-medium text-gray-700">صورة الوجبة (ارفع صورة دقيقة لاستبدال الحالية)</label>
                        @if($mealPlan->image_url)
                            <div class="mb-2">
                                <img src="{{ $mealPlan->image_url }}?v={{ optional($mealPlan->updated_at)->timestamp }}" alt="{{ $mealPlan->name }}" class="w-40 h-40 object-cover rounded border">
                                <p class="text-sm text-gray-500 mt-1">الصورة الحالية — يمكنك استبدالها برفع ملف جديد</p>
                                @if($mealPlan->image_attribution)
                                    <p class="text-xs text-gray-400">{{ $mealPlan->image_attribution }}</p>
                                @endif
                            </div>
                        @endif
                        <input type="file" name="image" id="image" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        @error('image')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- المكونات وطريقة التحضير -->
            <div class="border-b border-gray-200 py-6">
                <h3 class="text-lg font-medium text-gray-900">المكونات وطريقة التحضير</h3>
                <p class="mt-1 text-sm text-gray-500">تعديل المكونات المطلوبة وخطوات التحضير.</p>
                
                <div class="mt-6">
                    <!-- المكونات -->
                    <div class="mb-6">
                        <label for="ingredients" class="block text-sm font-medium text-gray-700">المكونات *</label>
                        <textarea name="ingredients" id="ingredients" rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="اكتب كل مكون في سطر منفصل" required>{{ old('ingredients', $mealPlan->ingredients) }}</textarea>
                        @error('ingredients')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">اكتب كل مكون في سطر منفصل مع الكمية المطلوبة.</p>
                    </div>

                    <!-- طريقة التحضير -->
                    <div>
                        <label for="instructions" class="block text-sm font-medium text-gray-700">طريقة التحضير</label>
                        <textarea name="instructions" id="instructions" rows="6" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="اكتب خطوات التحضير بالتفصيل">{{ old('instructions', $mealPlan->instructions) }}</textarea>
                        @error('instructions')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">اكتب خطوات التحضير بشكل واضح ومرتب.</p>
                    </div>
                </div>
            </div>

            @include('partials.audience-fields', ['model' => $mealPlan])

            <!-- إعدادات النشر -->
            <div class="border-b border-gray-200 py-6">
                <h3 class="text-lg font-medium text-gray-900">إعدادات النشر</h3>
                <p class="mt-1 text-sm text-gray-500">تعديل حالة نشر الوجبة.</p>
                
                <div class="mt-6">
                    <!-- حالة النشاط -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" id="is_active" value="1" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" {{ old('is_active', $mealPlan->is_active) ? 'checked' : '' }}>
                        </div>
                        <div class="mr-3 text-sm">
                            <label for="is_active" class="font-medium text-gray-700">نشر الوجبة</label>
                            <p class="text-gray-500">عند التفعيل، ستكون الوجبة متاحة في الجداول العامة.</p>
                        </div>
                    </div>
                </div>
                
                <!-- معلومات الوجبة -->
                <div class="mt-6 bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">معلومات الوجبة</h4>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">تاريخ الإنشاء</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $mealPlan->created_at ? $mealPlan->created_at->format('d/m/Y H:i') : '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">آخر تحديث</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $mealPlan->updated_at ? $mealPlan->updated_at->format('d/m/Y H:i') : '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">المؤلف</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $mealPlan->user->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">الوقت الكلي</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $mealPlan->total_time }} دقيقة</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('meal-plans.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    إلغاء
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    تحديث الوجبة
                </button>
            </div>
        </form>
    </div>
</div>
@endsection