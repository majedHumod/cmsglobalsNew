@extends('layouts.admin')

@section('title', 'إضافة Check-in')
@section('header', 'إضافة Check-in جديد للعميل: ' . $client->name)

@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <form method="POST" action="{{ route('clients.progress.store', $client) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label for="checked_in_at" class="block text-sm font-medium text-gray-700">تاريخ التحديث</label>
                <input type="datetime-local" name="checked_in_at" id="checked_in_at" value="{{ old('checked_in_at', now()->format('Y-m-d\TH:i')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>
            <div>
                <label for="weight" class="block text-sm font-medium text-gray-700">الوزن (كجم)</label>
                <input type="number" step="0.01" name="weight" id="weight" value="{{ old('weight') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="body_fat_percentage" class="block text-sm font-medium text-gray-700">نسبة الدهون</label>
                <input type="number" step="0.01" name="body_fat_percentage" id="body_fat_percentage" value="{{ old('body_fat_percentage') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="waist_cm" class="block text-sm font-medium text-gray-700">الخصر (سم)</label>
                <input type="number" step="0.01" name="waist_cm" id="waist_cm" value="{{ old('waist_cm') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="chest_cm" class="block text-sm font-medium text-gray-700">الصدر (سم)</label>
                <input type="number" step="0.01" name="chest_cm" id="chest_cm" value="{{ old('chest_cm') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="hips_cm" class="block text-sm font-medium text-gray-700">الأرداف (سم)</label>
                <input type="number" step="0.01" name="hips_cm" id="hips_cm" value="{{ old('hips_cm') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="arm_cm" class="block text-sm font-medium text-gray-700">الذراع (سم)</label>
                <input type="number" step="0.01" name="arm_cm" id="arm_cm" value="{{ old('arm_cm') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="thigh_cm" class="block text-sm font-medium text-gray-700">الفخذ (سم)</label>
                <input type="number" step="0.01" name="thigh_cm" id="thigh_cm" value="{{ old('thigh_cm') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="progress_photo" class="block text-sm font-medium text-gray-700">صورة التقدم</label>
                <input type="file" name="progress_photo" id="progress_photo" class="mt-1 block w-full text-sm text-gray-500">
            </div>
            <div>
                <label for="energy_level" class="block text-sm font-medium text-gray-700">مستوى الطاقة /10</label>
                <input type="number" name="energy_level" id="energy_level" min="1" max="10" value="{{ old('energy_level') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="training_adherence" class="block text-sm font-medium text-gray-700">الالتزام بالتمرين /10</label>
                <input type="number" name="training_adherence" id="training_adherence" min="1" max="10" value="{{ old('training_adherence') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="nutrition_adherence" class="block text-sm font-medium text-gray-700">الالتزام الغذائي /10</label>
                <input type="number" name="nutrition_adherence" id="nutrition_adherence" min="1" max="10" value="{{ old('nutrition_adherence') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700">ملاحظات العميل/المدرب</label>
                <textarea name="notes" id="notes" rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
            </div>
            <div>
                <label for="coach_feedback" class="block text-sm font-medium text-gray-700">تغذية راجعة من المدرب</label>
                <textarea name="coach_feedback" id="coach_feedback" rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('coach_feedback') }}</textarea>
            </div>
        </div>

        <div>
            <label for="next_steps" class="block text-sm font-medium text-gray-700">الخطوات القادمة</label>
            <textarea name="next_steps" id="next_steps" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('next_steps') }}</textarea>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('clients.progress.index', $client) }}" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">إلغاء</a>
            <button type="submit" class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">حفظ Check-in</button>
        </div>
    </form>
</div>
@endsection
