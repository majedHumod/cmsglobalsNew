@extends('layouts.admin')

@section('title', 'إضافة فترة توفر')
@section('header', 'إضافة فترة توفر أسبوعية')

@section('content')
<div class="space-y-4">
    @role('admin')
    @if($coaches->isEmpty())
    <div class="rounded-md border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900" role="status">
        لا يوجد مدرب لاختياره. بعد إسناد دور <strong>coach</strong> لحساب المدرب (أو تشغيل
        <code class="mx-1 rounded bg-amber-100 px-1">php artisan tenants:ensure-admin-coach</code>
        للمستأجرات الحالية)، عُد لإكمال الحقول.
    </div>
    @endif
    @endrole
    <div class="bg-white shadow rounded-lg p-6">
    @if ($errors->any())
        <div class="mb-4 rounded-md border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" role="alert">
            <div class="font-medium mb-1">تعذّر الحفظ. راجع الحقول التالية:</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form method="POST" action="{{ route('coach-availabilities.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @csrf
        @unless(auth()->user()->hasRole('coach') && ! auth()->user()->hasRole('admin'))
            <div class="md:col-span-2">
                <label for="user_id" class="block text-sm font-medium text-gray-700">المدرب</label>
                <select name="user_id" id="user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    @foreach($coaches as $coach)
                        <option value="{{ $coach->id }}" @selected(old('user_id') == $coach->id)>{{ $coach->name }}</option>
                    @endforeach
                </select>
                @error('user_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
        @endunless

        <div>
            <label for="day_of_week" class="block text-sm font-medium text-gray-700">اليوم</label>
            <select name="day_of_week" id="day_of_week" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @foreach([0 => 'الأحد', 1 => 'الاثنين', 2 => 'الثلاثاء', 3 => 'الأربعاء', 4 => 'الخميس', 5 => 'الجمعة', 6 => 'السبت'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('day_of_week') == $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="capacity" class="block text-sm font-medium text-gray-700">السعة</label>
            <input type="number" name="capacity" id="capacity" min="1" value="{{ old('capacity', 1) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label for="start_time" class="block text-sm font-medium text-gray-700">من</label>
            <input type="time" name="start_time" id="start_time" value="{{ old('start_time', '09:00') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label for="end_time" class="block text-sm font-medium text-gray-700">إلى</label>
            <input type="time" name="end_time" id="end_time" value="{{ old('end_time', '17:00') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label for="slot_duration_minutes" class="block text-sm font-medium text-gray-700">مدة الموعد بالدقائق</label>
            <input type="number" name="slot_duration_minutes" id="slot_duration_minutes" min="15" value="{{ old('slot_duration_minutes', 60) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label for="buffer_minutes" class="block text-sm font-medium text-gray-700">فاصل بين المواعيد</label>
            <input type="number" name="buffer_minutes" id="buffer_minutes" min="0" value="{{ old('buffer_minutes', 0) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div class="md:col-span-2">
            <label for="location" class="block text-sm font-medium text-gray-700">الموقع أو رابط الجلسة</label>
            <input type="text" name="location" id="location" value="{{ old('location') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="الاستوديو الرئيسي أو رابط Zoom">
        </div>
        <div class="md:col-span-2 flex items-center gap-3">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', 1)) class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <label for="is_active" class="text-sm font-medium text-gray-700">تفعيل هذه الفترة</label>
        </div>
        <div class="md:col-span-2 flex justify-end gap-3">
            <a href="{{ route('coach-availabilities.index') }}" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">إلغاء</a>
            <button type="submit" class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">حفظ الفترة</button>
        </div>
    </form>
    </div>
</div>
@endsection
