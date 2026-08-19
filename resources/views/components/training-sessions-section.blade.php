{{-- Homepage training sessions only — Tremor-styled, scoped to this include. --}}
@php
    $trainingSectionsEnabled = (bool) \App\Models\SiteSetting::get('training_sessions_enabled', true);
    $trainingSectionsTitle = \App\Models\SiteSetting::get('training_sessions_title', 'مدربونا الخبراء');
    $trainingSectionsDescription = \App\Models\SiteSetting::get(
        'training_sessions_description',
        'تعرف على مدربينا المعتمدين المتخصصين في إرشادك خلال رحلتك مع الدعم الشخصي والتعليمات الواعية وممارسات العافية الشاملة'
    );
@endphp

@if($trainingSectionsEnabled)
<section id="homepage-training-sessions" class="bg-white py-0" dir="rtl">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @php
            try {
                $trainingSessions = \App\Models\TrainingSession::getHomepageSessions(auth()->user());
            } catch (\Exception $e) {
                $trainingSessions = collect([]);
            }
        @endphp

        @if($trainingSessions->count() > 0)
            <div class="hts-layout grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-12 items-start py-16">
                {{-- Intro column --}}
                <div class="hts-intro lg:sticky lg:top-20">
                    <div class="space-y-6 max-w-xl">
                        <div class="space-y-3">
                            <p class="hts-kicker">
                                الجلسات التدريبية
                            </p>
                            <h2 class="hts-title">
                                {{ $trainingSectionsTitle }}
                            </h2>
                            <p class="hts-description">
                                {{ $trainingSectionsDescription }}
                            </p>
                        </div>

                        <div>
                            <a href="{{ route('training-sessions.all') }}" class="hts-browse-btn btn-brand">
                                <span>تصفح جميع الجلسات</span>
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Sessions grid --}}
                <div class="hts-grid grid grid-cols-1 sm:grid-cols-2 gap-5">
                    @foreach($trainingSessions as $session)
                        <article class="hts-card {{ $session->image ? 'hts-card--has-image' : 'hts-card--no-image' }}">
                            @if($session->image)
                                <img
                                    src="{{ Storage::url($session->image) }}"
                                    alt="{{ $session->title }}"
                                    class="hts-card__media"
                                    loading="lazy"
                                    decoding="async"
                                >
                            @else
                                <div class="hts-card__fallback" aria-hidden="true"></div>
                            @endif

                            <div class="hts-card__overlay" aria-hidden="true"></div>

                            <div class="hts-card__body">
                                <h3 class="hts-card__title">{{ $session->title }}</h3>

                                @if($session->description)
                                    <p class="hts-card__excerpt">
                                        {{ Str::limit(strip_tags($session->description), 80) }}
                                    </p>
                                @endif

                                <div class="hts-card__meta">
                                    <span class="hts-card__duration">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        {{ $session->duration_text }}
                                    </span>
                                    <span class="hts-card__price">{{ $session->formatted_price }}</span>
                                </div>

                                <a href="{{ route('training-sessions.show', $session) }}" class="hts-card__cta">
                                    احجز الآن
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>

<style>
    /* Scoped to #homepage-training-sessions only */
    #homepage-training-sessions {
        --hts-ink: #111827;
        --hts-muted: #6b7280;
        --hts-brand: var(--primary-color, #f97316);
        --hts-brand-deep: var(--primary-deep, #c2410c);
        --hts-surface: #ffffff;
        --hts-radius: 0.75rem;
    }

    #homepage-training-sessions .hts-kicker {
        margin: 0;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        color: var(--hts-brand);
    }

    #homepage-training-sessions .hts-title {
        margin: 0;
        font-size: clamp(1.75rem, 2.5vw, 2.25rem);
        font-weight: 800;
        line-height: 1.25;
        color: var(--hts-ink);
    }

    #homepage-training-sessions .hts-description {
        margin: 0;
        font-size: 1.05rem;
        line-height: 1.7;
        color: var(--hts-muted);
    }

    #homepage-training-sessions .hts-browse-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border-radius: 0.375rem;
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
        font-weight: 600;
        color: #ffffff !important;
        text-decoration: none;
        border: none;
        background-color: var(--primary-color, #6366f1) !important;
        background-image: none !important;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
    }

    #homepage-training-sessions .hts-browse-btn:hover,
    #homepage-training-sessions .hts-browse-btn:focus {
        background-color: var(--primary-hover, #4f46e5) !important;
        background-image: none !important;
        color: #ffffff !important;
        transform: translateY(-2px);
        box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
    }

    #homepage-training-sessions .hts-intro {
        margin-bottom: 0;
    }

    #homepage-training-sessions .hts-grid {
        margin-top: 0;
    }

    @media (max-width: 1023px) {
        #homepage-training-sessions .hts-layout {
            gap: 2.5rem !important;
            row-gap: 2.5rem !important;
        }

        #homepage-training-sessions .hts-intro {
            margin-bottom: 0.5rem;
            padding-bottom: 0.25rem;
        }

        #homepage-training-sessions .hts-browse-btn {
            margin-bottom: 0.75rem;
        }

        #homepage-training-sessions .hts-grid {
            margin-top: 1.25rem;
            padding-top: 0.25rem;
        }
    }

    #homepage-training-sessions .hts-card {
        position: relative;
        display: flex;
        flex-direction: column;
        height: 20rem;
        overflow: hidden;
        border-radius: var(--hts-radius);
        border: 1px solid rgba(229, 231, 235, 0.55);
        box-shadow: 0 1px 3px rgba(17, 24, 39, 0.1), 0 1px 2px rgba(17, 24, 39, 0.06);
        background: #111827;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        will-change: transform;
    }

    #homepage-training-sessions .hts-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 30px rgba(17, 24, 39, 0.18);
    }

    #homepage-training-sessions .hts-card__media,
    #homepage-training-sessions .hts-card__fallback,
    #homepage-training-sessions .hts-card__overlay {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
    }

    #homepage-training-sessions .hts-card__media {
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    #homepage-training-sessions .hts-card:hover .hts-card__media {
        transform: scale(1.03);
    }

    /* No-image fallback: always a dark brand gradient (never white) */
    #homepage-training-sessions .hts-card__fallback {
        background: linear-gradient(
            145deg,
            var(--hts-brand) 0%,
            var(--hts-brand-deep) 48%,
            #111827 100%
        );
    }

    /* Dark overlay so white text stays readable on any photo */
    #homepage-training-sessions .hts-card__overlay {
        background: linear-gradient(
            to top,
            rgba(0, 0, 0, 0.88) 0%,
            rgba(0, 0, 0, 0.55) 42%,
            rgba(0, 0, 0, 0.22) 100%
        );
        pointer-events: none;
    }

    #homepage-training-sessions .hts-card__body {
        position: relative;
        z-index: 2;
        margin-top: auto;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        height: 100%;
        padding: 1.25rem;
        color: #ffffff !important;
        text-align: right;
    }

    #homepage-training-sessions .hts-card__title,
    #homepage-training-sessions .hts-card__excerpt,
    #homepage-training-sessions .hts-card__meta,
    #homepage-training-sessions .hts-card__duration,
    #homepage-training-sessions .hts-card__price {
        color: #ffffff !important;
    }

    #homepage-training-sessions .hts-card__title {
        margin: 0;
        font-size: 1.125rem;
        font-weight: 800;
        line-height: 1.35;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.35);
    }

    #homepage-training-sessions .hts-card__excerpt {
        margin: 0.5rem 0 0;
        font-size: 0.875rem;
        line-height: 1.55;
        color: rgba(255, 255, 255, 0.88) !important;
    }

    #homepage-training-sessions .hts-card__meta {
        margin-top: 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        font-size: 0.875rem;
    }

    #homepage-training-sessions .hts-card__duration {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 9999px;
        padding: 0.25rem 0.65rem;
        background: rgba(0, 0, 0, 0.35);
        backdrop-filter: blur(4px);
    }

    #homepage-training-sessions .hts-card__price {
        font-size: 1.05rem;
        font-weight: 800;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.35);
    }

    #homepage-training-sessions .hts-card__cta {
        margin-top: 1rem;
        display: block;
        width: 100%;
        border-radius: 9999px;
        padding: 0.7rem 1.25rem;
        text-align: center;
        font-size: 0.9rem;
        font-weight: 700;
        text-decoration: none;
        color: #111827 !important;
        background: #ffffff !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
        transition: background-color 0.2s ease, transform 0.2s ease;
    }

    #homepage-training-sessions .hts-card__cta:hover {
        background: #f3f4f6 !important;
        color: #111827 !important;
        transform: translateY(-1px);
    }

    @media (max-width: 640px) {
        #homepage-training-sessions .hts-card {
            height: 18rem;
        }
    }
</style>
@endif
