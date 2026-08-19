<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="vapid-public-key" content="{{ env('VAPID_PUBLIC_KEY', '') }}">
    <link rel="manifest" href="/manifest.json">

    <title>{{ \App\Models\SiteSetting::get('site_name', config('app.name', 'Laravel')) }} - @yield('title')</title>

    <meta name="theme-color" content="#f97316">

    <!-- Admin uses a fixed UI font (not public brand font) -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=tajawal:400,500,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Tremor-compatible admin tokens: orange / gray / white / black */
        :root {
            --primary-color: #f97316;
            --secondary-color: #111827;

            --tremor-brand-faint: #fff7ed;
            --tremor-brand-muted: #ffedd5;
            --tremor-brand-subtle: #fdba74;
            --tremor-brand: #f97316;
            --tremor-brand-emphasis: #ea580c;
            --tremor-brand-inverted: #ffffff;

            --tremor-background-muted: #f9fafb;
            --tremor-background-subtle: #f3f4f6;
            --tremor-background: #ffffff;
            --tremor-background-emphasis: #374151;

            --tremor-border: #e5e7eb;
            --tremor-ring: #e5e7eb;

            --tremor-content-subtle: #9ca3af;
            --tremor-content: #6b7280;
            --tremor-content-emphasis: #374151;
            --tremor-content-strong: #111827;
            --tremor-content-inverted: #ffffff;

            --admin-bg: var(--tremor-background-muted);
            --admin-surface: var(--tremor-background);
            --admin-border: var(--tremor-border);
            --admin-ink: var(--tremor-content-strong);
            --admin-muted: var(--tremor-content);
            --admin-soft: var(--tremor-background-subtle);
        }

        html, body, body.font-sans, .font-sans {
            font-family: 'Tajawal', Tahoma, sans-serif !important;
        }

        .bg-primary { background-color: var(--tremor-brand); }
        .text-primary { color: var(--tremor-brand); }
        .border-primary { border-color: var(--tremor-brand); }
        .bg-secondary { background-color: var(--tremor-content-strong); }
        .text-secondary { color: var(--tremor-content-strong); }
        .border-secondary { border-color: var(--tremor-content-strong); }

        [x-cloak] { display: none !important; }
        html, body { height: 100%; }

        #admin-shell {
            height: 100vh;
            height: 100dvh;
            overflow: hidden;
            background: var(--tremor-background);
        }

        #admin-main {
            min-height: 0;
            -webkit-overflow-scrolling: touch;
            overscroll-behavior: contain;
            background: var(--tremor-background-muted);
        }

        .admin-card, .tremor-Card {
            background: var(--tremor-background);
            border: 1px solid var(--tremor-border);
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        }

        .admin-kpi, .tremor-MetricCard {
            background: var(--tremor-background);
            border: 1px solid var(--tremor-border);
            border-radius: 0.5rem;
            padding: 1rem 1.15rem;
            min-height: 6rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        }

        .admin-kpi-label, .tremor-Label {
            font-size: 0.875rem;
            color: var(--tremor-content);
            font-weight: 500;
        }

        .admin-kpi-value, .tremor-Metric {
            margin-top: 0.35rem;
            font-size: 1.875rem;
            line-height: 2.25rem;
            font-weight: 600;
            letter-spacing: -0.02em;
            color: var(--tremor-content-strong);
        }

        .admin-kpi-meta {
            margin-top: 0.35rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .admin-kpi-meta.up { color: var(--tremor-brand-emphasis); }
        .admin-kpi-meta.down { color: var(--tremor-content-strong); }
        .admin-kpi-meta.flat { color: var(--tremor-content); }

        .admin-pill, .tremor-Badge {
            display: inline-flex;
            align-items: center;
            border-radius: 9999px;
            padding: 0.15rem 0.55rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .admin-pill.up { background: var(--tremor-brand-faint); color: var(--tremor-brand-emphasis); }
        .admin-pill.down { background: var(--tremor-background-subtle); color: var(--tremor-content-strong); }
        .admin-pill.neutral { background: var(--tremor-background-subtle); color: var(--tremor-content-emphasis); }

        .admin-btn-dark, .tremor-Button {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            border-radius: 0.5rem;
            background: var(--tremor-content-strong);
            color: var(--tremor-content-inverted);
            font-size: 0.875rem;
            font-weight: 600;
            padding: 0.5rem 0.9rem;
            transition: background .15s ease;
        }
        .admin-btn-dark:hover { background: #000; color: #fff; }

        .admin-btn-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            border-radius: 0.5rem;
            background: var(--tremor-brand);
            color: #fff;
            font-size: 0.875rem;
            font-weight: 600;
            padding: 0.5rem 0.9rem;
        }
        .admin-btn-brand:hover { background: var(--tremor-brand-emphasis); color: #fff; }

        .admin-tab {
            padding: 0.55rem 0.15rem;
            margin-left: 1.25rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--tremor-content-subtle);
            border-bottom: 2px solid transparent;
        }
        .admin-tab.is-active {
            color: var(--tremor-content-strong);
            font-weight: 700;
            border-bottom-color: var(--tremor-brand);
        }

        .admin-nav-link {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            padding: 0.55rem 0.75rem;
            border-radius: 0.5rem;
            color: var(--tremor-content-emphasis);
            font-size: 0.875rem;
            font-weight: 500;
            transition: background-color .15s ease, color .15s ease;
        }
        .admin-nav-link:hover {
            background: var(--tremor-background-muted);
            color: var(--tremor-content-strong);
        }
        .admin-nav-link.is-active {
            background: var(--tremor-brand-faint);
            color: var(--tremor-brand-emphasis);
            font-weight: 600;
        }
        .admin-nav-link svg {
            width: 1.05rem;
            height: 1.05rem;
            flex-shrink: 0;
            opacity: 0.8;
            margin: 0 !important;
        }

        .admin-section-label {
            padding: 1.15rem 0.75rem 0.35rem;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--tremor-content-subtle);
        }

        .admin-search-wrap { position: relative; width: min(100%, 22rem); }
        .admin-search {
            width: 100%;
            border-radius: 0.5rem;
            border: 1px solid var(--tremor-border);
            background: var(--tremor-background);
            padding: 0.55rem 2.5rem 0.55rem 3.25rem;
            font-size: 0.875rem;
            color: var(--tremor-content-strong);
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        }
        .admin-search:focus {
            outline: none;
            border-color: var(--tremor-brand-subtle);
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
        }
        .admin-search-kbd {
            position: absolute;
            left: 0.65rem;
            top: 50%;
            transform: translateY(-50%);
            border: 1px solid var(--tremor-border);
            border-radius: 0.375rem;
            background: var(--tremor-background-muted);
            color: var(--tremor-content-subtle);
            font-size: 0.65rem;
            padding: 0.1rem 0.35rem;
            font-weight: 600;
        }

        .admin-icon-btn {
            display: inline-flex;
            height: 2.35rem;
            width: 2.35rem;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            color: var(--tremor-content);
            border: 1px solid transparent;
            transition: background-color .15s ease, color .15s ease, border-color .15s ease;
        }
        .admin-icon-btn:hover {
            background: var(--tremor-background-muted);
            border-color: var(--tremor-border);
            color: var(--tremor-content-strong);
        }

        .admin-weight {
            height: 0.35rem;
            border-radius: 999px;
            background: var(--tremor-background-subtle);
            overflow: hidden;
            min-width: 4rem;
        }
        .admin-weight > span {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: var(--tremor-brand);
        }

        #admin-sidebar nav {
            scrollbar-width: thin;
            scrollbar-color: transparent transparent;
        }
        #admin-sidebar nav:hover {
            scrollbar-color: rgba(156, 163, 175, 0.7) transparent;
        }
        #admin-sidebar nav::-webkit-scrollbar { width: 6px; }
        #admin-sidebar nav::-webkit-scrollbar-thumb {
            background: transparent;
            border-radius: 999px;
        }
        #admin-sidebar nav:hover::-webkit-scrollbar-thumb {
            background: rgba(156, 163, 175, 0.7);
        }

        main .overflow-x-auto,
        main table { max-width: 100%; }

        @media (max-width: 1023px) {
            #admin-sidebar {
                position: fixed !important;
                top: 0; bottom: 0; right: 0; left: auto;
                width: min(18.5rem, 88vw);
                height: 100%; max-height: 100dvh; z-index: 50;
                transform: translate3d(100%, 0, 0);
                visibility: hidden; pointer-events: none;
                transition: transform 0.3s ease-in-out, visibility 0.3s ease-in-out;
                overflow: hidden;
            }
            #admin-sidebar-overlay { display: none; }
            .admin-sidebar-open #admin-sidebar {
                transform: translate3d(0, 0, 0);
                visibility: visible; pointer-events: auto;
            }
            .admin-sidebar-open #admin-sidebar-overlay { display: block; }
        }

        @media (min-width: 1024px) {
            #admin-sidebar {
                position: relative !important;
                transform: none !important;
                visibility: visible !important;
                pointer-events: auto !important;
                width: 15.25rem; max-width: 15.25rem;
                height: 100%; overflow: hidden;
            }
            #admin-sidebar-overlay { display: none !important; }
        }

        @supports (padding: max(0px)) {
            #admin-sidebar { padding-bottom: env(safe-area-inset-bottom); }
        }
    </style>
</head>
<body class="font-sans antialiased bg-white overflow-hidden" data-push-prompt="{{ auth()->check() ? '1' : '0' }}">
    <div
        id="admin-shell"
        class="flex"
        x-data="{
            sidebarOpen: false,
            closeSidebar() { this.sidebarOpen = false },
            openSidebar() { this.sidebarOpen = true },
            toggleSidebar() { this.sidebarOpen = !this.sidebarOpen },
            onResize() { if (window.innerWidth >= 1024) this.sidebarOpen = false }
        }"
        x-init="window.addEventListener('resize', () => onResize())"
        @keydown.escape.window="closeSidebar()"
        :class="{ 'admin-sidebar-open': sidebarOpen }"
    >
        <!-- Mobile overlay -->
        <div
            id="admin-sidebar-overlay"
            class="fixed inset-0 z-40 bg-gray-900/50 lg:hidden"
            @click="closeSidebar()"
            data-admin-sidebar-close
            aria-hidden="true"
        ></div>

        <!-- Sidebar -->
        <aside
            id="admin-sidebar"
            class="z-50 flex h-full min-h-0 w-64 flex-shrink-0 flex-col border-l border-tremor-border bg-white"
            aria-label="قائمة لوحة التحكم"
        >
            @php
                $sidebarLogo = \App\Models\SiteSetting::get('site_logo');
                $sidebarSiteName = \App\Models\SiteSetting::get('site_name', config('app.name'));
                $sidebarBrandImage = $sidebarLogo
                    ? \Illuminate\Support\Facades\Storage::url($sidebarLogo)
                    : (auth()->check() ? auth()->user()->profile_photo_url : null);
            @endphp
            <div class="flex flex-shrink-0 items-center justify-between gap-2 px-4 py-5">
                <a href="{{ route('dashboard') }}" class="flex min-w-0 flex-1 items-center gap-2.5" title="{{ $sidebarSiteName }}">
                    @if($sidebarBrandImage)
                        <img
                            src="{{ $sidebarBrandImage }}"
                            alt="{{ $sidebarSiteName }}"
                            class="h-8 w-8 flex-shrink-0 rounded-lg object-cover {{ $sidebarLogo ? 'bg-white object-contain' : '' }}"
                        >
                    @else
                        <span class="inline-flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-black text-sm font-bold text-white">
                            {{ mb_substr($sidebarSiteName, 0, 1) }}
                        </span>
                    @endif
                    <span class="min-w-0 truncate text-[15px] font-bold text-gray-900">{{ $sidebarSiteName }}</span>
                </a>
                <button
                    type="button"
                    class="admin-icon-btn lg:hidden"
                    @click="closeSidebar()"
                    data-admin-sidebar-close
                    aria-label="إغلاق القائمة"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <nav class="min-h-0 flex-1 overflow-y-auto overscroll-contain py-1" @click="if (window.innerWidth < 1024 && $event.target.closest('a')) closeSidebar()">
                <div class="space-y-0.5 px-3 pb-6">
                    <p class="admin-section-label" style="padding-top:0.25rem">متابعة</p>
                    <a href="{{ route('dashboard') }}" class="admin-nav-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3m10-11v10a1 1 0 01-1 1h-3m-4 0h4"/></svg>
                        لوحة التحكم
                    </a>

                    {{-- 2. Content --}}
                    <p class="admin-section-label">المحتوى</p>

                    @hasanyrole('admin|user|client')
                    <a href="{{ route('notes.index') }}" class="admin-nav-link {{ request()->routeIs('notes.*') ? 'is-active' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                        </svg>
                        الملاحظات
                    </a>
                    @endhasanyrole

                    @can('view pages')
                    <a href="{{ route('pages.index') }}" class="admin-nav-link {{ request()->routeIs('pages.*') ? 'is-active' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                        </svg>
                        إدارة الصفحات
                    </a>
                    @endcan

                    @role('admin')
                    <a href="{{ route('articles.index') }}" class="admin-nav-link {{ request()->routeIs('articles.*') ? 'is-active' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2 5a2 2 0 012-2h8a2 2 0 012 2v10a2 2 0 002 2H4a2 2 0 01-2-2V5zm3 1h6v4H5V6zm6 6H5v2h6v-2z" clip-rule="evenodd"></path>
                        </svg>
                        المقالات
                    </a>

                    <a href="{{ route('admin.landing-pages.index') }}" class="admin-nav-link {{ request()->routeIs('admin.landing-pages.*') ? 'is-active' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"></path>
                        </svg>
                        الصفحة الرئيسية
                    </a>

                    <a href="{{ route('admin.faqs.index') }}" class="admin-nav-link {{ request()->routeIs('admin.faqs.*') ? 'is-active' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path>
                        </svg>
                        الأسئلة الشائعة
                    </a>

                    <a href="{{ route('admin.testimonials.index') }}" class="admin-nav-link {{ request()->routeIs('admin.testimonials.*') ? 'is-active' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z"></path>
                            <path d="M15 7v2a4 4 0 01-4 4H9.828l-1.766 1.767c.28.149.599.233.938.233h2l3 3v-3h2a2 2 0 002-2V9a2 2 0 00-2-2h-1z"></path>
                        </svg>
                        قصص النجاح
                    </a>

                    <a href="{{ url('/admin-cms') }}" class="admin-nav-link" target="_blank" rel="noopener">
                        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        تجربة Filament (لوحة جديدة)
                    </a>
                    @endrole

                    {{-- 3. Coach operations --}}
                    @hasanyrole('admin|coach')
                    <p class="admin-section-label">التدريب والعملاء</p>

                    <a href="{{ route('coach.workspace') }}" class="admin-nav-link {{ request()->routeIs('coach.workspace') ? 'is-active' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        مساحة عمل المدرب
                    </a>
                    <a href="{{ route('coach.clients.index') }}" class="admin-nav-link {{ request()->routeIs('coach.clients.*') || request()->routeIs('clients.progress.*') || request()->routeIs('progress-check-ins.*') ? 'is-active' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                        </svg>
                        العملاء والمتابعة
                    </a>
                    <a href="{{ route('coach-availabilities.index') }}" class="admin-nav-link {{ request()->routeIs('coach-availabilities.*') ? 'is-active' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        التوفر الأسبوعي
                    </a>
                    <a href="{{ route('admin.training-sessions.index') }}" class="admin-nav-link {{ request()->routeIs('admin.training-sessions.*') ? 'is-active' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        جلسات التدريب الخاصة
                    </a>
                    <a href="{{ route('admin.session-bookings.index') }}" class="admin-nav-link {{ request()->routeIs('admin.session-bookings.*') ? 'is-active' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a2 2 0 100-4 2 2 0 000 4zm0 0v4a2 2 0 002 2h6a2 2 0 002-2v-4"></path>
                        </svg>
                        حجوزات الجلسات
                    </a>
                    @endhasanyrole

                    {{-- 4. Workouts --}}
                    <p class="admin-section-label">التمارين</p>

                    @hasanyrole('admin|coach')
                    <a href="{{ route('exercises.index') }}" class="admin-nav-link {{ request()->routeIs('exercises.*') ? 'is-active' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        مكتبة التمارين
                    </a>
                    @endhasanyrole

                    @hasanyrole('admin|coach|user|client')
                    <a href="{{ route('workouts.index') }}" class="admin-nav-link {{ request()->routeIs('workouts.*') ? 'is-active' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        التمارين الرياضية
                    </a>
                    <a href="{{ route('workout-schedules.index') }}" class="admin-nav-link {{ request()->routeIs('workout-schedules.*') ? 'is-active' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a2 2 0 100-4 2 2 0 000 4zm0 0v4a2 2 0 002 2h6a2 2 0 002-2v-4"></path>
                        </svg>
                        الجدول الأسبوعي
                    </a>
                    @endhasanyrole

                    {{-- 5. Nutrition --}}
                    <p class="admin-section-label">التغذية</p>

                    @hasanyrole('admin|coach|user|client')
                    <a href="{{ route('meal-plans.index') }}" class="admin-nav-link {{ request()->routeIs('meal-plans.*') ? 'is-active' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                        </svg>
                        الجداول الغذائية
                    </a>
                    @endhasanyrole

                    @hasanyrole('admin|coach')
                    <a href="{{ route('supplement-plans.index') }}" class="admin-nav-link {{ request()->routeIs('supplement-plans.*') ? 'is-active' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                        </svg>
                        خطط المكملات
                    </a>
                    @endhasanyrole

                    @hasanyrole('user|client')
                    <a href="{{ route('supplement-plans.public') }}" class="admin-nav-link {{ request()->routeIs('supplement-plans.*') ? 'is-active' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                        </svg>
                        خطط المكملات
                    </a>
                    @endhasanyrole

                    @role('admin')
                    <a href="{{ route('nutrition-discounts.index') }}" class="admin-nav-link {{ request()->routeIs('nutrition-discounts.*') ? 'is-active' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        خصومات المراكز الغذائية
                    </a>
                    @endrole

                    {{-- 6. Engagement --}}
                    @hasanyrole('admin|coach|user|client')
                    <p class="admin-section-label">التواصل والمتابعة</p>

                    <a href="{{ route('messages.index') }}" class="admin-nav-link {{ request()->routeIs('messages.*') ? 'is-active' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 5a2 2 0 012-2h10a2 2 0 012 2v5a2 2 0 01-2 2H8l-4 4v-4H4a2 2 0 01-2-2V5z"></path>
                        </svg>
                        الرسائل
                    </a>
                    <a href="{{ route('notifications.index') }}" class="admin-nav-link {{ request()->routeIs('notifications.*') ? 'is-active' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        الإشعارات
                    </a>
                    <a href="{{ route('habits.index') }}" class="admin-nav-link {{ request()->routeIs('habits.*') ? 'is-active' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        متابعة العادات
                    </a>
                    <a href="{{ route('community.index') }}" class="admin-nav-link {{ request()->routeIs('community.*') ? 'is-active' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v10l-4-4H5a2 2 0 01-2-2V10a2 2 0 012-2h2m10 0V6a2 2 0 00-2-2H7a2 2 0 00-2 2v2m12 0H7"></path>
                        </svg>
                        المجتمع
                    </a>
                    @endhasanyrole

                    {{-- 7. Membership & billing --}}
                    @role('admin')
                    <p class="admin-section-label">العضويات والفوترة</p>

                    <a href="{{ route('membership-types.index') }}" class="admin-nav-link {{ request()->routeIs('membership-types.*') ? 'is-active' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"></path>
                        </svg>
                        إدارة العضويات
                    </a>
                    <a href="{{ route('subscription-plans.index') }}" class="admin-nav-link {{ request()->routeIs('subscription-plans.*') && !request()->routeIs('subscription-plans.public') ? 'is-active' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 4a2 2 0 012-2h8a2 2 0 012 2v2H4V4zm0 4h12v8a2 2 0 01-2 2H6a2 2 0 01-2-2V8zm3 2a1 1 0 000 2h6a1 1 0 100-2H7z"></path>
                        </svg>
                        خطط الاشتراك
                    </a>
                    <a href="{{ route('admin.user-memberships.index') }}" class="admin-nav-link {{ request()->routeIs('admin.user-memberships.*') ? 'is-active' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                        </svg>
                        اشتراكات الأعضاء
                    </a>
                    <a href="{{ route('tenant.billing') }}" class="admin-nav-link {{ request()->routeIs('tenant.billing') ? 'is-active' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h10M5 7h14M6 19h12"></path>
                        </svg>
                        الفوترة والاشتراك
                    </a>

                    {{-- 8. Settings (always last) --}}
                    <p class="admin-section-label">الإعدادات</p>

                    <a href="{{ route('admin.permissions.index') }}" class="admin-nav-link {{ request()->routeIs('admin.permissions.*') ? 'is-active' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                        </svg>
                        إدارة الصلاحيات
                    </a>
                    <a href="{{ route('admin.settings.index') }}" class="admin-nav-link {{ request()->routeIs('admin.settings.*') ? 'is-active' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path>
                        </svg>
                        إعدادات الموقع
                    </a>
                    @endrole
                </div>
            </nav>

            {{-- Account / quick links --}}
            <div class="flex-shrink-0 border-t border-tremor-border bg-white px-3 py-3 space-y-0.5">
                <p class="admin-section-label" style="padding-top:0.15rem">خدمات</p>
                <a href="{{ route('home') }}" target="_blank" rel="noopener" class="admin-nav-link">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                    الموقع الرئيسي
                </a>
                @auth
                    <a href="{{ route('profile.show') }}" class="admin-nav-link {{ request()->routeIs('profile.show') ? 'is-active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        الإعدادات
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="admin-nav-link w-full text-rose-600 hover:bg-rose-50 hover:text-rose-700">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            تسجيل الخروج
                        </button>
                    </form>
                @endauth
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex min-h-0 min-w-0 flex-1 flex-col overflow-hidden bg-[#f6f7f9]">
            <!-- Header -->
            <header class="z-30 flex-shrink-0 border-b border-tremor-border bg-white">
                <div class="px-4 py-3.5 sm:px-6 lg:px-7">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex min-w-0 flex-1 items-center gap-2 sm:gap-3">
                            <button
                                type="button"
                                class="admin-icon-btn lg:hidden"
                                @click="openSidebar()"
                                data-admin-sidebar-open
                                :aria-expanded="sidebarOpen.toString()"
                                aria-controls="admin-sidebar"
                                aria-label="فتح القائمة"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            </button>
                            <div class="min-w-0">
                                <h1 class="truncate text-right text-[1.65rem] font-bold tracking-tight text-gray-900 leading-none">@yield('header')</h1>
                                @hasSection('breadcrumbs')
                                    <nav class="mt-1.5 hidden sm:flex" aria-label="Breadcrumb">
                                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                                            <li class="inline-flex items-center">
                                                <a href="{{ route('dashboard') }}" class="text-sm font-medium text-gray-500 hover:text-gray-800">الرئيسية</a>
                                            </li>
                                            @yield('breadcrumbs')
                                        </ol>
                                    </nav>
                                @endif
                            </div>
                        </div>

                        <div class="order-3 hidden w-full justify-center md:order-none md:flex md:w-auto md:flex-1">
                            <div class="admin-search-wrap">
                                <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/></svg>
                                <input type="search" class="admin-search" placeholder="ابحث في اللوحة..." disabled aria-label="بحث" title="قريباً">
                                <span class="admin-search-kbd">⌘ K</span>
                            </div>
                        </div>

                        @hasSection('header_actions')
                            <div class="w-full flex-shrink-0 sm:w-auto">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    @yield('header_actions')
                                </div>
                            </div>
                        @endif

                        @auth
                            @php
                                $headerUnreadNotifications = \App\Models\NotificationFeed::query()
                                    ->where('user_id', auth()->id())
                                    ->whereNull('read_at')
                                    ->latest('created_at')
                                    ->limit(5)
                                    ->get();
                            @endphp
                            <div class="flex flex-shrink-0 items-center gap-1.5">
                                @hasanyrole('admin|coach|user|client')
                                <a href="{{ route('messages.index') }}" class="admin-icon-btn" title="الرسائل">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                </a>
                                @endhasanyrole
                                <a href="{{ route('notifications.index') }}" class="relative admin-icon-btn" title="الإشعارات">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                    @if($headerUnreadNotifications->count() > 0)
                                        <span class="absolute top-1 right-1 inline-flex h-[15px] min-w-[15px] items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] text-white">{{ $headerUnreadNotifications->count() }}</span>
                                    @endif
                                </a>
                                <button type="button" class="admin-icon-btn" onclick="window.location.reload()" title="تحديث">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 4v5h.582M20 20v-5h-.581M5.5 9A7 7 0 0119 9m-.5 6a7 7 0 01-13.5 0"/></svg>
                                </button>

                                <div class="relative ms-1" x-data="{ open: false }" @keydown.escape.window="open = false">
                                    <button type="button" class="rounded-full focus:outline-none focus:ring-2 focus:ring-orange-300" @click="open = !open" :aria-expanded="open.toString()">
                                        <img src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}" class="h-9 w-9 rounded-full object-cover ring-1 ring-gray-200">
                                    </button>
                                    <div x-cloak x-show="open" @click.outside="open = false" x-transition class="absolute left-0 z-50 mt-2 w-56 origin-top-left rounded-xl border border-gray-100 bg-white py-1 shadow-lg">
                                        <div class="border-b border-gray-100 px-4 py-3">
                                            <p class="truncate text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                                            <p class="truncate text-xs text-gray-500">{{ auth()->user()->email }}</p>
                                        </div>
                                        <a href="{{ route('home') }}" target="_blank" rel="noopener" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" @click="open = false">الموقع الرئيسي</a>
                                        <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" @click="open = false">الملف الشخصي</a>
                                        <div class="my-1 border-t border-gray-100"></div>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="block w-full px-4 py-2 text-right text-sm text-rose-600 hover:bg-rose-50">تسجيل الخروج</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endauth
                    </div>

                    @hasSection('subheader')
                        <div class="mt-3 flex flex-wrap items-center justify-between gap-3 border-t border-transparent pt-1">
                            @yield('subheader')
                        </div>
                    @endif
                </div>
            </header>

            <!-- Page Content -->
            <main id="admin-main" class="min-h-0 min-w-0 flex-1 overflow-x-auto overflow-y-auto p-4 sm:p-5 lg:p-6">
                @if(session('success'))
                    <x-admin.alert type="success">{{ session('success') }}</x-admin.alert>
                @endif
                @if(session('error'))
                    <x-admin.alert type="error">{{ session('error') }}</x-admin.alert>
                @endif
                @if(session('warning'))
                    <x-admin.alert type="warning">{{ session('warning') }}</x-admin.alert>
                @endif
                @if(session('info'))
                    <x-admin.alert type="info">{{ session('info') }}</x-admin.alert>
                @endif

                <div class="min-w-0 text-right">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    <script>
        (function () {
            var shell = document.getElementById('admin-shell');
            if (!shell) return;

            function setOpen(open) {
                shell.classList.toggle('admin-sidebar-open', !!open);
            }

            document.querySelectorAll('[data-admin-sidebar-open]').forEach(function (btn) {
                btn.addEventListener('click', function () { setOpen(true); });
            });
            document.querySelectorAll('[data-admin-sidebar-close]').forEach(function (btn) {
                btn.addEventListener('click', function () { setOpen(false); });
            });
            window.addEventListener('resize', function () {
                if (window.innerWidth >= 1024) setOpen(false);
            });
            // Ensure closed on first paint for narrow screens
            if (window.innerWidth < 1024) setOpen(false);
        })();
    </script>
    @stack('scripts')
</body>
</html>