@props([
    'href' => null,
    'type' => 'button',
    'tone' => 'default', // default|danger
    'confirm' => null,
])

@php
    $base = 'inline-flex items-center justify-center gap-1.5 rounded-tremor-default border px-2.5 py-1.5 text-xs font-semibold transition focus:outline-none focus:ring-2 focus:ring-offset-1 disabled:cursor-not-allowed disabled:opacity-60';
    $toneClasses = $tone === 'danger'
        ? 'border-rose-200 bg-white text-rose-700 hover:bg-rose-50 focus:ring-rose-400'
        : 'border-tremor-border bg-white text-tremor-content-emphasis hover:bg-tremor-background-muted focus:ring-tremor-brand';
    $classes = trim("{$base} {$toneClasses}");
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button
        type="{{ $type }}"
        @if($confirm) onclick="return confirm(@js($confirm))" @endif
        {{ $attributes->merge(['class' => $classes]) }}
    >
        {{ $slot }}
    </button>
@endif
