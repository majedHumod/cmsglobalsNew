@extends('layouts.client')

@section('title', 'متابعة')

@section('content')
<div class="space-y-4">
    <section class="rounded-2xl bg-white p-5 shadow-sm">
        <h1 class="font-semibold text-slate-900">إرسال متابعة للمدرب</h1>
        <p class="text-sm text-slate-500 mt-1">سجّل تقدّمك اليومي لمساعدة مدربك على متابعتك.</p>

        <form method="POST" action="{{ route('client.progress.store') }}" enctype="multipart/form-data" class="mt-5 space-y-4">
            @csrf
            <div>
                <label class="block text-sm text-slate-700 mb-1">تاريخ التحديث</label>
                <input type="datetime-local" name="checked_in_at" value="{{ old('checked_in_at', now()->format('Y-m-d\TH:i')) }}" class="w-full rounded-xl border-slate-300" required>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm text-slate-700 mb-1">الوزن (كجم)</label>
                    <input type="number" step="0.01" name="weight" value="{{ old('weight') }}" class="w-full rounded-xl border-slate-300">
                </div>
                <div>
                    <label class="block text-sm text-slate-700 mb-1">نسبة الدهون</label>
                    <input type="number" step="0.01" name="body_fat_percentage" value="{{ old('body_fat_percentage') }}" class="w-full rounded-xl border-slate-300">
                </div>
                <div>
                    <label class="block text-sm text-slate-700 mb-1">الخصر (سم)</label>
                    <input type="number" step="0.01" name="waist_cm" value="{{ old('waist_cm') }}" class="w-full rounded-xl border-slate-300">
                </div>
                <div>
                    <label class="block text-sm text-slate-700 mb-1">الطاقة /10</label>
                    <input type="number" name="energy_level" min="1" max="10" value="{{ old('energy_level', 7) }}" class="w-full rounded-xl border-slate-300">
                </div>
                <div>
                    <label class="block text-sm text-slate-700 mb-1">التزام التمرين /10</label>
                    <input type="number" name="training_adherence" min="1" max="10" value="{{ old('training_adherence', 7) }}" class="w-full rounded-xl border-slate-300">
                </div>
                <div>
                    <label class="block text-sm text-slate-700 mb-1">التزام الغذاء /10</label>
                    <input type="number" name="nutrition_adherence" min="1" max="10" value="{{ old('nutrition_adherence', 7) }}" class="w-full rounded-xl border-slate-300">
                </div>
            </div>
            <div>
                <label class="block text-sm text-slate-700 mb-1">ملاحظاتك</label>
                <textarea name="notes" rows="4" class="w-full rounded-xl border-slate-300">{{ old('notes') }}</textarea>
            </div>
            <div>
                <label class="block text-sm text-slate-700 mb-1">صورة التقدم (اختياري)</label>
                <input type="file" name="progress_photo" accept="image/*" class="w-full text-sm text-slate-500">
            </div>
            <button type="submit" class="w-full btn-brand rounded-xl py-3 font-medium">إرسال المتابعة</button>
        </form>
    </section>
</div>
@endsection
