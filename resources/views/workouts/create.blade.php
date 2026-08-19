@extends('layouts.admin')

@section('title', 'إضافة تمرين جديد')

@section('header', 'إضافة تمرين جديد')

@section('header_actions')
<div class="flex space-x-2">
    <a href="{{ route('workouts.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
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
            <h2 class="text-lg font-medium text-gray-900">إنشاء تمرين جديد</h2>
            <p class="mt-1 text-sm text-gray-500">قم بإضافة تمرين جديد لبرنامجك التدريبي.</p>
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

        <form action="{{ route('workouts.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <!-- معلومات التمرين الأساسية -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900">معلومات التمرين الأساسية</h3>
                <p class="mt-1 text-sm text-gray-500">أدخل المعلومات الأساسية للتمرين.</p>
                
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- اسم التمرين -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">اسم التمرين *</label>
                        <input type="text" name="name" id="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('name') }}" required placeholder="مثال: تمرين الضغط">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- مدة التمرين -->
                    <div>
                        <label for="duration" class="block text-sm font-medium text-gray-700">مدة التمرين (دقيقة) *</label>
                        <input type="number" name="duration" id="duration" min="1" max="300" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('duration') }}" required>
                        @error('duration')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- وصف التمرين -->
                <div class="mt-6">
                    <label for="description" class="block text-sm font-medium text-gray-700">وصف التمرين *</label>
                    <textarea name="description" id="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required placeholder="اكتب وصفاً مفصلاً للتمرين وطريقة أدائه">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <!-- إعدادات التمرين -->
            <div class="border-b border-gray-200 py-6">
                <h3 class="text-lg font-medium text-gray-900">إعدادات التمرين</h3>
                <p class="mt-1 text-sm text-gray-500">حدد مستوى الصعوبة والإعدادات الأخرى.</p>
                
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- مستوى الصعوبة -->
                    <div>
                        <label for="difficulty" class="block text-sm font-medium text-gray-700">مستوى الصعوبة *</label>
                        <select name="difficulty" id="difficulty" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            <option value="easy" {{ old('difficulty') == 'easy' ? 'selected' : '' }}>سهل</option>
                            <option value="medium" {{ old('difficulty') == 'medium' ? 'selected' : '' }}>متوسط</option>
                            <option value="hard" {{ old('difficulty') == 'hard' ? 'selected' : '' }}>صعب</option>
                        </select>
                        @error('difficulty')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- رابط الفيديو -->
                    <div>
                        <label for="video_url" class="block text-sm font-medium text-gray-700">رابط فيديو التمرين</label>
                        <input type="url" name="video_url" id="video_url" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('video_url') }}" placeholder="https://youtube.com/watch?v=...">
                        @error('video_url')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">رابط فيديو توضيحي للتمرين (اختياري).</p>
                    </div>
                </div>
            </div>
            
            @include('partials.audience-fields', ['model' => new \App\Models\Workout()])

            @include('partials.workout-exercise-picker', ['selectedExercises' => $selectedExercises ?? collect()])

            <!-- إعدادات النشر -->
            <div class="py-6">
                <h3 class="text-lg font-medium text-gray-900">إعدادات النشر</h3>
                <p class="mt-1 text-sm text-gray-500">حدد حالة نشر التمرين.</p>
                
                <div class="mt-6">
                    <!-- حالة النشاط -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="status" id="status" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" {{ old('status', true) ? 'checked' : '' }}>
                        </div>
                        <div class="mr-3 text-sm">
                            <label for="status" class="font-medium text-gray-700">تفعيل التمرين</label>
                            <p class="text-gray-500">عند التفعيل، سيكون التمرين متاحاً للعملاء.</p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="mr-3">
                            <h3 class="text-sm font-medium text-blue-800">نصائح لإنشاء التمرين</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>اكتب وصفاً واضحاً ومفصلاً للتمرين</li>
                                    <li>حدد المدة بدقة لمساعدة العملاء في التخطيط</li>
                                    <li>اختر مستوى الصعوبة المناسب</li>
                                    <li>أضف رابط فيديو توضيحي إذا أمكن</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3">
                <a href="{{ route('workouts.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    إلغاء
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    حفظ التمرين
                </button>
            </div>
        </form>
    </div>
</div>
@endsection