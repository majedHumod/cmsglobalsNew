@props([
    'type' => 'info', // success|error|warning|info
    'title' => null,
])

@php
    $styles = match ($type) {
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
        'error' => 'border-rose-200 bg-rose-50 text-rose-900',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-900',
        default => 'border-sky-200 bg-sky-50 text-sky-900',
    };
@endphp

<div {{ $attributes->merge(['class' => "mb-4 rounded-tremor-default border px-4 py-3 text-sm {$styles}", 'role' => 'alert']) }}>
    @if($title)
        <p class="mb-1 font-semibold">{{ $title }}</p>
    @endif
    <div>{{ $slot }}</div>
</div>
