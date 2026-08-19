{{-- Public-site brand tokens only (include AFTER Vite CSS). Do not use on admin layout. --}}
@php
    $brandFontKey = \App\Support\Branding::fontKey();
    $brandFont = \App\Support\Branding::font($brandFontKey);
    $palette = \App\Support\Branding::palette();
    $brandFontHref = 'https://fonts.bunny.net/css?family=' . $brandFont['bunny'] . '&display=swap';
@endphp

<link rel="preconnect" href="https://fonts.bunny.net">
<link
    id="brand-font-stylesheet"
    href="{{ $brandFontHref }}"
    rel="stylesheet"
    data-font-key="{{ $brandFontKey }}"
/>

<style id="brand-tokens-style">
    :root {
        --primary-color: {{ $palette['primary'] }};
        --secondary-color: {{ $palette['secondary'] }};
        --brand-font: {!! $brandFont['family'] !!};

        --primary-hover: {{ $palette['primary_hover'] }};
        --primary-soft: {{ $palette['primary_soft'] }};
        --primary-muted: {{ $palette['primary_muted'] }};
        --primary-tint: {{ $palette['primary_tint'] }};
        --primary-deep: {{ $palette['primary_deep'] }};

        --brand-accent: {{ $palette['accent'] }};
        --brand-accent-hover: {{ $palette['accent_hover'] }};
        --brand-companion: {{ $palette['companion'] }};
        --brand-companion-hover: {{ $palette['companion_hover'] }};

        --brand-gradient-from: {{ $palette['gradient_from'] }};
        --brand-gradient-to: {{ $palette['gradient_to'] }};
        --brand-gradient-cta-from: {{ $palette['gradient_cta_from'] }};
        --brand-gradient-cta-to: {{ $palette['gradient_cta_to'] }};
        --brand-gradient-from-hover: {{ $palette['gradient_from_hover'] }};
        --brand-gradient-to-hover: {{ $palette['gradient_to_hover'] }};
        --brand-gradient-cta-from-hover: {{ $palette['gradient_cta_from_hover'] }};
        --brand-gradient-cta-to-hover: {{ $palette['gradient_cta_to_hover'] }};

        --brand-surface-from: {{ $palette['surface_from'] }};
        --brand-surface-to: {{ $palette['surface_to'] }};

        --secondary-hover: {{ $palette['secondary_hover'] }};
        --secondary-soft: {{ $palette['secondary_soft'] }};
    }

    html {
        font-family: {!! $brandFont['family'] !!} !important;
    }

    body,
    body.font-sans,
    .font-sans,
    button,
    input,
    select,
    textarea {
        font-family: {!! $brandFont['family'] !!} !important;
    }

    /* Brand utilities */
    .bg-primary,
    .bg-brand { background-color: var(--primary-color) !important; }
    .bg-brand-soft { background-color: var(--primary-soft) !important; }
    .bg-brand-muted { background-color: var(--primary-muted) !important; }
    .text-primary,
    .text-brand { color: var(--primary-color) !important; }
    .border-primary,
    .border-brand { border-color: var(--primary-color) !important; }
    .bg-secondary { background-color: var(--secondary-color) !important; }
    .text-secondary { color: var(--secondary-color) !important; }
    .border-secondary { border-color: var(--secondary-color) !important; }

    .btn-brand {
        background-color: var(--primary-color) !important;
        color: #fff !important;
        border-color: transparent !important;
    }
    .btn-brand:hover,
    .btn-brand:focus {
        background-color: var(--primary-hover) !important;
        color: #fff !important;
    }

    .btn-brand-gradient {
        background-image: linear-gradient(to left, var(--brand-gradient-from), var(--brand-gradient-to)) !important;
        color: #fff !important;
        border-color: transparent !important;
    }
    .btn-brand-gradient:hover,
    .btn-brand-gradient:focus {
        background-image: linear-gradient(to left, var(--brand-gradient-from-hover), var(--brand-gradient-to-hover)) !important;
    }

    .btn-brand-cta {
        background-image: linear-gradient(to left, var(--brand-gradient-cta-from), var(--brand-gradient-cta-to)) !important;
        color: #fff !important;
        border-color: transparent !important;
    }
    .btn-brand-cta:hover,
    .btn-brand-cta:focus {
        background-image: linear-gradient(to left, var(--brand-gradient-cta-from-hover), var(--brand-gradient-cta-to-hover)) !important;
    }

    .bg-brand-surface {
        background-image: linear-gradient(to bottom right, var(--brand-surface-from), var(--brand-surface-to)) !important;
    }

    .link-brand { color: var(--primary-color) !important; }
    .link-brand:hover,
    .link-brand:focus { color: var(--primary-hover) !important; }
    .hover\:text-brand:hover { color: var(--primary-color) !important; }
    .ring-brand:focus,
    .focus\:ring-brand:focus { --tw-ring-color: var(--primary-color); }

    /*
     | Remap hardcoded Tailwind indigo/blue/purple/pink utilities on public pages
     | so existing markup stays harmonious with the tenant brand color.
     */
    .bg-indigo-50,
    .bg-purple-50 { background-color: var(--primary-tint) !important; }
    .bg-indigo-100,
    .bg-purple-100 { background-color: var(--primary-soft) !important; }
    .bg-indigo-200 { background-color: var(--primary-muted) !important; }
    .bg-indigo-400,
    .bg-indigo-500,
    .bg-indigo-600,
    .bg-blue-500,
    .bg-blue-600,
    .bg-purple-500,
    .bg-purple-600,
    .bg-pink-500,
    .bg-pink-600 { background-color: var(--primary-color) !important; }
    .bg-indigo-700,
    .bg-blue-700,
    .bg-purple-700,
    .bg-pink-700 { background-color: var(--primary-hover) !important; }

    .hover\:bg-indigo-50:hover { background-color: var(--primary-tint) !important; }
    .hover\:bg-indigo-100:hover,
    .hover\:bg-indigo-200:hover { background-color: var(--primary-soft) !important; }
    .hover\:bg-indigo-600:hover,
    .hover\:bg-indigo-700:hover,
    .hover\:bg-blue-700:hover,
    .hover\:bg-purple-700:hover,
    .hover\:bg-pink-700:hover { background-color: var(--primary-hover) !important; }

    .text-indigo-200 { color: var(--primary-muted) !important; }
    .text-indigo-500,
    .text-indigo-600,
    .text-indigo-700,
    .text-indigo-800,
    .text-blue-600,
    .text-purple-600,
    .text-pink-600 { color: var(--primary-color) !important; }

    .hover\:text-indigo-600:hover,
    .hover\:text-indigo-700:hover,
    .hover\:text-indigo-800:hover,
    .group:hover .group-hover\:text-indigo-600 { color: var(--primary-hover) !important; }

    .border-indigo-100,
    .border-indigo-200 { border-color: var(--primary-soft) !important; }
    .border-indigo-400,
    .border-indigo-500,
    .border-indigo-600,
    .border-indigo-700 { border-color: var(--primary-color) !important; }

    .ring-indigo-500,
    .focus\:ring-indigo-500:focus,
    .focus\:border-indigo-500:focus {
        --tw-ring-color: var(--primary-color) !important;
        border-color: var(--primary-color) !important;
    }

    /* Dual-tone CTA: primary → companion (sessions / pages) */
    .from-indigo-600,
    .from-indigo-500,
    .from-blue-600,
    .from-blue-500 {
        --tw-gradient-from: var(--brand-gradient-from) var(--tw-gradient-from-position) !important;
        --tw-gradient-to: color-mix(in srgb, var(--brand-gradient-from) 0%, transparent) var(--tw-gradient-to-position) !important;
        --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to) !important;
    }
    .to-blue-600,
    .to-blue-500,
    .to-indigo-600 {
        --tw-gradient-to: var(--brand-gradient-to) var(--tw-gradient-to-position) !important;
    }
    .hover\:from-indigo-700:hover,
    .hover\:from-blue-700:hover {
        --tw-gradient-from: var(--brand-gradient-from-hover) var(--tw-gradient-from-position) !important;
        --tw-gradient-to: color-mix(in srgb, var(--brand-gradient-from-hover) 0%, transparent) var(--tw-gradient-to-position) !important;
        --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to) !important;
    }
    .hover\:to-blue-700:hover,
    .hover\:to-indigo-700:hover {
        --tw-gradient-to: var(--brand-gradient-to-hover) var(--tw-gradient-to-position) !important;
    }

    /* Expressive CTA: primary → accent (testimonials purple→pink style) */
    .from-purple-600,
    .from-purple-500,
    .from-pink-600 {
        --tw-gradient-from: var(--brand-gradient-cta-from) var(--tw-gradient-from-position) !important;
        --tw-gradient-to: color-mix(in srgb, var(--brand-gradient-cta-from) 0%, transparent) var(--tw-gradient-to-position) !important;
        --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to) !important;
    }
    .to-pink-600,
    .to-pink-500,
    .to-purple-600,
    .to-purple-500 {
        --tw-gradient-to: var(--brand-gradient-cta-to) var(--tw-gradient-to-position) !important;
    }
    .hover\:from-purple-700:hover,
    .hover\:from-pink-700:hover {
        --tw-gradient-from: var(--brand-gradient-cta-from-hover) var(--tw-gradient-from-position) !important;
        --tw-gradient-to: color-mix(in srgb, var(--brand-gradient-cta-from-hover) 0%, transparent) var(--tw-gradient-to-position) !important;
        --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to) !important;
    }
    .hover\:to-pink-700:hover,
    .hover\:to-purple-700:hover {
        --tw-gradient-to: var(--brand-gradient-cta-to-hover) var(--tw-gradient-to-position) !important;
    }

    /* Card / avatar surface gradients */
    .from-indigo-400 {
        --tw-gradient-from: var(--brand-surface-from) var(--tw-gradient-from-position) !important;
        --tw-gradient-to: color-mix(in srgb, var(--brand-surface-from) 0%, transparent) var(--tw-gradient-to-position) !important;
        --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to) !important;
    }
    .to-purple-500,
    .to-blue-500 {
        --tw-gradient-to: var(--brand-surface-to) var(--tw-gradient-to-position) !important;
    }

    /* Session card fallback without photo */
    .bg-gradient-to-br.from-indigo-500.to-purple-600 {
        background-image: linear-gradient(to bottom right, var(--brand-surface-from), var(--primary-deep)) !important;
    }
</style>
