<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $siteName = \App\Models\SiteSetting::get('site_name', config('app.name', 'Laravel'));
        $pageTitle = trim($__env->yieldContent('title'));
        $metaDescription = trim($__env->yieldContent('meta_description'));
    @endphp

    <title>{{ $pageTitle ? $pageTitle . ' - ' . $siteName : $siteName }}</title>

    @if($metaDescription)
        <meta name="description" content="{{ $metaDescription }}">
    @endif

    @php
        $siteFavicon = \App\Models\SiteSetting::get('site_favicon');
        $primaryColor = \App\Models\SiteSetting::get('primary_color', '#6366f1');
        $secondaryColor = \App\Models\SiteSetting::get('secondary_color', '#10b981');
    @endphp

    @if($siteFavicon)
        <link rel="icon" href="{{ Storage::url($siteFavicon) }}" type="image/x-icon">
    @endif

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=tajawal:400,500,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --primary-color: {{ $primaryColor }};
            --secondary-color: {{ $secondaryColor }};
        }

        .bg-primary { background-color: var(--primary-color); }
        .text-primary { color: var(--primary-color); }
        .border-primary { border-color: var(--primary-color); }
        .bg-secondary { background-color: var(--secondary-color); }
        .text-secondary { color: var(--secondary-color); }
        .border-secondary { border-color: var(--secondary-color); }

        .prose {
            max-width: none;
        }

        .prose img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 1rem 0;
        }

        .prose table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
        }

        .prose table th,
        .prose table td {
            border: 1px solid #e5e7eb;
            padding: 0.5rem;
            text-align: right;
        }

        .prose table th {
            background-color: #f9fafb;
            font-weight: 600;
        }

        .prose blockquote {
            border-right: 4px solid #6366f1;
            padding-right: 1rem;
            margin: 1rem 0;
            font-style: italic;
            background-color: #f8fafc;
            padding: 1rem;
            border-radius: 4px;
        }

        .prose ul, .prose ol {
            padding-right: 1.5rem;
        }

        .prose h1, .prose h2, .prose h3, .prose h4, .prose h5, .prose h6 {
            margin-top: 2rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .prose h1 { font-size: 2.25rem; }
        .prose h2 { font-size: 1.875rem; }
        .prose h3 { font-size: 1.5rem; }
        .prose h4 { font-size: 1.25rem; }
        .prose h5 { font-size: 1.125rem; }
        .prose h6 { font-size: 1rem; }

        .prose p {
            margin-bottom: 1rem;
            line-height: 1.75;
            text-align: right;
        }

        .prose a {
            color: #6366f1;
            text-decoration: underline;
        }

        .prose a:hover {
            color: #4f46e5;
        }

        .prose code {
            background-color: #f1f5f9;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
        }

        .prose pre {
            background-color: #1e293b;
            color: #f1f5f9;
            padding: 1rem;
            border-radius: 8px;
            overflow-x: auto;
            margin: 1rem 0;
        }

        .prose pre code {
            background-color: transparent;
            padding: 0;
            color: inherit;
        }
    </style>

    @stack('styles')
</head>
<body class="font-sans antialiased pt-16" dir="rtl">
    <div class="min-h-screen bg-gray-100">
        <header class="fixed left-0 right-0 top-0 z-50 bg-white shadow-sm">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between" dir="rtl">
                    <div class="order-1 flex items-center">
                        @php
                            $siteLogo = \App\Models\SiteSetting::get('site_logo');
                        @endphp
                        @if($siteLogo)
                            <a href="{{ route('home') }}" class="flex flex-shrink-0 items-center">
                                <img class="h-8 w-auto" src="{{ Storage::url($siteLogo) }}" alt="{{ $siteName }}">
                            </a>
                        @else
                            <a href="{{ route('home') }}" class="flex flex-shrink-0 items-center">
                                <span class="text-xl font-bold text-indigo-600">{{ $siteName }}</span>
                            </a>
                        @endif
                    </div>

                    <div class="order-2 hidden md:flex md:items-center md:space-x-4">
                        <a href="{{ route('home') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-600 hover:text-indigo-600">الرئيسية</a>
                        <a href="{{ route('faqs.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-600 hover:text-indigo-600">الأسئلة الشائعة</a>

                        @php
                            try {
                                $allMenuPages = \App\Models\Page::where('show_in_menu', true)
                                    ->where('is_published', true)
                                    ->orderBy('menu_order')
                                    ->get();

                                $user = auth()->user();
                                $menuPages = $allMenuPages->filter(function ($page) use ($user) {
                                    if ($page->access_level === 'public') return true;
                                    if (! $user) return false;
                                    if ($page->access_level === 'authenticated') return true;
                                    if ($page->access_level === 'user' && $user->hasRole('user')) return true;
                                    if ($page->access_level === 'page_manager' && $user->hasRole('page_manager')) return true;
                                    if ($page->access_level === 'admin' && $user->hasRole('admin')) return true;
                                    if ($page->access_level === 'membership' && $user->membership_type_id) {
                                        $requiredTypes = $page->required_membership_types;
                                        if (is_string($requiredTypes)) {
                                            $requiredTypes = json_decode($requiredTypes, true) ?: [];
                                        }
                                        return in_array($user->membership_type_id, $requiredTypes);
                                    }
                                    return false;
                                });
                            } catch (\Exception $e) {
                                $menuPages = collect([]);
                            }
                        @endphp

                        @foreach($menuPages as $menuPage)
                            <a href="{{ route('pages.show', $menuPage->slug) }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-600 hover:text-indigo-600">
                                {{ $menuPage->title }}
                            </a>
                        @endforeach

                        @auth
                            <div class="relative ms-3" x-data="{ open: false }">
                                <x-dropdown align="right" width="48">
                                    <x-slot name="trigger">
                                        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                            <button class="flex rounded-full border-2 border-transparent text-sm transition focus:border-gray-300 focus:outline-none">
                                                <img class="size-8 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                                            </button>
                                        @else
                                            <span class="inline-flex rounded-md">
                                                <button type="button" class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:bg-gray-50 focus:outline-none active:bg-gray-50">
                                                    {{ Auth::user()->name }}
                                                    <svg class="-me-0.5 ms-2 size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                                </button>
                                            </span>
                                        @endif
                                    </x-slot>
                                    <x-slot name="content">
                                        <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">لوحة التحكم</a>
                                        <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">الملف الشخصي</a>
                                        @role('admin')
                                            <a href="{{ route('admin.settings.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">الإعدادات</a>
                                        @endrole
                                        <div class="border-t border-gray-100"></div>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="block w-full px-4 py-2 text-right text-sm text-gray-700 hover:bg-gray-100">تسجيل الخروج</button>
                                        </form>
                                    </x-slot>
                                </x-dropdown>
                            </div>
                        @endauth

                        @guest
                            <a href="{{ route('login') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-600 hover:text-indigo-600">تسجيل الدخول</a>
                            <a href="{{ route('register') }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">إنشاء حساب</a>
                        @endguest
                    </div>

                    <div class="order-3 flex items-center md:hidden">
                        <button type="button" id="mobile-menu-button" class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500" aria-expanded="false">
                            <span class="sr-only">فتح القائمة الرئيسية</span>
                            <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="hidden md:hidden" id="mobile-menu">
                <div class="space-y-1 px-2 pb-3 pt-2 sm:px-3">
                    <a href="{{ route('home') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900">الرئيسية</a>
                    <a href="{{ route('faqs.index') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900">الأسئلة الشائعة</a>

                    @foreach($menuPages as $menuPage)
                        <a href="{{ route('pages.show', $menuPage->slug) }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900">
                            {{ $menuPage->title }}
                        </a>
                    @endforeach

                    @auth
                        <a href="{{ route('dashboard') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900">لوحة التحكم</a>
                        <a href="{{ route('profile.show') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900">الملف الشخصي</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full rounded-md px-3 py-2 text-right text-base font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900">تسجيل الخروج</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900">تسجيل الدخول</a>
                        <a href="{{ route('register') }}" class="block rounded-md px-3 py-2 text-base font-medium text-indigo-600 hover:bg-gray-50 hover:text-indigo-800">إنشاء حساب</a>
                    @endauth
                </div>
            </div>
        </header>

        <main class="pb-12 pt-6" dir="rtl">
            @yield('content')
        </main>

        @include('layouts.footer')
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');

            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
