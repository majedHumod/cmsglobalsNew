@extends('layouts.admin')

@section('title', 'متابعة التقدم')
@section('header', 'متابعة التقدم: ' . $client->name)

@section('header_actions')
<div class="flex gap-2">
    <a href="{{ route('clients.progress.create', $client) }}" class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">إضافة Check-in</a>
    @hasanyrole('admin|coach')
        <a href="{{ route('coach.clients.show', $client) }}" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">ملف العميل</a>
    @endhasanyrole
</div>
@endsection

@section('content')
<div class="space-y-6">
    <div class="bg-white shadow rounded-lg p-6" id="profile">
        <h3 class="text-lg font-semibold text-gray-900">ملف العميل الرياضي</h3>
        <form method="POST" action="{{ route('clients.profile.update', $client) }}" class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            @csrf
            @method('PUT')
            <div class="md:col-span-2">
                <label for="fitness_goal" class="block text-sm font-medium text-gray-700">الهدف الرياضي</label>
                <textarea name="fitness_goal" id="fitness_goal" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('fitness_goal', $profile->fitness_goal) }}</textarea>
            </div>
            <div>
                <label for="target_weight" class="block text-sm font-medium text-gray-700">الوزن المستهدف</label>
                <input type="number" step="0.01" name="target_weight" id="target_weight" value="{{ old('target_weight', $profile->target_weight) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="activity_level" class="block text-sm font-medium text-gray-700">مستوى النشاط</label>
                <select name="activity_level" id="activity_level" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach(['beginner' => 'مبتدئ', 'intermediate' => 'متوسط', 'advanced' => 'متقدم'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('activity_level', $profile->activity_level ?? 'beginner') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="preferred_contact_method" class="block text-sm font-medium text-gray-700">وسيلة التواصل المفضلة</label>
                <select name="preferred_contact_method" id="preferred_contact_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach(['whatsapp' => 'واتساب', 'sms' => 'SMS', 'email' => 'بريد إلكتروني', 'phone' => 'اتصال'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('preferred_contact_method', $profile->preferred_contact_method ?? 'whatsapp') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="injuries" class="block text-sm font-medium text-gray-700">الإصابات أو القيود</label>
                <textarea name="injuries" id="injuries" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('injuries', $profile->injuries) }}</textarea>
            </div>
            <div>
                <label for="medical_notes" class="block text-sm font-medium text-gray-700">ملاحظات طبية</label>
                <textarea name="medical_notes" id="medical_notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('medical_notes', $profile->medical_notes) }}</textarea>
            </div>
            <div>
                <label for="onboarding_notes" class="block text-sm font-medium text-gray-700">ملاحظات البداية</label>
                <textarea name="onboarding_notes" id="onboarding_notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('onboarding_notes', $profile->onboarding_notes) }}</textarea>
            </div>
            @hasanyrole('admin|coach')
            <div>
                <label for="current_program_week" class="block text-sm font-medium text-gray-700">أسبوع البرنامج الحالي</label>
                <input type="number" min="1" max="52" name="current_program_week" id="current_program_week" value="{{ old('current_program_week', $profile->current_program_week ?? 1) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <p class="mt-1 text-xs text-gray-500">يحدد تمرين اليوم من جدول التمارين الأسبوعي.</p>
            </div>
            @endhasanyrole
            <div class="md:col-span-2 flex justify-end">
                <button type="submit" class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">حفظ الملف</button>
            </div>
        </form>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">سجل Check-ins</h3>
                <p class="text-sm text-gray-500">تتبع دوري للقياسات والالتزام والملاحظات.</p>
            </div>
        </div>

        <div class="divide-y divide-gray-200">
            @forelse($checkIns as $checkIn)
                <div class="p-6">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <div class="text-base font-semibold text-gray-900">{{ $checkIn->checked_in_at?->format('Y-m-d H:i') }}</div>
                            <div class="mt-1 text-sm text-gray-500">
                                الوزن: {{ $checkIn->weight ?? '—' }} كجم |
                                الدهون: {{ $checkIn->body_fat_percentage ?? '—' }}% |
                                متوسط الالتزام: {{ $checkIn->average_adherence ?? '—' }}/10
                            </div>
                        </div>
                        <div class="flex gap-3 text-sm">
                            <a href="{{ route('progress-check-ins.show', $checkIn) }}" class="text-indigo-600 hover:text-indigo-800">عرض</a>
                            <form method="POST" action="{{ route('progress-check-ins.destroy', $checkIn) }}" onsubmit="return confirm('حذف هذا التحديث؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">حذف</button>
                            </form>
                        </div>
                    </div>
                    @if($checkIn->notes)
                        <p class="mt-3 text-sm text-gray-700">{{ $checkIn->notes }}</p>
                    @endif
                </div>
            @empty
                <div class="p-6 text-sm text-gray-500">لا توجد تحديثات تقدم مسجلة حتى الآن.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
