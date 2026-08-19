@props([
    'disabled' => false,
])

<select {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge([
    'class' => 'mt-1 block w-full rounded-tremor-default border-tremor-border shadow-tremor-input focus:border-tremor-brand focus:ring-tremor-brand disabled:bg-tremor-background-muted',
]) !!}>
    {{ $slot }}
</select>
