@extends('layouts.client')

@section('title', 'التحديات والشارات')

@section('content')
<div class="space-y-4">
    <section class="rounded-2xl bg-gradient-to-l from-violet-600 to-indigo-600 text-white p-5 shadow-sm">
        <div class="text-sm opacity-90">نقاط التحفيز</div>
        <div class="text-3xl font-bold mt-1">{{ $gamification['points'] ?? 0 }}</div>
        <div class="text-sm opacity-90 mt-2">{{ $gamification['badges_count'] ?? 0 }} شارة مكتسبة</div>
    </section>

    @if($activeChallenge)
        <section class="rounded-2xl bg-white p-5 shadow-sm">
            <h2 class="font-semibold text-slate-900">{{ $activeChallenge->title }}</h2>
            <p class="text-sm text-slate-500 mt-1">ينتهي {{ $activeChallenge->ends_on->format('d/m/Y') }}</p>
            @php
                $progress = $participant?->progress_value ?? 0;
                $percent = $activeChallenge->target_value > 0
                    ? min(100, round(($progress / $activeChallenge->target_value) * 100))
                    : 0;
            @endphp
            <div class="mt-4">
                <div class="flex justify-between text-sm text-slate-600 mb-1">
                    <span>تقدّمك</span>
                    <span>{{ $progress }} / {{ $activeChallenge->target_value }}</span>
                </div>
                <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-indigo-600 rounded-full" style="width: {{ $percent }}%"></div>
                </div>
            </div>
            @if($participant?->is_completed)
                <div class="mt-3 text-sm text-emerald-700 font-medium">أكملت التحدي! 🎉</div>
            @endif
        </section>
    @else
        <div class="rounded-2xl bg-white p-6 text-center text-slate-500 text-sm">لا يوجد تحدي نشط هذا الأسبوع.</div>
    @endif

    <section class="rounded-2xl bg-white p-5 shadow-sm">
        <h2 class="font-semibold text-slate-900 mb-3">شاراتك</h2>
        @forelse($badges as $badge)
            <div class="flex items-center gap-3 py-2 border-b border-slate-100 last:border-0">
                <span class="text-xl">🏅</span>
                <div>
                    <div class="font-medium text-slate-900">{{ $badge->badge->name ?? 'شارة' }}</div>
                    <div class="text-xs text-slate-500">{{ optional($badge->awarded_at)->format('d/m/Y') }}</div>
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-500">لم تحصل على شارات بعد. استمر في الالتزام!</p>
        @endforelse
    </section>

    <a href="{{ route('client.community.index') }}" class="block text-center text-sm text-indigo-600">انضم للمجتمع</a>
</div>
@endsection
