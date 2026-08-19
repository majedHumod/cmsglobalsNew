@props([
    'cancelHref' => null,
    'cancelLabel' => 'إلغاء',
    'submitLabel' => 'حفظ',
])

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center justify-end gap-2 border-t border-tremor-border pt-5']) }}>
    {{ $slot }}

    @if($cancelHref)
        <x-admin.button :href="$cancelHref" variant="secondary">
            {{ $cancelLabel }}
        </x-admin.button>
    @endif

    <x-admin.button type="submit" variant="primary">
        {{ $submitLabel }}
    </x-admin.button>
</div>
