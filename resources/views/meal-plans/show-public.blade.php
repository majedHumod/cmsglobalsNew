<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $mealPlan->name }} - {{ \App\Models\SiteSetting::get('site_name', config('app.name', 'Laravel')) }}</title>

    <!-- Favicon -->
    @php
        $siteFavicon = \App\Models\SiteSetting::get('site_favicon');
    @endphp
    @if($siteFavicon)
        <link rel="icon" href="{{ Storage::url($siteFavicon) }}" type="image/x-icon">
    @endif

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
        
        .bg-primary {
            background-color: var(--primary-color);
        }
        
        .text-primary {
            color: var(--primary-color);
        }
        
        .border-primary {
            border-color: var(--primary-color);
        }
        
        .bg-secondary {
            background-color: var(--secondary-color);
        }
        
        .text-secondary {
            color: var(--secondary-color);
        }
        
        .border-secondary {
            border-color: var(--secondary-color);
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
                                <img class="h-8 w-auto" src="{{ Storage::url($siteLogo) }}" alt="{{ $siteName }}">
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
                        <a href="{{ route('meal-plans.public') }}" class="text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">الجداول الغذائية</a>
                        <a href="{{ route('faqs.index') }}" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">الأسئلة الشائعة</a>

                        @php
                            try {
                                $allMenuPages = \App\Models\Page::where('show_in_menu', true)
                                    ->where('is_published', true)
                                    ->orderBy('menu_order')
                                    ->get();

                                $user = auth()->user();
                                $menuPages = $allMenuPages->filter(function($page) use ($user) {
                                    if ($page->access_level === 'public') return true;
                                    if (!$user) return false;
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
                    <a href="{{ route('meal-plans.public') }}" class="block px-3 py-2 rounded-md text-base font-medium text-indigo-600 hover:text-indigo-800 hover:bg-gray-50">الجداول الغذائية</a>
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
                                <a href="{{ route('meal-plans.public') }}" class="ml-1 md:ml-2 text-sm font-medium text-gray-500 hover:text-indigo-600">الجداول الغذائية</a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-1 md:ml-2 text-sm font-medium text-gray-500">{{ $mealPlan->name }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>

                <!-- Page Content Container -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                    <article>
                        <!-- Meal Plan Image -->
                        @if($mealPlan->image)
                            <div class="w-full h-64 md:h-80 mb-8 rounded-lg overflow-hidden">
                                <img src="{{ Storage::url($mealPlan->image) }}" alt="{{ $mealPlan->name }}" class="w-full h-full object-cover" loading="lazy" decoding="async">
                            </div>
                        @endif

                        <header class="mb-8">
                            <div class="flex justify-between items-start mb-4">
                                <h1 class="text-3xl md:text-4xl font-bold text-gray-900">{{ $mealPlan->name }}</h1>
                                @auth
                                    @if(auth()->user()->hasRole('admin') || $mealPlan->user_id === auth()->id())
                                        <a href="{{ route('meal-plans.edit', $mealPlan) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                            تعديل الوجبة
                                        </a>
                                    @endif
                                @endauth
                            </div>
                            
                            <!-- Meal Type and Status Badges -->
                            <div class="flex items-center space-x-4 space-x-reverse mb-6">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    {{ $mealPlan->meal_type_name }}
                                </span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                    {{ $mealPlan->difficulty_name }}
                                </span>
                                @if($mealPlan->is_active)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        متاح
                                    </span>
                                @endif
                            </div>
                            
                            @if($mealPlan->description)
                                <p class="text-xl text-gray-600 leading-relaxed mb-6">{{ $mealPlan->description }}</p>
                            @endif

                            <!-- Meal Plan Details -->
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
                                @if($mealPlan->calories)
                                    <div class="bg-green-50 p-4 rounded-lg text-center">
                                        <div class="text-2xl font-bold text-green-600">{{ $mealPlan->calories }}</div>
                                        <div class="text-sm text-gray-600">سعرة حرارية</div>
                                    </div>
                                @endif
                                
                                @if($mealPlan->total_time > 0)
                                    <div class="bg-blue-50 p-4 rounded-lg text-center">
                                        <div class="text-2xl font-bold text-blue-600">{{ $mealPlan->total_time }}</div>
                                        <div class="text-sm text-gray-600">دقيقة</div>
                                    </div>
                                @endif
                                
                                <div class="bg-purple-50 p-4 rounded-lg text-center">
                                    <div class="text-2xl font-bold text-purple-600">{{ $mealPlan->servings }}</div>
                                    <div class="text-sm text-gray-600">حصة</div>
                                </div>
                                
                                <div class="bg-yellow-50 p-4 rounded-lg text-center">
                                    <div class="text-2xl font-bold text-yellow-600">{{ $mealPlan->difficulty_name }}</div>
                                    <div class="text-sm text-gray-600">مستوى الصعوبة</div>
                                </div>
                            </div>

                            <!-- Nutrition Information -->
                            @if($mealPlan->protein || $mealPlan->carbs || $mealPlan->fats)
                                <div class="mb-8">
                                    <h2 class="text-2xl font-bold text-gray-900 mb-4">المعلومات الغذائية</h2>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        @if($mealPlan->protein)
                                            <div class="bg-red-50 p-4 rounded-lg text-center">
                                                <div class="text-2xl font-bold text-red-600">{{ $mealPlan->protein }}ج</div>
                                                <div class="text-sm text-gray-600">بروتين</div>
                                            </div>
                                        @endif
                                        
                                        @if($mealPlan->carbs)
                                            <div class="bg-orange-50 p-4 rounded-lg text-center">
                                                <div class="text-2xl font-bold text-orange-600">{{ $mealPlan->carbs }}ج</div>
                                                <div class="text-sm text-gray-600">كربوهيدرات</div>
                                            </div>
                                        @endif
                                        
                                        @if($mealPlan->fats)
                                            <div class="bg-yellow-50 p-4 rounded-lg text-center">
                                                <div class="text-2xl font-bold text-yellow-600">{{ $mealPlan->fats }}ج</div>
                                                <div class="text-sm text-gray-600">دهون</div>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Macro Percentages -->
                                    @if($mealPlan->total_macros > 0)
                                        <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                                            <h3 class="text-sm font-medium text-gray-900 mb-3">توزيع المغذيات الكبرى</h3>
                                            <div class="space-y-2">
                                                @if($mealPlan->protein)
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-sm text-gray-600">البروتين</span>
                                                        <div class="flex items-center">
                                                            <div class="w-32 bg-gray-200 rounded-full h-2 mr-2">
                                                                
                                                                <div class="bg-red-500 h-2 rounded-full" style="width: {{ ($mealPlan->protein * 4 / $mealPlan->calories) * 100 }}%"></div>
                                                            </div>
                                                            <span class="text-sm font-medium">{{ round(($mealPlan->protein * 4 / $mealPlan->calories) * 100) }}%</span>
                                                        </div>
                                                    </div>
                                                @endif
                                                
                                                @if($mealPlan->carbs)
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-sm text-gray-600">الكربوهيدرات</span>
                                                        <div class="flex items-center">
                                                            <div class="w-32 bg-gray-200 rounded-full h-2 mr-2">
                                                                <div class="bg-orange-500 h-2 rounded-full" style="width: {{ ($mealPlan->carbs * 4 / $mealPlan->calories) * 100 }}%"></div>
                                                            </div>
                                                            <span class="text-sm font-medium">{{ round(($mealPlan->carbs * 4 / $mealPlan->calories) * 100) }}%</span>
                                                        </div>
                                                    </div>
                                                @endif
                                                
                                                @if($mealPlan->fats)
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-sm text-gray-600">الدهون</span>
                                                        <div class="flex items-center">
                                                            <div class="w-32 bg-gray-200 rounded-full h-2 mr-2">
                                                                <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ ($mealPlan->fats * 9 / $mealPlan->calories) * 100 }}%"></div>
                                                            </div>
                                                            <span class="text-sm font-medium">{{ round(($mealPlan->fats * 9 / $mealPlan->calories) * 100) }}%</span>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </header>

                        <!-- Ingredients Section -->
                        @if($mealPlan->ingredients)
                            <section class="mb-8">
                                <h2 class="text-2xl font-bold text-gray-900 mb-4">المكونات</h2>
                                <div class="bg-gray-50 p-6 rounded-lg">
                                    <div class="prose prose-lg max-w-none text-gray-700">
                                        {!! nl2br(e($mealPlan->ingredients)) !!}
                                    </div>
                                </div>
                            </section>
                        @endif

                        <!-- Instructions Section -->
                        @if($mealPlan->instructions)
                            <section class="mb-8">
                                <h2 class="text-2xl font-bold text-gray-900 mb-4">طريقة التحضير</h2>
                                <div class="bg-gray-50 p-6 rounded-lg">
                                    <div class="prose prose-lg max-w-none text-gray-700">
                                        {!! nl2br(e($mealPlan->instructions)) !!}
                                    </div>
                                </div>
                            </section>
                        @endif

                        <!-- Notes Section -->
                        @if($mealPlan->notes)
                            <section class="mb-8">
                                <h2 class="text-2xl font-bold text-gray-900 mb-4">ملاحظات</h2>
                                <div class="bg-blue-50 p-6 rounded-lg border-r-4 border-blue-400">
                                    <div class="prose prose-lg max-w-none text-gray-700">
                                        {!! nl2br(e($mealPlan->notes)) !!}
                                    </div>
                                </div>
                            </section>
                        @endif

                        <!-- Tags Section -->
                        @if($mealPlan->tags && count($mealPlan->tags) > 0)
                            <section class="mb-8">
                                <h2 class="text-2xl font-bold text-gray-900 mb-4">العلامات</h2>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($mealPlan->tags as $tag)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                            #{{ $tag }}
                                        </span>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        <!-- Back to Meal Plans -->
                        <div class="mt-12 pt-8 border-t border-gray-200">
                            <a href="{{ route('meal-plans.public') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 transition-colors duration-200">
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                العودة إلى الجداول الغذائية
                            </a>
                        </div>
                    </article>
                </div>
            </div>
        </main>
    </div>
    <!-- Footer -->
@include('layouts.footer')

</body>
</html>