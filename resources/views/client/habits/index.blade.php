@extends('layouts.client')

@section('title', 'يومي')

@section('content')
@php
    $today = now()->toDateString();
@endphp
<div class="space-y-4">
    <section class="rounded-2xl bg-white p-5 shadow-sm">
        <h1 class="font-semibold text-slate-900">عاداتك الأسبوعية</h1>
        <p class="text-sm text-slate-500 mt-1">نسبة الالتزام: <span class="font-bold text-brand">{{ $weeklyCompletion }}%</span></p>
        <div class="grid grid-cols-2 gap-2 mt-4 text-center text-xs">
            <div class="bg-brand-soft rounded-xl py-3">
                <div class="font-bold text-brand">{{ $insights['active_streak'] ?? 0 }}</div>
                <div class="text-slate-500">سلسلة حالية</div>
            </div>
            <div class="bg-emerald-50 rounded-xl py-3">
                <div class="font-bold text-emerald-700">{{ $gamification['points'] ?? 0 }}</div>
                <div class="text-slate-500">نقاط التحفيز</div>
            </div>
        </div>
        @if($activeChallenge)
            <div class="mt-3 p-3 rounded-xl bg-amber-50 text-sm">
                <div class="font-medium text-amber-900">{{ $activeChallenge->title }}</div>
                <div class="text-amber-700 mt-1">{{ $challengeProgress }} / {{ $activeChallenge->target_value }}</div>
                <a href="{{ route('client.challenges.index') }}" class="inline-block mt-2 text-xs text-amber-800 underline">عرض التحديات</a>
            </div>
        @endif
    </section>

    <section class="space-y-2">
        @forelse($habits as $habit)
            @php
                $todayLog = $habit->logs->first(function ($log) use ($today) {
                    return optional($log->logged_on)->toDateString() === $today;
                });
                $completedToday = (bool) ($todayLog?->is_completed);
                $weekDone = $habit->logs->where('is_completed', true)->count();
            @endphp
            <div class="rounded-2xl bg-white p-4 shadow-sm border {{ $completedToday ? 'border-emerald-200' : 'border-slate-100' }}">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <div class="font-medium text-slate-900">{{ $habit->name }}</div>
                        <div class="text-xs text-slate-500 mt-1">الهدف: {{ $habit->target_value }} {{ $habit->unit }}</div>
                        <div class="text-xs text-slate-400 mt-1">{{ $weekDone }} / 7 أيام</div>
                    </div>
                    @if($completedToday)
                        <span class="text-sm px-4 py-2 rounded-xl bg-emerald-100 text-emerald-800 font-medium inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            مكتمل اليوم
                        </span>
                    @else
                        <form method="POST" action="{{ route('client.habits.log', $habit) }}">
                            @csrf
                            <input type="hidden" name="logged_on" value="{{ $today }}">
                            <input type="hidden" name="value" value="{{ $habit->target_value }}">
                            <input type="hidden" name="is_completed" value="1">
                            <button type="submit" class="text-sm px-4 py-2 rounded-xl btn-brand">
                                سجّل اليوم
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-2xl bg-white p-6 shadow-sm text-center text-slate-500 text-sm">
                لا توجد عادات مفعّلة. تواصل مع مدربك لإضافة عاداتك.
            </div>
        @endforelse
    </section>

    @if($badges->isNotEmpty())
        <section class="rounded-2xl bg-white p-4 shadow-sm">
            <h2 class="font-semibold text-slate-900 mb-2">شاراتك</h2>
            <div class="flex flex-wrap gap-2">
                @foreach($badges as $badge)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-violet-50 text-violet-800">
                        {{ $badge->badge->name ?? 'شارة' }}
                    </span>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
