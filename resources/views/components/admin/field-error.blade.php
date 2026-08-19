@props([
    'name' => null,
])

@php
    $errorKey = $name ?? $attributes->get('name');
@endphp

@error($errorKey)
    <p {{ $attributes->merge(['class' => 'mt-1 text-sm text-rose-600']) }}>{{ $message }}</p>
@enderror
