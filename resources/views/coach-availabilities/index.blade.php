@extends('layouts.admin')

@section('title', 'التوفر الأسبوعي')
@section('header', 'التوفر الأسبوعي للمدربين')

@section('header_actions')
    <a href="{{ route('coach-availabilities.create') }}" class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">إضافة فترة توفر</a>
@endsection

@section('content')
<div class="space-y-6">
    @role('admin')
    @if($coaches->isEmpty())
    <div class="rounded-md border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900" role="status">
        لا يوجد مستخدم بدر <strong>coach</strong> بعد. لإسناد الدور لحسابات الأدمن الحالية، شغّل من الخادم:
        <code class="mx-1 rounded bg-amber-100 px-1">php artisan tenants:ensure-admin-coach</code>
        (أو أضف دور <strong>coach</strong> لمن يمثل المدرب من إعدادات الصلاحيات).
    </div>
    @endif
    <div class="bg-white shadow rounded-lg p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="coach_id" class="block text-sm font-medium text-gray-700">المدرب</label>
                <select name="coach_id" id="coach_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">كل المدربين</option>
                    @foreach($coaches as $coach)
                        <option value="{{ $coach->id }}" @selected((string) request('coach_id') === (string) $coach->id)>{{ $coach->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-3">
                <button type="submit" class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">تصفية</button>
                <a href="{{ route('coach-availabilities.index') }}" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">إعادة تعيين</a>
            </div>
        </form>
    </div>
    @endrole

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">المدرب</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">اليوم</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">الفترة</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">المدة/السعة</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">الموقع</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">الحالة</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($availabilities as $availability)
                        <tr>
                            <td class="px-4 py-4 text-sm font-medium text-gray-900">{{ $availability->user->name }}</td>
                            <td class="px-4 py-4 text-sm text-gray-700">{{ $availability->day_name }}</td>
                            <td class="px-4 py-4 text-sm text-gray-700">{{ $availability->start_time }} - {{ $availability->end_time }}</td>
                            <td class="px-4 py-4 text-sm text-gray-700">{{ $availability->slot_duration_minutes }} دقيقة / {{ $availability->capacity }} مقاعد</td>
                            <td class="px-4 py-4 text-sm text-gray-700">{{ $availability->location ?: '—' }}</td>
                            <td class="px-4 py-4 text-sm text-gray-700">{{ $availability->is_active ? 'مفعل' : 'موقوف' }}</td>
                            <td class="px-4 py-4 text-sm">
                                <div class="flex gap-3">
                                    <a href="{{ route('coach-availabilities.edit', $availability) }}" class="text-indigo-600 hover:text-indigo-800">تعديل</a>
                                    <form method="POST" action="{{ route('coach-availabilities.destroy', $availability) }}" onsubmit="return confirm('حذف هذه الفترة؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">لا توجد فترات توفر مسجلة بعد.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
