<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $trainingSession->title }} - {{ \App\Models\SiteSetting::get('site_name', config('app.name', 'Laravel')) }}</title>

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
</head>
<body class="font-sans antialiased pt-16" dir="rtl">
    <div class="min-h-screen bg-gray-100">
        <!-- Navigation -->
        @include('components.landing-page-nav')

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
                                <a href="{{ route('training-sessions.all') }}" class="ml-1 md:ml-2 text-sm font-medium text-gray-500 hover:text-indigo-600">جلسات التدريب</a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-1 md:ml-2 text-sm font-medium text-gray-500">{{ $trainingSession->title }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                    <article>
                    @if($trainingSession->image)
                        <div class="w-full h-64 md:h-80 mb-8 rounded-lg overflow-hidden">
                            <img src="{{ Storage::url($trainingSession->image) }}" alt="{{ $trainingSession->title }}" class="w-full h-full object-cover">
                        </div>
                    @endif

                        <header class="mb-8">
                            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">{{ $trainingSession->title }}</h1>
                            
                            <!-- Session Info -->
                            <div class="flex items-center space-x-6 space-x-reverse mb-6">
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-indigo-500 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-lg text-gray-700">{{ $trainingSession->duration_text }}</span>
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-green-500 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                    <span class="text-2xl font-bold text-green-600">{{ $trainingSession->formatted_price }}</span>
                                </div>
                            </div>
                            @if($trainingSession->video_meeting_url)
                                <div class="mb-6">
                                    <a href="{{ $trainingSession->video_meeting_url }}" target="_blank" class="inline-flex items-center px-4 py-2 rounded-md bg-indigo-50 text-indigo-700 hover:bg-indigo-100">
                                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14m-6 3h4a2 2 0 002-2V9a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                                        </svg>
                                        رابط الجلسة المرئية
                                    </a>
                                </div>
                            @endif
                        </header>

                        <div class="prose prose-lg max-w-none text-right mb-8" dir="rtl">
                            <p class="text-xl text-gray-600 leading-relaxed">{{ $trainingSession->description }}</p>
                        </div>

                        @auth
                            <!-- Booking Form -->
                            <div class="bg-gray-50 rounded-xl p-6 mb-8">
                                <h2 class="text-2xl font-bold text-gray-900 mb-6">احجز جلسة التدريب</h2>
                                
                                <form action="{{ route('training-sessions.book', $trainingSession) }}" method="POST" class="space-y-6">
                                    @csrf
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- تاريخ الحجز -->
                                        <div>
                                            <label for="booking_date" class="block text-sm font-medium text-gray-700">تاريخ الجلسة *</label>
                                            <input type="date" name="booking_date" id="booking_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('booking_date') }}" required min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                            @error('booking_date')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <!-- وقت الحجز -->
                                        <div>
                                            <label for="booking_time" class="block text-sm font-medium text-gray-700">وقت الجلسة *</label>
                                            <select name="booking_time" id="booking_time" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                                <option value="">اختر الوقت</option>
                                                <option value="08:00" {{ old('booking_time') == '08:00' ? 'selected' : '' }}>8:00 صباحاً</option>
                                                <option value="10:00" {{ old('booking_time') == '10:00' ? 'selected' : '' }}>10:00 صباحاً</option>
                                                <option value="12:00" {{ old('booking_time') == '12:00' ? 'selected' : '' }}>12:00 ظهراً</option>
                                                <option value="14:00" {{ old('booking_time') == '14:00' ? 'selected' : '' }}>2:00 مساءً</option>
                                                <option value="16:00" {{ old('booking_time') == '16:00' ? 'selected' : '' }}>4:00 مساءً</option>
                                                <option value="18:00" {{ old('booking_time') == '18:00' ? 'selected' : '' }}>6:00 مساءً</option>
                                                <option value="20:00" {{ old('booking_time') == '20:00' ? 'selected' : '' }}>8:00 مساءً</option>
                                            </select>
                                            @error('booking_time')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- ملاحظات -->
                                    <div>
                                        <label for="notes" class="block text-sm font-medium text-gray-700">ملاحظات إضافية</label>
                                        <textarea name="notes" id="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="أي ملاحظات أو طلبات خاصة">{{ old('notes') }}</textarea>
                                        @error('notes')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- ملخص الحجز -->
                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                        <h3 class="text-lg font-medium text-gray-900 mb-4">ملخص الحجز</h3>
                                        <div class="space-y-2">
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">الجلسة:</span>
                                                <span class="font-medium">{{ $trainingSession->title }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">المدة:</span>
                                                <span class="font-medium">{{ $trainingSession->duration_text }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">السعر:</span>
                                                <span class="font-medium text-green-600">{{ $trainingSession->formatted_price }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex justify-end">
                                        <button type="submit" class="inline-flex items-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transform hover:scale-105 transition-all duration-300">
                                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a2 2 0 100-4 2 2 0 000 4zm0 0v4a2 2 0 002 2h6a2 2 0 002-2v-4"></path>
                                            </svg>
                                            تأكيد الحجز
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @else
                            <!-- Login Required -->
                            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 text-center">
                                <svg class="mx-auto h-12 w-12 text-yellow-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">تسجيل الدخول مطلوب</h3>
                                <p class="text-gray-600 mb-6">يجب تسجيل الدخول أولاً لحجز جلسة التدريب.</p>
                                <div class="flex justify-center space-x-4 space-x-reverse">
                                    <a href="{{ route('login') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                        تسجيل الدخول
                                    </a>
                                    <a href="{{ route('register') }}" class="inline-flex items-center px-6 py-3 border border-indigo-600 text-base font-medium rounded-md text-indigo-600 bg-white hover:bg-indigo-50">
                                        إنشاء حساب
                                    </a>
                                </div>
                            </div>
                        @endauth
                    </article>
                </div>
            </div>
        </main>
        
        <!-- Footer -->
        @include('layouts.footer')
    </div>
</body>
</html>