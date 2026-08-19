@props([
    'align' => 'end', // start|end|center
])

@php
    $alignClass = match ($align) {
        'start' => 'justify-start',
        'center' => 'justify-center',
        default => 'justify-end',
    };
@endphp

<div {{ $attributes->merge(['class' => "flex flex-wrap items-center gap-2 {$alignClass}"]) }}>
    {{ $slot }}
</div>
