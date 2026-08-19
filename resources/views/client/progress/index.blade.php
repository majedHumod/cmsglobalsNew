@extends('layouts.client')

@section('title', 'تقدّمي')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="font-semibold text-slate-900">سجل المتابعة</h1>
        <a href="{{ route('client.progress.create') }}" class="text-sm text-brand font-medium">+ جديد</a>
    </div>

    @forelse($checkIns as $checkIn)
        <div class="rounded-2xl bg-white p-4 shadow-sm border border-slate-100">
            <div class="text-sm font-medium text-slate-900">{{ $checkIn->checked_in_at->format('d/m/Y H:i') }}</div>
            <div class="grid grid-cols-3 gap-2 mt-3 text-xs text-slate-600">
                @if($checkIn->weight)
                    <div>الوزن: {{ $checkIn->weight }} كجم</div>
                @endif
                @if($checkIn->energy_level)
                    <div>الطاقة: {{ $checkIn->energy_level }}/10</div>
                @endif
                @if($checkIn->average_adherence)
                    <div>الالتزام: {{ $checkIn->average_adherence }}/10</div>
                @endif
            </div>
            @if($checkIn->notes)
                <p class="text-sm text-slate-500 mt-2">{{ Str::limit($checkIn->notes, 120) }}</p>
            @endif
        </div>
    @empty
        <div class="rounded-2xl bg-white p-6 shadow-sm text-center text-slate-500 text-sm">
            لم ترسل أي متابعة بعد.
            <a href="{{ route('client.progress.create') }}" class="block mt-2 text-brand">أرسل أول تحديث</a>
        </div>
    @endforelse
</div>
@endsection
