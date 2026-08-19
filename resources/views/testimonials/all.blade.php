<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>قصص النجاح - {{ \App\Models\SiteSetting::get('site_name', config('app.name', 'Laravel')) }}</title>

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
    
    <style>
        /* Testimonials specific styles */
        .testimonial-card {
            transition: all 0.3s ease;
        }
        
        .testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="font-sans antialiased pt-16" dir="rtl">
    <div class="min-h-screen bg-gray-50">
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
                        <a href="{{ route('testimonials.all') }}" class="text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">قصص النجاح</a>
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
                    <a href="{{ route('testimonials.all') }}" class="block px-3 py-2 rounded-md text-base font-medium text-indigo-600 hover:text-indigo-800 hover:bg-gray-50">قصص النجاح</a>
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
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
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
                                <span class="ml-1 md:ml-2 text-sm font-medium text-gray-500">قصص النجاح</span>
                            </div>
                        </li>
                    </ol>
                </nav>

                <!-- Hero Section -->
                <div class="text-center mb-12">
                    <h1 class="text-4xl font-bold text-gray-900 mb-4">قصص نجاح عملائنا</h1>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                        اكتشف كيف غيرت خدماتنا حياة عملائنا وساعدتهم في تحقيق أهدافهم وأحلامهم
                    </p>
                </div>

                <!-- Testimonials Grid -->
                @if($testimonials->count() == 0)
                    <div class="text-center py-16">
                        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-gray-100 mb-4">
                            <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-1l-4 4z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">لا توجد قصص نجاح</h3>
                        <p class="text-sm text-gray-500">لم يتم إضافة أي قصص نجاح بعد.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        @foreach($testimonials as $testimonial)
                            <div class="testimonial-card bg-white rounded-2xl shadow-lg border border-gray-100 p-8 hover:shadow-xl transition-all duration-300">
                                <!-- Quote Icon -->
                                <div class="flex justify-end mb-6">
                                    <svg class="w-10 h-10 text-indigo-200" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h4v10h-10z"/>
                                    </svg>
                                </div>

                                <!-- Story Content -->
                                <div class="mb-8">
                                    <p class="text-gray-700 text-lg leading-relaxed" style="direction: rtl; text-align: right;">
                                        "{{ $testimonial->story_content }}"
                                    </p>
                                </div>

                                <!-- Author Info -->
                                <div class="flex items-center">
                                    @if($testimonial->image)
                                        <div class="flex-shrink-0 mr-4">
                                            <img src="{{ Storage::url($testimonial->image) }}" alt="{{ $testimonial->name }}" class="w-16 h-16 rounded-full object-cover border-4 border-indigo-100" loading="lazy">
                                        </div>
                                    @else
                                        <div class="flex-shrink-0 mr-4">
                                            <div class="w-16 h-16 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-full flex items-center justify-center border-4 border-indigo-100">
                                                <span class="text-white font-bold text-xl">{{ substr($testimonial->name, 0, 1) }}</span>
                                            </div>
                                        </div>
                                    @endif
                                    <div>
                                        <h4 class="text-xl font-bold text-gray-900" style="direction: rtl; text-align: right;">{{ $testimonial->name }}</h4>
                                        <p class="text-sm text-gray-500 mt-1">عميل راضٍ</p>
                                        <div class="flex items-center mt-2">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                </svg>
                                            @endfor
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if(method_exists($testimonials, 'links'))
                        <div class="mt-12">
                            {{ $testimonials->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </main>
        
        <!-- Footer -->
        @include('layouts.footer')
    </div>
</body>
</html>