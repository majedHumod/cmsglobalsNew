@props([
    'title' => null,
    'description' => null,
])

@if($title || $description || $slot->isNotEmpty())
    <div {{ $attributes->merge(['class' => 'mb-5']) }}>
        @if($title)
            <h2 class="text-tremor-title font-semibold text-tremor-content-strong">{{ $title }}</h2>
        @endif
        @if($description)
            <p class="mt-1 text-sm text-tremor-content">{{ $description }}</p>
        @endif
        {{ $slot }}
    </div>
@endif
