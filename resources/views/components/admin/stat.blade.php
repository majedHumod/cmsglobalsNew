@props([
    'label' => null,
    'value' => null,
    'hint' => null,
])

<div {{ $attributes->merge(['class' => 'admin-kpi']) }}>
    <div>
        @if($label)
            <div class="admin-kpi-label">{{ $label }}</div>
        @endif
        @if($value !== null)
            <div class="admin-kpi-value">{{ $value }}</div>
        @endif
        {{ $slot }}
    </div>
    @if($hint)
        <div class="admin-kpi-meta flat">{{ $hint }}</div>
    @endif
</div>
