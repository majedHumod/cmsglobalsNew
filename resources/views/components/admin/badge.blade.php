@props([
    'tone' => 'neutral', // neutral|success|danger|brand|warning
])

@php
    $classes = match ($tone) {
        'success' => 'bg-emerald-50 text-emerald-700',
        'danger' => 'bg-rose-50 text-rose-700',
        'warning' => 'bg-amber-50 text-amber-800',
        'brand' => 'bg-tremor-brand-faint text-tremor-brand-emphasis',
        default => 'bg-tremor-background-subtle text-tremor-content-emphasis',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {$classes}"]) }}>
    {{ $slot }}
</span>
