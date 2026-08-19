<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>جلسات التدريب الخاصة - {{ \App\Models\SiteSetting::get('site_name', config('app.name', 'Laravel')) }}</title>

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
        /* Training sessions specific styles */
        .session-card {
            transition: all 0.3s ease;
        }
        
        .session-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="font-sans antialiased pt-16" dir="rtl">
    <div class="min-h-screen bg-gray-50">
        <!-- Navigation -->
        @include('components.landing-page-nav')

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
                                <span class="ml-1 md:ml-2 text-sm font-medium text-gray-500">جلسات التدريب الخاصة</span>
                            </div>
                        </li>
                    </ol>
                </nav>

                <!-- Hero Section -->
                <div class="text-center mb-12">
                    <h1 class="text-4xl font-bold text-gray-900 mb-4">جلسات التدريب الخاصة</h1>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                        احجز جلسة تدريب خاصة مع مدربينا المحترفين واحصل على برنامج تدريبي مخصص يناسب أهدافك
                    </p>
                </div>

                <!-- Training Sessions Grid -->
                @if($trainingSessions->count() == 0)
                    <div class="text-center py-16">
                        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-gray-100 mb-4">
                            <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">لا توجد جلسات تدريب متاحة</h3>
                        <p class="text-sm text-gray-500">لم يتم إضافة أي جلسات تدريب بعد.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        @foreach($trainingSessions as $session)
                            <div class="session-card bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300">
                                @if($session->image)
                                    <div class="h-48 overflow-hidden">
                                        <img src="{{ Storage::url($session->image) }}" alt="{{ $session->title }}" class="w-full h-full object-cover" loading="lazy">
                                    </div>
                                @else
                                    <div class="h-48 bg-gradient-to-br from-indigo-400 to-blue-500 flex items-center justify-center">
                                        <svg class="h-16 w-16 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                    </div>
                                @endif
                                
                                <div class="p-6">
                                    <!-- Session Info -->
                                    <div class="mb-4">
                                        <h3 class="text-xl font-bold text-gray-900 mb-2" style="direction: rtl; text-align: right;">
                                            {{ $session->title }}
                                        </h3>
                                        <p class="text-gray-600 leading-relaxed" style="direction: rtl; text-align: right;">
                                            {{ Str::limit($session->description, 120) }}
                                        </p>
                                    </div>

                                    <!-- Session Details -->
                                    <div class="flex items-center justify-between mb-6">
                                        <div class="flex items-center space-x-4 space-x-reverse">
                                            <div class="flex items-center">
                                                <svg class="w-5 h-5 text-indigo-500 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <span class="text-sm text-gray-600">{{ $session->duration_text }}</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="text-2xl font-bold text-green-600">{{ $session->formatted_price }}</span>
                                        </div>
                                    </div>

                                    <!-- Book Now Button -->
                                    <div class="mt-6">
                                        <a href="{{ route('training-sessions.show', $session) }}" class="block w-full text-center bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-md hover:shadow-lg">
                                            احجز الآن
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if(method_exists($trainingSessions, 'links'))
                        <div class="mt-12">
                            {{ $trainingSessions->links() }}
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