@props([
    'title' => 'لا توجد بيانات',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'px-6 py-12 text-center']) }}>
    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-tremor-background-subtle text-tremor-content-subtle">
        @isset($icon)
            {{ $icon }}
        @else
            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6M7 4h10a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z"/>
            </svg>
        @endisset
    </div>
    <h3 class="text-tremor-title font-semibold text-tremor-content-strong">{{ $title }}</h3>
    @if($description)
        <p class="mt-1 text-sm text-tremor-content">{{ $description }}</p>
    @endif
    @isset($actions)
        <div class="mt-5 flex flex-wrap items-center justify-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>
