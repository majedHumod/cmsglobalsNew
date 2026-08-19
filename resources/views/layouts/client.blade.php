<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="vapid-public-key" content="{{ env('VAPID_PUBLIC_KEY', '') }}">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="{{ \App\Support\Branding::primaryColor() }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <title>{{ \App\Models\SiteSetting::get('site_name', config('app.name')) }} - @yield('title', 'مساحة المتدرب')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.brand-tokens')

    @php
        $siteName = \App\Models\SiteSetting::get('site_name', config('app.name'));
        $siteLogo = \App\Models\SiteSetting::get('site_logo');
        $unreadNotifications = auth()->check()
            ? \App\Models\NotificationFeed::query()->where('user_id', auth()->id())->whereNull('read_at')->count()
            : 0;
        $unreadMessages = 0;
        if (auth()->check()) {
            try {
                $unreadMessages = app(\App\Services\MessagingService::class)->unreadCountFor(auth()->user());
            } catch (\Throwable $e) {
                $unreadMessages = 0;
            }
        }

        $navActive = function (string|array $pattern): bool {
            return request()->routeIs($pattern);
        };
        $desktopNavClass = function (string|array $pattern) use ($navActive): string {
            return $navActive($pattern)
                ? 'text-brand font-semibold border-b-2 border-brand'
                : 'text-slate-600 hover:text-brand border-b-2 border-transparent';
        };
        $mobileNavClass = function (string|array $pattern) use ($navActive): string {
            return $navActive($pattern)
                ? 'text-brand font-semibold'
                : 'text-slate-500';
        };

        $moreActive = $navActive([
            'client.more',
            'client.pages.*',
            'client.bookings.*',
            'client.nutrition.*',
            'client.community.*',
            'client.challenges.*',
            'client.notifications.*',
        ]);

        $profileUrl = \Illuminate\Support\Facades\Route::has('profile.show')
            ? route('profile.show')
            : url('/user/profile');
    @endphp
    <style>
        /* Mobile / tablet (incl. iPad): app chrome with bottom nav */
        .client-safe-bottom {
            padding-bottom: calc(5rem + env(safe-area-inset-bottom));
        }
        .client-nav-icon {
            width: 1.25rem;
            height: 1.25rem;
            margin: 0 auto 0.2rem;
            display: block;
        }

        /* Desktop website experience from xl (1280px+) — keeps iPad on app chrome */
        @media (min-width: 1280px) {
            .client-safe-bottom {
                padding-bottom: 2rem;
            }
        }
    </style>
    @stack('head')
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-900" data-push-prompt="1" data-client-app="1">
    {{-- PWA banner: phones/tablets only --}}
    <div id="pwa-install-banner" class="hidden xl:hidden fixed top-0 inset-x-0 z-50 bg-brand text-white px-4 py-3 flex items-center justify-between gap-3 shadow-lg">
        <span class="text-sm">ثبّت التطبيق للوصول السريع اليومي</span>
        <div class="flex gap-2 shrink-0">
            <button type="button" id="pwa-install-btn" class="text-xs bg-white text-brand px-3 py-1.5 rounded-lg font-medium">تثبيت</button>
            <button type="button" id="pwa-install-dismiss" class="text-xs text-white/80 px-2">لاحقًا</button>
        </div>
    </div>

    {{-- ========== Desktop site header (xl+) ========== --}}
    <header class="hidden xl:block bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm">
        <div class="max-w-6xl mx-auto px-6">
            <div class="h-16 flex items-center justify-between gap-6">
                <div class="flex items-center gap-8 min-w-0">
                    <a href="{{ route('client.home') }}" class="flex items-center gap-3 shrink-0">
                        @if($siteLogo)
                            <img src="{{ Storage::url($siteLogo) }}" alt="{{ $siteName }}" class="h-9 w-auto object-contain" title="{{ $siteName }}">
                        @else
                            <span class="text-lg font-bold text-brand">{{ $siteName }}</span>
                        @endif
                    </a>
                    <nav class="flex items-center gap-1 text-sm" aria-label="تنقل سطح المكتب">
                        <a href="{{ route('client.home') }}" class="px-3 py-4 {{ $desktopNavClass('client.home') }}">الرئيسية</a>
                        <a href="{{ route('client.habits.index') }}" class="px-3 py-4 {{ $desktopNavClass('client.habits.*') }}">يومي</a>
                        <a href="{{ route('client.progress.index') }}" class="px-3 py-4 {{ $desktopNavClass('client.progress.*') }}">تقدّمي</a>
                        <a href="{{ route('client.bookings.index') }}" class="px-3 py-4 {{ $desktopNavClass('client.bookings.*') }}">الحجوزات</a>
                        <a href="{{ route('client.pages.index') }}" class="px-3 py-4 {{ $desktopNavClass('client.pages.*') }}">الصفحات</a>
                        <a href="{{ route('client.nutrition.index') }}" class="px-3 py-4 {{ $desktopNavClass('client.nutrition.*') }}">التغذية</a>
                        <a href="{{ route('client.community.index') }}" class="px-3 py-4 {{ $desktopNavClass('client.community.*') }}">المجتمع</a>
                        <a href="{{ route('client.more') }}" class="px-3 py-4 {{ $moreActive && !request()->routeIs(['client.bookings.*','client.pages.*','client.nutrition.*','client.community.*']) ? 'text-brand font-semibold border-b-2 border-brand' : 'text-slate-600 hover:text-brand border-b-2 border-transparent' }}">المزيد</a>
                    </nav>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <a href="{{ route('client.messages.index') }}" class="relative inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm {{ $navActive('client.messages.*') ? 'text-brand bg-brand-soft' : 'text-slate-600 hover:bg-slate-100' }}">
                        الرسائل
                        @if($unreadMessages > 0)
                            <span class="min-w-[18px] h-[18px] px-1 rounded-full bg-rose-600 text-white text-[10px] leading-[18px] text-center">{{ $unreadMessages }}</span>
                        @endif
                    </a>
                    <a href="{{ route('client.notifications.index') }}" class="relative inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm {{ $navActive('client.notifications.*') ? 'text-brand bg-brand-soft' : 'text-slate-600 hover:bg-slate-100' }}">
                        الإشعارات
                        @if($unreadNotifications > 0)
                            <span class="min-w-[18px] h-[18px] px-1 rounded-full bg-rose-600 text-white text-[10px] leading-[18px] text-center">{{ $unreadNotifications }}</span>
                        @endif
                    </a>
                    <a href="{{ route('home') }}" class="text-sm text-slate-500 hover:text-brand px-2">الموقع الرئيسي</a>
                    <div class="flex items-center gap-2 border-r border-slate-200 pr-3 mr-1">
                        <a href="{{ $profileUrl }}" class="text-sm font-medium text-slate-800 hover:text-brand truncate max-w-[9rem]">{{ auth()->user()->name ?? '' }}</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-xs text-slate-500 hover:text-rose-600">خروج</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- ========== Mobile / tablet app header (< xl) ========== --}}
    <header class="xl:hidden bg-white border-b border-slate-200 sticky top-0 z-40">
        <div class="max-w-lg mx-auto px-4 py-3 flex items-center justify-between">
            <div>
                <div class="text-xs text-slate-500">مرحبًا</div>
                <div class="font-semibold text-slate-900">{{ auth()->user()->name ?? '' }}</div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('client.messages.index') }}" class="relative p-2 rounded-full bg-slate-100 text-slate-600" aria-label="الرسائل">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    @if($unreadMessages > 0)
                        <span class="absolute -top-0.5 -left-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-rose-600 text-white text-[10px] leading-[18px] text-center">{{ $unreadMessages }}</span>
                    @endif
                </a>
                <a href="{{ route('client.notifications.index') }}" class="relative p-2 rounded-full bg-slate-100 text-slate-600" aria-label="الإشعارات">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    @if($unreadNotifications > 0)
                        <span class="absolute -top-0.5 -left-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-rose-600 text-white text-[10px] leading-[18px] text-center">{{ $unreadNotifications }}</span>
                    @endif
                </a>
            </div>
        </div>
    </header>

    <main class="w-full max-w-lg xl:max-w-6xl mx-auto px-4 xl:px-6 py-4 xl:py-8 client-safe-bottom min-h-screen">
        @if(session('success'))
            <div class="mb-4 rounded-lg bg-emerald-50 text-emerald-800 px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 rounded-lg bg-rose-50 text-rose-800 px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif
        @yield('content')
    </main>

    {{-- ========== Mobile / tablet bottom nav ========== --}}
    <nav class="xl:hidden fixed bottom-0 inset-x-0 bg-white border-t border-slate-200 z-40" style="padding-bottom: env(safe-area-inset-bottom);" aria-label="التنقل الرئيسي">
        <div class="max-w-lg mx-auto grid grid-cols-5 text-center text-[11px]">
            <a href="{{ route('client.home') }}" class="py-2.5 {{ $mobileNavClass('client.home') }}">
                <svg class="client-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3m10-11v10a1 1 0 01-1 1h-3m-4 0h4"/></svg>
                الرئيسية
            </a>
            <a href="{{ route('client.habits.index') }}" class="py-2.5 {{ $mobileNavClass('client.habits.*') }}">
                <svg class="client-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                يومي
            </a>
            <a href="{{ route('client.progress.index') }}" class="py-2.5 {{ $mobileNavClass('client.progress.*') }}">
                <svg class="client-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                تقدّمي
            </a>
            <a href="{{ route('client.messages.index') }}" class="relative py-2.5 {{ $mobileNavClass('client.messages.*') }}">
                <svg class="client-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                رسائل
                @if($unreadMessages > 0)
                    <span class="absolute top-1 left-1/2 translate-x-2 min-w-[14px] h-[14px] px-0.5 rounded-full bg-rose-600 text-white text-[9px] leading-[14px]">{{ min($unreadMessages, 9) }}{{ $unreadMessages > 9 ? '+' : '' }}</span>
                @endif
            </a>
            <a href="{{ route('client.more') }}" class="py-2.5 {{ $moreActive ? 'text-brand font-semibold' : 'text-slate-500' }}">
                <svg class="client-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                المزيد
            </a>
        </div>
    </nav>

    @stack('scripts')
</body>
</html>
