@props([
    'value' => null,
])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-tremor-content-emphasis']) }}>
    {{ $value ?? $slot }}
</label>
