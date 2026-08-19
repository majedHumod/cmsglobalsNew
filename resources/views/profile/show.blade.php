<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>الملف الشخصي - {{ \App\Models\SiteSetting::get('site_name', config('app.name', 'Laravel')) }}</title>

    <!-- Favicon -->
    @php
        $siteFavicon = \App\Models\SiteSetting::get('site_favicon');
    @endphp
    @if($siteFavicon)
        <link rel="icon" href="{{ Storage::url($siteFavicon) }}" type="image/x-icon">
    @endif

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.brand-tokens')
    
    <!-- Styles for rich content -->
    <style>
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
</head>
<body class="font-sans antialiased pt-16" dir="rtl">
    <div class="min-h-screen bg-gray-100">
        <!-- Navigation -->
        <header class="bg-white shadow-sm fixed top-0 left-0 right-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16" dir="rtl">
                    <!-- Logo and Site Name -->
                    <div class="flex items-center order-1">
                        @php
                            $siteLogo = \App\Models\SiteSetting::get('site_logo');
                            $siteName = \App\Models\SiteSetting::get('site_name', config('app.name', 'Laravel'));
                        @endphp
                        @if($siteLogo)
                            <a href="{{ route('home') }}" class="flex-shrink-0 flex items-center">
                                <img class="h-8 w-auto" src="{{ Storage::url($siteLogo) }}" alt="{{ $siteName }}" title="{{ $siteName }}">
                            </a>
                        @else
                            <a href="{{ route('home') }}" class="flex-shrink-0 flex items-center">
                                <span class="text-xl font-bold text-indigo-600">{{ $siteName }}</span>
                            </a>
                        @endif
                    </div>

                    <!-- Navigation Links -->
                    <div class="hidden md:flex md:items-center md:space-x-4 order-2">
                        <a href="{{ route('home') }}" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">الرئيسية</a>
                        <a href="{{ route('faqs.index') }}" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">الأسئلة الشائعة</a>

                        @php
                            try {
                                $allMenuPages = \App\Models\Page::where('show_in_menu', true)
                                    ->where('is_published', true)
                                    ->orderBy('menu_order')
                                    ->get();

                                $user = auth()->user();
                                $menuPages = $allMenuPages->filter(fn ($page) => $page->canAccess($user));
                            } catch (\Exception $e) {
                                $menuPages = collect([]);
                            }
                        @endphp

                        @foreach($menuPages as $menuPage)
                            <a href="{{ route('pages.show', $menuPage->slug) }}" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">
                                {{ $menuPage->title }}
                            </a>
                        @endforeach

                        @auth
                            <div class="ms-3 relative" x-data="{ open: false }">
                                <x-dropdown align="right" width="48">
                                    <x-slot name="trigger">
                                        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                            <button class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                                <img class="size-8 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                                            </button>
                                        @else
                                            <span class="inline-flex rounded-md">
                                                <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                                                    {{ Auth::user()->name }}
                                                    <svg class="ms-2 -me-0.5 size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
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
                                            <button type="submit" class="block w-full text-right px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">تسجيل الخروج</button>
                                        </form>
                                    </x-slot>
                                </x-dropdown>
                            </div>
                        @endauth

                        @guest
                            <a href="{{ route('login') }}" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">تسجيل الدخول</a>
                            <a href="{{ route('register') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded-md text-sm font-medium">إنشاء حساب</a>
                        @endguest
                    </div>

                    <!-- Mobile menu button -->
                    <div class="flex items-center md:hidden order-3">
                        <button type="button" id="mobile-menu-button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500" aria-expanded="false">
                            <span class="sr-only">فتح القائمة الرئيسية</span>
                            <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            <div class="hidden md:hidden" id="mobile-menu">
                <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                    <a href="{{ route('home') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">الرئيسية</a>
                    <a href="{{ route('faqs.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">الأسئلة الشائعة</a>

                    @foreach($menuPages as $menuPage)
                        <a href="{{ route('pages.show', $menuPage->slug) }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">
                            {{ $menuPage->title }}
                        </a>
                    @endforeach

                    @auth
                        <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">لوحة التحكم</a>
                        <a href="{{ route('profile.show') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">الملف الشخصي</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-right px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">تسجيل الخروج</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">تسجيل الدخول</a>
                        <a href="{{ route('register') }}" class="block px-3 py-2 rounded-md text-base font-medium text-indigo-600 hover:text-indigo-800 hover:bg-gray-50">إنشاء حساب</a>
                    @endauth
                </div>
            </div>

            <script>
                // Mobile menu toggle
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
        </header>

        <!-- Page Content -->
        <main class="pt-6 pb-12" dir="rtl">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Breadcrumb Navigation -->
                <nav class="flex mb-4" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3 space-x-reverse">
                        <li class="inline-flex items-center">
                            <a href="{{ route('home') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-indigo-600">
                                <svg class="w-4 h-4 ml-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                                </svg>
                                الرئيسية
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-1 md:ml-2 text-sm font-medium text-gray-500">الملف الشخصي</span>
                            </div>
                        </li>
                    </ol>
                </nav>

                <!-- Profile Content Container with FAQ-style design -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                    <article>
                        <header class="mb-8">
                            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">الملف الشخصي</h1>
                            
                            <div class="flex items-center text-sm text-gray-500 mt-4">
                                <span>إدارة معلوماتك الشخصية وإعدادات الحساب</span>
                            </div>
                        </header>

                        <div class="prose prose-lg max-w-none text-right" dir="rtl">
                            @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                                @livewire('profile.update-profile-information-form')

                                <div class="hidden sm:block">
                                    <div class="py-8">
                                        <div class="border-t border-gray-200"></div>
                                    </div>
                                </div>
                            @endif

                            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                                <div class="mt-10 sm:mt-0">
                                    @livewire('profile.update-password-form')
                                </div>

                                <div class="hidden sm:block">
                                    <div class="py-8">
                                        <div class="border-t border-gray-200"></div>
                                    </div>
                                </div>
                            @endif

                            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                                <div class="mt-10 sm:mt-0">
                                    @livewire('profile.two-factor-authentication-form')
                                </div>

                                <div class="hidden sm:block">
                                    <div class="py-8">
                                        <div class="border-t border-gray-200"></div>
                                    </div>
                                </div>
                            @endif

                            <div class="mt-10 sm:mt-0">
                                @livewire('profile.logout-other-browser-sessions-form')
                            </div>

                            @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
                                <div class="hidden sm:block">
                                    <div class="py-8">
                                        <div class="border-t border-gray-200"></div>
                                    </div>
                                </div>

                                <div class="mt-10 sm:mt-0">
                                    @livewire('profile.delete-user-form')
                                </div>
                            @endif
                        </div>
                    </article>
                </div>
            </div>
        </main>
        
        <!-- Footer -->
        @include('layouts.footer')
    </div>
</body>
</html>