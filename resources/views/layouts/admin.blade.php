<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ \App\Models\SiteSetting::get('site_name', config('app.name', 'Laravel')) }} - @yield('title')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=tajawal:400,500,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Custom Colors -->
    @php
        $primaryColor = \App\Models\SiteSetting::get('primary_color', '#6366f1');
        $secondaryColor = \App\Models\SiteSetting::get('secondary_color', '#10b981');
    @endphp
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
    </style>
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg">
            <div class="flex items-center justify-center h-16 border-b border-gray-200 px-3">
                @php
                    $sidebarLogo = \App\Models\SiteSetting::get('site_logo');
                    $sidebarSiteName = \App\Models\SiteSetting::get('site_name', config('app.name'));
                @endphp
                @if($sidebarLogo)
                    <a href="{{ route('dashboard') }}" class="flex items-center justify-center w-full py-1">
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($sidebarLogo) }}" alt="{{ $sidebarSiteName }}" class="h-9 w-auto max-w-[11rem] object-contain">
                    </a>
                @else
                    <a href="{{ route('dashboard') }}" class="text-lg font-bold text-gray-800 truncate text-center">{{ $sidebarSiteName }}</a>
                @endif
            </div>
            
            <nav class="mt-8">
                <div class="px-4 space-y-2">
                    <!-- Dashboard -->
                    <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 {{ request()->routeIs('dashboard') ? 'bg-indigo-100 text-indigo-700 border-r-4 border-indigo-500' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                        </svg>
                        لوحة التحكم
                    </a>

                    <!-- Notes -->
                    @hasanyrole('admin|user')
                    <a href="{{ route('notes.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 {{ request()->routeIs('notes.*') ? 'bg-indigo-100 text-indigo-700 border-r-4 border-indigo-500' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                        </svg>
                        الملاحظات
                    </a>
                    @endhasanyrole

                    <!-- Pages -->
                    @hasanyrole('admin|page_manager')
                    <a href="{{ route('pages.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 {{ request()->routeIs('pages.*') ? 'bg-indigo-100 text-indigo-700 border-r-4 border-indigo-500' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                        </svg>
                        إدارة الصفحات
                    </a>
                    @endhasanyrole

                    <!-- Articles -->
                    @role('admin')
                    <a href="{{ route('articles.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 {{ request()->routeIs('articles.*') ? 'bg-indigo-100 text-indigo-700 border-r-4 border-indigo-500' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2 5a2 2 0 012-2h8a2 2 0 012 2v10a2 2 0 002 2H4a2 2 0 01-2-2V5zm3 1h6v4H5V6zm6 6H5v2h6v-2z" clip-rule="evenodd"></path>
                        </svg>
                        المقالات
                    </a>
                    @endrole

                    <!-- Meal Plans -->
                    @hasanyrole('admin|user')
                    <a href="{{ route('meal-plans.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 {{ request()->routeIs('meal-plans.*') ? 'bg-indigo-100 text-indigo-700 border-r-4 border-indigo-500' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                        </svg>
                        الجداول الغذائية
                    </a>
                    @endhasanyrole

                    <!-- Workouts -->
                    @hasanyrole('admin|coach|client')
                    <a href="{{ route('workouts.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 {{ request()->routeIs('workouts.*') ? 'bg-indigo-100 text-indigo-700 border-r-4 border-indigo-500' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        التمارين الرياضية
                    </a>
                    @endhasanyrole

                    <!-- Workout Schedules -->
                    @hasanyrole('admin|coach|client')
                    <a href="{{ route('workout-schedules.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 {{ request()->routeIs('workout-schedules.*') ? 'bg-indigo-100 text-indigo-700 border-r-4 border-indigo-500' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a2 2 0 100-4 2 2 0 000 4zm0 0v4a2 2 0 002 2h6a2 2 0 002-2v-4"></path>
                        </svg>
                        الجدول الأسبوعي
                    </a>
                    @endhasanyrole

                    <!-- Membership Types -->
                    @role('admin')
                    <a href="{{ route('membership-types.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 {{ request()->routeIs('membership-types.*') ? 'bg-indigo-100 text-indigo-700 border-r-4 border-indigo-500' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"></path>
                        </svg>
                        إدارة العضويات
                    </a>

                    <a href="{{ route('admin.user-memberships.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 {{ request()->routeIs('admin.user-memberships.*') ? 'bg-indigo-100 text-indigo-700 border-r-4 border-indigo-500' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                        </svg>
                        اشتراكات الأعضاء
                    </a>
                    @endrole

                    <!-- FAQs -->
                    @role('admin')
                    <a href="{{ route('admin.faqs.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 {{ request()->routeIs('admin.faqs.*') ? 'bg-indigo-100 text-indigo-700 border-r-4 border-indigo-500' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path>
                        </svg>
                        الأسئلة الشائعة
                    </a>
                    
                    <!-- Testimonials -->
                    <a href="{{ route('admin.testimonials.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 {{ request()->routeIs('admin.testimonials.*') ? 'bg-indigo-100 text-indigo-700 border-r-4 border-indigo-500' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z"></path>
                            <path d="M15 7v2a4 4 0 01-4 4H9.828l-1.766 1.767c.28.149.599.233.938.233h2l3 3v-3h2a2 2 0 002-2V9a2 2 0 00-2-2h-1z"></path>
                        </svg>
                        قصص النجاح
                    </a>
                    
                    <!-- Training Sessions -->
                    <a href="{{ route('admin.training-sessions.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 {{ request()->routeIs('admin.training-sessions.*') ? 'bg-indigo-100 text-indigo-700 border-r-4 border-indigo-500' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        جلسات التدريب الخاصة
                    </a>
                    
                    <!-- Session Bookings -->
                    <a href="{{ route('admin.session-bookings.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 {{ request()->routeIs('admin.session-bookings.*') ? 'bg-indigo-100 text-indigo-700 border-r-4 border-indigo-500' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a2 2 0 100-4 2 2 0 000 4zm0 0v4a2 2 0 002 2h6a2 2 0 002-2v-4"></path>
                        </svg>
                        حجوزات الجلسات
                    </a>
                    
                    <!-- Nutrition Discounts -->
                    <a href="{{ route('nutrition-discounts.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 {{ request()->routeIs('nutrition-discounts.*') ? 'bg-indigo-100 text-indigo-700 border-r-4 border-indigo-500' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        خصومات المراكز الغذائية
                    </a>
                    @endrole

                    <!-- Landing Pages -->
                    @role('admin')
                    <a href="{{ route('admin.landing-pages.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 {{ request()->routeIs('admin.landing-pages.*') ? 'bg-indigo-100 text-indigo-700 border-r-4 border-indigo-500' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"></path>
                        </svg>
                        الصفحة الرئيسية
                    </a>
                    @endrole

                    <!-- Permissions -->
                    @role('admin')
                    <a href="{{ route('admin.permissions.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 {{ request()->routeIs('admin.permissions.*') ? 'bg-indigo-100 text-indigo-700 border-r-4 border-indigo-500' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                        </svg>
                        إدارة الصلاحيات
                    </a>
                    @endrole

                    <!-- Settings -->
                    @role('admin')
                    <a href="{{ route('admin.settings.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 {{ request()->routeIs('admin.settings.*') ? 'bg-indigo-100 text-indigo-700 border-r-4 border-indigo-500' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path>
                        </svg>
                        إعدادات الموقع
                    </a>
                    @endrole

                    <!-- Billing -->
                    @role('admin')
                    <a href="{{ route('tenant.billing') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 {{ request()->routeIs('tenant.billing') ? 'bg-indigo-100 text-indigo-700 border-r-4 border-indigo-500' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h10M5 7h14M6 19h12"></path>
                        </svg>
                        الفوترة والاشتراك
                    </a>
                    @endrole
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between gap-4 flex-wrap">
                        <div class="flex items-center gap-4 min-w-0 flex-1">
                            @php
                                $headerLogo = \App\Models\SiteSetting::get('site_logo');
                                $headerSiteName = \App\Models\SiteSetting::get('site_name', config('app.name'));
                            @endphp
                            @if($headerLogo)
                                <a href="{{ route('dashboard') }}" class="flex-shrink-0" title="{{ $headerSiteName }}">
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($headerLogo) }}" alt="{{ $headerSiteName }}" class="h-10 w-auto max-h-10 max-w-[140px] object-contain">
                                </a>
                            @endif
                            <div class="min-w-0">
                            <h1 class="text-2xl font-bold text-gray-900 text-right truncate">@yield('header')</h1>
                            @hasSection('breadcrumbs')
                                <nav class="flex mt-2" aria-label="Breadcrumb">
                                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                                        <li class="inline-flex items-center">
                                            <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-indigo-600 text-sm font-medium">
                                                الرئيسية
                                            </a>
                                        </li>
                                        @yield('breadcrumbs')
                                    </ol>
                                </nav>
                            @endif
                            </div>
                        </div>
                        @hasSection('header_actions')
                            <div class="flex-shrink-0">
                                @yield('header_actions')
                            </div>
                        @endif
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-6">
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 ml-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 ml-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                <div class="text-right">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
</body>
</html>