@extends('layouts.admin')

@section('title', 'تعديل جدولة التمرين')

@section('header', 'تعديل جدولة التمرين')

@section('header_actions')
<div class="flex space-x-2">
    <a href="{{ route('workout-schedules.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
        <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        العودة للجدول
    </a>
</div>
@endsection

@section('content')
<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <div class="p-6">
        <div class="mb-6">
            <h2 class="text-lg font-medium text-gray-900">تعديل جدولة التمرين</h2>
            <p class="mt-1 text-sm text-gray-500">قم بتحديث بيانات الجدولة الأسبوعية.</p>
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

        <form action="{{ route('workout-schedules.update', $workoutSchedule) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900">معلومات الجدولة الأساسية</h3>
                <p class="mt-1 text-sm text-gray-500">اختر التمرين والتوقيت المناسب.</p>
                
                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="workout_id" class="block text-sm font-medium text-gray-700">التمرين *</label>
                        <select name="workout_id" id="workout_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            <option value="">اختر التمرين</option>
                            @foreach($workouts as $workout)
                                <option value="{{ $workout->id }}" {{ old('workout_id', $workoutSchedule->workout_id) == $workout->id ? 'selected' : '' }}>
                                    {{ $workout->name }} ({{ $workout->duration }} دقيقة - {{ $workout->difficulty_name }})
                                </option>
                            @endforeach
                        </select>
                        @error('workout_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="week_number" class="block text-sm font-medium text-gray-700">رقم الأسبوع *</label>
                        <select name="week_number" id="week_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ (int) old('week_number', $workoutSchedule->week_number) === $i ? 'selected' : '' }}>الأسبوع {{ $i }}</option>
                            @endfor
                        </select>
                        @error('week_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="session_number" class="block text-sm font-medium text-gray-700">رقم الجلسة *</label>
                        <select name="session_number" id="session_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            @for($i = 1; $i <= 7; $i++)
                                <option value="{{ $i }}" {{ (int) old('session_number', $workoutSchedule->session_number) === $i ? 'selected' : '' }}>الجلسة {{ $i }}</option>
                            @endfor
                        </select>
                        @error('session_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <div class="mt-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700">ملاحظات إضافية</label>
                    <textarea name="notes" id="notes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="ملاحظات خاصة للمدرب أو العميل حول هذه الجدولة">{{ old('notes', $workoutSchedule->notes) }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            @include('partials.audience-fields', ['model' => $workoutSchedule])

            <div class="py-6">
                <h3 class="text-lg font-medium text-gray-900">إعدادات النشر</h3>
                <p class="mt-1 text-sm text-gray-500">حدد حالة نشر الجدولة.</p>
                
                <div class="mt-6">
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="status" id="status" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" {{ old('status', $workoutSchedule->status) ? 'checked' : '' }}>
                        </div>
                        <div class="mr-3 text-sm">
                            <label for="status" class="font-medium text-gray-700">تفعيل الجدولة</label>
                            <p class="text-gray-500">عند التفعيل، ستكون الجدولة متاحة للعملاء.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3">
                <a href="{{ route('workout-schedules.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    إلغاء
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    حفظ التعديلات
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
