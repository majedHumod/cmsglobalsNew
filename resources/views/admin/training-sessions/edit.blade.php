@extends('layouts.admin')

@section('title', 'تعديل جلسة التدريب')

@section('header', 'تعديل جلسة التدريب: ' . $trainingSession->title)

@section('header_actions')
<div class="flex space-x-2">
    <a href="{{ route('training-sessions.show', $trainingSession) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50" target="_blank">
        <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
        </svg>
        عرض الجلسة
    </a>
    <a href="{{ route('admin.training-sessions.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
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
            <h2 class="text-lg font-medium text-gray-900">تعديل جلسة التدريب</h2>
            <p class="mt-1 text-sm text-gray-500">قم بتعديل معلومات جلسة التدريب.</p>
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

        <form action="{{ route('admin.training-sessions.update', $trainingSession) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            
            <!-- معلومات الجلسة الأساسية -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900">معلومات الجلسة الأساسية</h3>
                <p class="mt-1 text-sm text-gray-500">تعديل المعلومات الأساسية لجلسة التدريب.</p>
                
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- عنوان الجلسة -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">عنوان الجلسة *</label>
                        <input type="text" name="title" id="title" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('title', $trainingSession->title) }}" required>
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- ترتيب العرض -->
                    <div>
                        <label for="sort_order" class="block text-sm font-medium text-gray-700">ترتيب العرض</label>
                        <input type="number" name="sort_order" id="sort_order" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('sort_order', $trainingSession->sort_order) }}">
                        @error('sort_order')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- وصف الجلسة -->
                <div class="mt-6">
                    <label for="description" class="block text-sm font-medium text-gray-700">وصف الجلسة *</label>
                    <textarea name="description" id="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('description', $trainingSession->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <!-- إعدادات السعر والمدة -->
            <div class="border-b border-gray-200 py-6">
                <h3 class="text-lg font-medium text-gray-900">إعدادات السعر والمدة</h3>
                <p class="mt-1 text-sm text-gray-500">تعديل سعر ومدة جلسة التدريب.</p>
                
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- السعر -->
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700">سعر الجلسة (ريال) *</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <input type="number" name="price" id="price" step="0.01" min="0" class="block w-full pr-12 border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" value="{{ old('price', $trainingSession->price) }}" required>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">ريال</span>
                            </div>
                        </div>
                        @error('price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- مدة الجلسة -->
                    <div>
                        <label for="duration_hours" class="block text-sm font-medium text-gray-700">مدة الجلسة (ساعة) *</label>
                        <select name="duration_hours" id="duration_hours" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            <option value="1" {{ old('duration_hours', $trainingSession->duration_hours) == 1 ? 'selected' : '' }}>ساعة واحدة</option>
                            <option value="2" {{ old('duration_hours', $trainingSession->duration_hours) == 2 ? 'selected' : '' }}>ساعتان</option>
                            <option value="3" {{ old('duration_hours', $trainingSession->duration_hours) == 3 ? 'selected' : '' }}>3 ساعات</option>
                            <option value="4" {{ old('duration_hours', $trainingSession->duration_hours) == 4 ? 'selected' : '' }}>4 ساعات</option>
                            <option value="6" {{ old('duration_hours', $trainingSession->duration_hours) == 6 ? 'selected' : '' }}>6 ساعات</option>
                            <option value="8" {{ old('duration_hours', $trainingSession->duration_hours) == 8 ? 'selected' : '' }}>8 ساعات (يوم كامل)</option>
                        </select>
                        @error('duration_hours')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <label for="session_type" class="block text-sm font-medium text-gray-700">نوع الجلسة *</label>
                        <select name="session_type" id="session_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            <option value="in_person" {{ old('session_type', $trainingSession->session_type ?? 'in_person') === 'in_person' ? 'selected' : '' }}>حضوري</option>
                            <option value="online" {{ old('session_type', $trainingSession->session_type) === 'online' ? 'selected' : '' }}>أونلاين</option>
                            <option value="hybrid" {{ old('session_type', $trainingSession->session_type) === 'hybrid' ? 'selected' : '' }}>هجين</option>
                        </select>
                    </div>
                    <div>
                        <label for="capacity" class="block text-sm font-medium text-gray-700">السعة *</label>
                        <input type="number" name="capacity" id="capacity" min="1" value="{{ old('capacity', $trainingSession->capacity ?? 1) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                    <div>
                        <label for="location" class="block text-sm font-medium text-gray-700">الموقع / رابط الجلسة</label>
                        <input type="text" name="location" id="location" value="{{ old('location', $trainingSession->location) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="video_meeting_url" class="block text-sm font-medium text-gray-700">رابط الاجتماع المرئي</label>
                        <input type="url" name="video_meeting_url" id="video_meeting_url" value="{{ old('video_meeting_url', $trainingSession->video_meeting_url) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <!-- صورة الجلسة -->
                <div class="mt-6">
                    <label for="image" class="block text-sm font-medium text-gray-700">صورة الجلسة</label>
                    @if($trainingSession->image)
                        <div class="mb-2">
                            <img src="{{ Storage::url($trainingSession->image) }}" alt="{{ $trainingSession->title }}" class="w-32 h-32 object-cover rounded">
                            <p class="text-sm text-gray-500 mt-1">الصورة الحالية</p>
                        </div>
                    @endif
                    <input type="file" name="image" id="image" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    @error('image')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            @include('partials.audience-fields', ['model' => $trainingSession])

            <!-- إعدادات العرض -->
            <div class="py-6">
                <h3 class="text-lg font-medium text-gray-900">إعدادات العرض</h3>
                <p class="mt-1 text-sm text-gray-500">تعديد إعدادات عرض جلسة التدريب.</p>
                
                <div class="mt-6">
                    <!-- حالة الإظهار -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="is_visible" id="is_visible" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" {{ old('is_visible', $trainingSession->is_visible) ? 'checked' : '' }}>
                        </div>
                        <div class="mr-3 text-sm">
                            <label for="is_visible" class="font-medium text-gray-700">إظهار الجلسة</label>
                            <p class="text-gray-500">عند التفعيل، ستظهر الجلسة في الصفحة الرئيسية وصفحة الجلسات.</p>
                        </div>
                    </div>
                </div>
                
                <!-- معلومات الجلسة -->
                <div class="mt-6 bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">معلومات الجلسة</h4>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">تاريخ الإنشاء</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $trainingSession->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">آخر تحديث</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $trainingSession->updated_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">المؤلف</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $trainingSession->user->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">عدد الحجوزات</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $trainingSession->total_bookings }} حجز</dd>
                        </div>
                    </dl>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.training-sessions.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    إلغاء
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    تحديث الجلسة
                </button>
            </div>
        </form>
    </div>
</div>
@endsection