{{--
  Primary CTA only (header / empty-state).
  For table/card row actions use <x-admin.action> so every page looks the same.
--}}
@props([
    'variant' => 'primary', // primary|secondary|danger|ghost
    'href' => null,
    'type' => 'button',
    'size' => 'md', // sm|md
])

@php
    $base = 'inline-flex items-center justify-center gap-2 font-semibold transition focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed rounded-tremor-default';
    $sizes = $size === 'sm'
        ? 'px-3 py-1.5 text-xs'
        : 'px-4 py-2 text-sm';
    $variants = match ($variant) {
        'secondary' => 'border border-tremor-border bg-white text-tremor-content-emphasis shadow-tremor-input hover:bg-tremor-background-muted focus:ring-tremor-brand',
        'danger' => 'border border-transparent bg-rose-600 text-white hover:bg-rose-700 focus:ring-rose-500',
        'ghost' => 'border border-transparent bg-transparent text-tremor-content-emphasis hover:bg-tremor-background-muted focus:ring-tremor-brand',
        default => 'border border-transparent bg-tremor-brand text-white hover:bg-tremor-brand-emphasis focus:ring-tremor-brand',
    };
    $classes = trim("{$base} {$sizes} {$variants}");
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
