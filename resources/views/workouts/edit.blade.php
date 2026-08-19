@extends('layouts.admin')

@section('title', 'تعديل التمرين')

@section('header', 'تعديل التمرين: ' . $workout->name)

@section('header_actions')
<div class="flex space-x-2">
    <a href="{{ route('workouts.show', $workout) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
        <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
        </svg>
        عرض التمرين
    </a>
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
            <h2 class="text-lg font-medium text-gray-900">تعديل التمرين</h2>
            <p class="mt-1 text-sm text-gray-500">قم بتعديل معلومات التمرين.</p>
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

        <form action="{{ route('workouts.update', $workout) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <!-- معلومات التمرين الأساسية -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900">معلومات التمرين الأساسية</h3>
                <p class="mt-1 text-sm text-gray-500">تعديل المعلومات الأساسية للتمرين.</p>
                
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- اسم التمرين -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">اسم التمرين *</label>
                        <input type="text" name="name" id="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('name', $workout->name) }}" required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- مدة التمرين -->
                    <div>
                        <label for="duration" class="block text-sm font-medium text-gray-700">مدة التمرين (دقيقة) *</label>
                        <input type="number" name="duration" id="duration" min="1" max="300" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('duration', $workout->duration) }}" required>
                        @error('duration')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- وصف التمرين -->
                <div class="mt-6">
                    <label for="description" class="block text-sm font-medium text-gray-700">وصف التمرين *</label>
                    <textarea name="description" id="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('description', $workout->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <!-- إعدادات التمرين -->
            <div class="border-b border-gray-200 py-6">
                <h3 class="text-lg font-medium text-gray-900">إعدادات التمرين</h3>
                <p class="mt-1 text-sm text-gray-500">تعديل مستوى الصعوبة والإعدادات الأخرى.</p>
                
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- مستوى الصعوبة -->
                    <div>
                        <label for="difficulty" class="block text-sm font-medium text-gray-700">مستوى الصعوبة *</label>
                        <select name="difficulty" id="difficulty" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            <option value="easy" {{ old('difficulty', $workout->difficulty) == 'easy' ? 'selected' : '' }}>سهل</option>
                            <option value="medium" {{ old('difficulty', $workout->difficulty) == 'medium' ? 'selected' : '' }}>متوسط</option>
                            <option value="hard" {{ old('difficulty', $workout->difficulty) == 'hard' ? 'selected' : '' }}>صعب</option>
                        </select>
                        @error('difficulty')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- رابط الفيديو -->
                    <div>
                        <label for="video_url" class="block text-sm font-medium text-gray-700">رابط فيديو التمرين</label>
                        <input type="url" name="video_url" id="video_url" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('video_url', $workout->video_url) }}">
                        @error('video_url')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            
            @include('partials.audience-fields', ['model' => $workout])

            @include('partials.workout-exercise-picker')

            <!-- معلومات التمرين -->
            <div class="py-6">
                <h3 class="text-lg font-medium text-gray-900">معلومات التمرين</h3>
                <p class="mt-1 text-sm text-gray-500">معلومات إضافية عن التمرين.</p>
                
                <div class="mt-6 bg-gray-50 rounded-lg p-4">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">تاريخ الإنشاء</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $workout->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">آخر تحديث</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $workout->updated_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">المدرب</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $workout->user->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">عدد الجدولات</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $workout->schedules->count() }} جدولة</dd>
                        </div>
                    </dl>
                </div>
                
                <!-- حالة النشاط -->
                <div class="mt-6">
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="status" id="status" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" {{ old('status', $workout->status) ? 'checked' : '' }}>
                        </div>
                        <div class="mr-3 text-sm">
                            <label for="status" class="font-medium text-gray-700">تفعيل التمرين</label>
                            <p class="text-gray-500">عند التفعيل، سيكون التمرين متاحاً للعملاء.</p>
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
                    تحديث التمرين
                </button>
            </div>
        </form>
    </div>
</div>
@endsection