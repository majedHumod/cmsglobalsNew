{{-- Homepage subscription plans only — Tremor-styled, scoped to this include. --}}
@php
    $plans = $subscriptionPlans ?? collect();
@endphp

<section id="homepage-subscription-plans" class="hps-section" dir="rtl">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="hps-header">
            <p class="hps-kicker">الاشتراكات</p>
            <h2 class="hps-title">خطط الاشتراك</h2>
            <p class="hps-subtitle">اختر العرض المناسب من حيث السعر والمدة</p>
        </div>

        <div class="hps-grid">
            @forelse($plans as $plan)
                <article class="hps-card {{ $plan->hasDiscount() ? 'hps-card--discounted' : '' }}">
                    <div class="hps-card__body">
                        <div class="hps-card__top">
                            <div>
                                <h3 class="hps-card__name">{{ $plan->name }}</h3>
                                @if($plan->membershipType)
                                    <p class="hps-card__path">المسار: {{ $plan->membershipType->name }}</p>
                                @endif
                            </div>
                            <span class="hps-card__badge">{{ $plan->gender_scope_label }}</span>
                        </div>

                        @if($plan->description)
                            <p class="hps-card__desc">{{ $plan->description }}</p>
                        @endif

                        <div class="hps-card__price">
                            <x-subscription-plan-price :plan="$plan" />
                            <p class="hps-card__duration">/ {{ $plan->duration_text }}</p>
                        </div>

                        @if(!empty($plan->features) && is_array($plan->features))
                            <ul class="hps-card__features">
                                @foreach($plan->features as $feature)
                                    <li>
                                        <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span>{{ $feature }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="hps-card__spacer"></div>
                        @endif

                        <a href="{{ route('subscription-plans.public') }}" class="hps-card__cta btn-brand">
                            اشترك الآن
                        </a>
                    </div>
                </article>
            @empty
                <div class="hps-empty">
                    لا توجد خطط اشتراك متاحة حالياً
                </div>
            @endforelse
        </div>

        @if($plans->isNotEmpty())
            <div class="hps-footer">
                <a href="{{ route('subscription-plans.public') }}" class="hps-footer__link">
                    عرض كل خطط الاشتراك
                </a>
            </div>
        @endif
    </div>
</section>

<style>
    /* Scoped to homepage subscription plans — does not affect other landing sections */
    #homepage-subscription-plans {
        --hps-ink: #111827;
        --hps-muted: #6b7280;
        --hps-subtle: #9ca3af;
        --hps-border: #e5e7eb;
        --hps-surface: #ffffff;
        --hps-bg: #f9fafb;
        --hps-brand: var(--primary-color, #6366f1);
        --hps-brand-hover: var(--primary-hover, #4f46e5);
        --hps-brand-soft: var(--primary-soft, #eef2ff);
        --hps-radius: 0.75rem;
        background: var(--hps-bg);
        padding: 4rem 0;
    }

    #homepage-subscription-plans .hps-header {
        text-align: center;
        margin-bottom: 2.5rem;
    }

    #homepage-subscription-plans .hps-kicker {
        margin: 0 0 0.5rem;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        color: var(--hps-brand);
    }

    #homepage-subscription-plans .hps-title {
        margin: 0;
        font-size: clamp(1.75rem, 2.5vw, 1.875rem);
        font-weight: 800;
        color: var(--hps-ink);
    }

    #homepage-subscription-plans .hps-subtitle {
        margin: 1rem 0 0;
        font-size: 1.125rem;
        color: var(--hps-muted);
    }

    #homepage-subscription-plans .hps-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
        align-items: stretch;
    }

    @media (min-width: 768px) {
        #homepage-subscription-plans .hps-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (min-width: 1024px) {
        #homepage-subscription-plans .hps-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    #homepage-subscription-plans .hps-card {
        display: flex;
        flex-direction: column;
        height: 100%;
        background: var(--hps-surface);
        border: 1px solid var(--hps-border);
        border-radius: var(--hps-radius);
        box-shadow: 0 1px 3px rgba(17, 24, 39, 0.08), 0 1px 2px rgba(17, 24, 39, 0.04);
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    #homepage-subscription-plans .hps-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 24px rgba(17, 24, 39, 0.1);
    }

    #homepage-subscription-plans .hps-card--discounted {
        border-color: color-mix(in srgb, var(--hps-brand) 28%, var(--hps-border));
    }

    #homepage-subscription-plans .hps-card__body {
        display: flex;
        flex-direction: column;
        height: 100%;
        padding: 1.5rem;
    }

    #homepage-subscription-plans .hps-card__top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
    }

    #homepage-subscription-plans .hps-card__name {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 800;
        line-height: 1.35;
        color: var(--hps-ink);
    }

    #homepage-subscription-plans .hps-card__path {
        margin: 0.35rem 0 0;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--hps-brand);
    }

    #homepage-subscription-plans .hps-card__badge {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        border-radius: 9999px;
        padding: 0.25rem 0.7rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--hps-brand);
        background: var(--hps-brand-soft);
        white-space: nowrap;
    }

    #homepage-subscription-plans .hps-card__desc {
        margin: 0 0 1rem;
        font-size: 0.9rem;
        line-height: 1.6;
        color: var(--hps-muted);
    }

    #homepage-subscription-plans .hps-card__price {
        margin-bottom: 0.75rem;
    }

    #homepage-subscription-plans .hps-card__price .subscription-plan-price {
        color: inherit;
    }

    #homepage-subscription-plans .hps-card__price .subscription-plan-price [aria-label="سعر البيع بعد الخصم"],
    #homepage-subscription-plans .hps-card__price .subscription-plan-price > div:last-child {
        color: var(--hps-brand) !important;
    }

    #homepage-subscription-plans .hps-card__duration {
        margin: 0.35rem 0 0;
        font-size: 0.875rem;
        color: var(--hps-subtle);
    }

    #homepage-subscription-plans .hps-card__features {
        list-style: none;
        margin: 0 0 1.25rem;
        padding: 0;
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
    }

    #homepage-subscription-plans .hps-card__features li {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        font-size: 0.9rem;
        line-height: 1.45;
        color: #374151;
    }

    #homepage-subscription-plans .hps-card__features svg {
        width: 1.1rem;
        height: 1.1rem;
        margin-top: 0.15rem;
        flex-shrink: 0;
        color: #10b981;
    }

    #homepage-subscription-plans .hps-card__spacer {
        flex: 1 1 auto;
        min-height: 1rem;
    }

    #homepage-subscription-plans .hps-card__cta {
        margin-top: auto;
        display: block;
        width: 100%;
        text-align: center;
        border-radius: 0.375rem;
        padding: 0.7rem 1rem;
        font-size: 0.95rem;
        font-weight: 600;
        text-decoration: none;
        color: #ffffff !important;
        background-color: var(--hps-brand) !important;
        background-image: none !important;
        border: none;
        transition: background-color 0.2s ease, transform 0.2s ease;
    }

    #homepage-subscription-plans .hps-card__cta:hover,
    #homepage-subscription-plans .hps-card__cta:focus {
        background-color: var(--hps-brand-hover) !important;
        color: #ffffff !important;
        transform: translateY(-1px);
    }

    #homepage-subscription-plans .hps-empty {
        grid-column: 1 / -1;
        text-align: center;
        padding: 2rem 1rem;
        color: var(--hps-muted);
        font-size: 0.95rem;
    }

    #homepage-subscription-plans .hps-footer {
        margin-top: 2rem;
        text-align: center;
    }

    #homepage-subscription-plans .hps-footer__link {
        font-weight: 600;
        color: var(--hps-brand);
        text-decoration: none;
    }

    #homepage-subscription-plans .hps-footer__link:hover {
        color: var(--hps-brand-hover);
        text-decoration: underline;
    }

    @media (max-width: 640px) {
        #homepage-subscription-plans {
            padding: 3rem 0;
        }

        #homepage-subscription-plans .hps-header {
            margin-bottom: 1.75rem;
        }

        #homepage-subscription-plans .hps-card__body {
            padding: 1.25rem;
        }
    }
</style>
