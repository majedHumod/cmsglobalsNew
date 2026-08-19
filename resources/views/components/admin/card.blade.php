@props([
    'padding' => true,
    'flush' => false,
])

<div {{ $attributes->merge(['class' => 'admin-card overflow-hidden'.($flush ? '' : '')]) }}>
    @isset($header)
        <div class="border-b border-tremor-border px-5 py-4">
            {{ $header }}
        </div>
    @endisset

    <div @class(['px-5 py-5' => $padding && ! isset($body), 'p-0' => ! $padding])>
        @isset($body)
            {{ $body }}
        @else
            {{ $slot }}
        @endisset
    </div>

    @isset($footer)
        <div class="border-t border-tremor-border bg-tremor-background-muted/60 px-5 py-4">
            {{ $footer }}
        </div>
    @endisset
</div>
