@props([
    'plan',
    'size' => 'lg', // sm|lg
])

@php
    $priceClass = $size === 'sm'
        ? 'text-xl font-bold text-[color:var(--brand-primary,#4f46e5)]'
        : 'text-3xl md:text-4xl font-extrabold text-[color:var(--brand-primary,#4f46e5)]';
@endphp

<div {{ $attributes->merge(['class' => 'subscription-plan-price']) }}>
    @if($plan->hasDiscount())
        <div class="flex flex-wrap items-center gap-2 mb-1">
            <span
                class="text-base text-slate-400"
                style="text-decoration: line-through; text-decoration-thickness: 2px; text-decoration-color: #94a3b8;"
                aria-label="السعر قبل الخصم"
            >
                {{ $plan->formatted_compare_at_price }}
            </span>

            @if($plan->discountPercent())
                <span class="inline-flex items-center rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-semibold text-rose-700">
                    خصم {{ $plan->discountPercent() }}%
                </span>
            @endif
        </div>

        <div class="{{ $priceClass }}" aria-label="سعر البيع بعد الخصم">
            {{ $plan->formatted_price }}
        </div>

        <p class="mt-1 text-xs font-medium text-emerald-700">
            وفّر {{ number_format((float) $plan->compare_at_price - (float) $plan->price, 2) }} ريال
        </p>
    @else
        <div class="{{ $priceClass }}">
            {{ $plan->formatted_price }}
        </div>
    @endif
</div>
