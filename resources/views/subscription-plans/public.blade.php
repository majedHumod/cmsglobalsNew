@extends('layouts.public-page')

@section('title', 'خطط الاشتراك')

@section('content')
{{-- Scoped Tremor styling for this page content only — layout/nav/footer untouched --}}
<section id="public-subscription-plans" class="psp-section" dir="rtl">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="psp-breadcrumb" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 space-x-reverse md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('home') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-indigo-600">
                        <svg class="ml-2 h-4 w-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                        </svg>
                        الرئيسية
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="h-6 w-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">خطط الاشتراك</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="psp-header">
            <p class="psp-kicker">الاشتراكات</p>
            <h1 class="psp-title">خطط الاشتراك</h1>
            <p class="psp-subtitle">اختر الخطة المناسبة لمدة اشتراكك مع ربطها تلقائياً بالمسار التدريبي الصحيح.</p>
        </div>

        <div class="psp-grid">
            @forelse($plans as $plan)
                <article class="psp-card {{ $plan->hasDiscount() ? 'psp-card--discounted' : '' }}">
                    <div class="psp-card__body">
                        <div class="psp-card__top">
                            <div>
                                <h2 class="psp-card__name">{{ $plan->name }}</h2>
                                @if($plan->membershipType)
                                    <p class="psp-card__path">المسار: {{ $plan->membershipType->name }}</p>
                                @endif
                            </div>
                            <span class="psp-card__badge">{{ $plan->gender_scope_label }}</span>
                        </div>

                        @if($plan->description)
                            <p class="psp-card__desc">{{ $plan->description }}</p>
                        @endif

                        <div class="psp-card__price">
                            <x-subscription-plan-price :plan="$plan" />
                            <p class="psp-card__duration">/ {{ $plan->duration_text }}</p>
                        </div>

                        @if(!empty($plan->features) && is_array($plan->features))
                            <ul class="psp-card__features">
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
                            <div class="psp-card__spacer"></div>
                        @endif

                        @auth
                            <form action="{{ route('subscription-plans.subscribe', $plan) }}" method="POST" class="psp-card__form">
                                @csrf
                                <button type="submit" class="psp-card__cta btn-brand">اشترك الآن</button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="psp-card__cta btn-brand">سجل الدخول للاشتراك</a>
                        @endauth
                    </div>
                </article>
            @empty
                <div class="psp-empty">
                    لا توجد خطط اشتراك مفعلة حالياً.
                </div>
            @endforelse
        </div>
    </div>
</section>

<style>
    /* Scoped to /subscription-plans content only — does not alter public-page layout */
    #public-subscription-plans {
        --psp-ink: #111827;
        --psp-muted: #6b7280;
        --psp-subtle: #9ca3af;
        --psp-border: #e5e7eb;
        --psp-surface: #ffffff;
        --psp-bg: transparent;
        --psp-brand: var(--primary-color, #6366f1);
        --psp-brand-hover: var(--primary-hover, #4f46e5);
        --psp-brand-soft: var(--primary-soft, #eef2ff);
        --psp-radius: 0.75rem;
        background: var(--psp-bg);
        padding: 0 0 3.5rem;
    }

    #public-subscription-plans .psp-breadcrumb {
        display: flex;
        margin: 0 0 1.25rem;
    }

    #public-subscription-plans .psp-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    #public-subscription-plans .psp-kicker {
        margin: 0 0 0.5rem;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        color: var(--psp-brand);
    }

    #public-subscription-plans .psp-title {
        margin: 0;
        font-size: clamp(1.75rem, 2.5vw, 1.875rem);
        font-weight: 800;
        color: var(--psp-ink);
    }

    #public-subscription-plans .psp-subtitle {
        margin: 0.85rem auto 0;
        max-width: 40rem;
        font-size: 1.05rem;
        line-height: 1.7;
        color: var(--psp-muted);
    }

    #public-subscription-plans .psp-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
        align-items: stretch;
    }

    @media (min-width: 768px) {
        #public-subscription-plans .psp-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (min-width: 1280px) {
        #public-subscription-plans .psp-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    #public-subscription-plans .psp-card {
        display: flex;
        flex-direction: column;
        height: 100%;
        background: var(--psp-surface);
        border: 1px solid var(--psp-border);
        border-radius: var(--psp-radius);
        box-shadow: 0 1px 3px rgba(17, 24, 39, 0.08), 0 1px 2px rgba(17, 24, 39, 0.04);
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    #public-subscription-plans .psp-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 24px rgba(17, 24, 39, 0.1);
    }

    #public-subscription-plans .psp-card--discounted {
        border-color: color-mix(in srgb, var(--psp-brand) 28%, var(--psp-border));
    }

    #public-subscription-plans .psp-card__body {
        display: flex;
        flex-direction: column;
        height: 100%;
        padding: 1.5rem;
    }

    #public-subscription-plans .psp-card__top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
    }

    #public-subscription-plans .psp-card__name {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 800;
        line-height: 1.35;
        color: var(--psp-ink);
    }

    #public-subscription-plans .psp-card__path {
        margin: 0.35rem 0 0;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--psp-brand);
    }

    #public-subscription-plans .psp-card__badge {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        border-radius: 9999px;
        padding: 0.25rem 0.7rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--psp-brand);
        background: var(--psp-brand-soft);
        white-space: nowrap;
    }

    #public-subscription-plans .psp-card__desc {
        margin: 0 0 1rem;
        font-size: 0.9rem;
        line-height: 1.6;
        color: var(--psp-muted);
    }

    #public-subscription-plans .psp-card__price {
        margin-bottom: 0.75rem;
    }

    #public-subscription-plans .psp-card__price .subscription-plan-price [aria-label="سعر البيع بعد الخصم"],
    #public-subscription-plans .psp-card__price .subscription-plan-price > div:last-child {
        color: var(--psp-brand) !important;
    }

    #public-subscription-plans .psp-card__duration {
        margin: 0.35rem 0 0;
        font-size: 0.875rem;
        color: var(--psp-subtle);
    }

    #public-subscription-plans .psp-card__features {
        list-style: none;
        margin: 0 0 1.25rem;
        padding: 0;
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
    }

    #public-subscription-plans .psp-card__features li {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        font-size: 0.9rem;
        line-height: 1.45;
        color: #374151;
    }

    #public-subscription-plans .psp-card__features svg {
        width: 1.1rem;
        height: 1.1rem;
        margin-top: 0.15rem;
        flex-shrink: 0;
        color: #10b981;
    }

    #public-subscription-plans .psp-card__spacer {
        flex: 1 1 auto;
        min-height: 1rem;
    }

    #public-subscription-plans .psp-card__form {
        margin-top: auto;
    }

    #public-subscription-plans .psp-card__cta {
        margin-top: auto;
        display: block;
        width: 100%;
        text-align: center;
        border-radius: 0.375rem;
        padding: 0.8rem 1rem;
        font-size: 0.95rem;
        font-weight: 600;
        text-decoration: none;
        color: #ffffff !important;
        background-color: var(--psp-brand) !important;
        background-image: none !important;
        border: none;
        cursor: pointer;
        transition: background-color 0.2s ease, transform 0.2s ease;
    }

    #public-subscription-plans .psp-card__form .psp-card__cta {
        margin-top: 0;
    }

    #public-subscription-plans .psp-card__cta:hover,
    #public-subscription-plans .psp-card__cta:focus {
        background-color: var(--psp-brand-hover) !important;
        color: #ffffff !important;
        transform: translateY(-1px);
    }

    #public-subscription-plans .psp-empty {
        grid-column: 1 / -1;
        text-align: center;
        padding: 2.5rem 1.25rem;
        border: 1px dashed var(--psp-border);
        border-radius: var(--psp-radius);
        background: #ffffff;
        color: var(--psp-muted);
        font-size: 0.95rem;
    }

    @media (max-width: 640px) {
        #public-subscription-plans {
            padding: 0 0 2.5rem;
        }

        #public-subscription-plans .psp-breadcrumb {
            margin-bottom: 1rem;
        }

        #public-subscription-plans .psp-header {
            margin-bottom: 1.5rem;
        }

        #public-subscription-plans .psp-card__body {
            padding: 1.25rem;
        }
    }
</style>
@endsection
