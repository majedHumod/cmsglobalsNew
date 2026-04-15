<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $landingPage->meta_title ?? $landingPage->title }} - {{ \App\Models\SiteSetting::get('site_name', config('app.name', 'Laravel')) }}</title>
    
    @if($landingPage->meta_description)
        <meta name="description" content="{{ $landingPage->meta_description }}">
    @endif

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
    
    <!-- Preload critical resources -->
    <link rel="preload" href="{{ Storage::url($landingPage->header_image) }}" as="image">
    @if(\App\Models\SiteSetting::get('site_logo'))
        <link rel="preload" href="{{ Storage::url(\App\Models\SiteSetting::get('site_logo')) }}" as="image">
    @endif

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
    
    <!-- Custom Styles for Landing Page -->
    <style>
        .hero-section {
            position: relative;
            background-size: cover !important;
            background-position: center !important;
            background-repeat: no-repeat !important;
            min-height: 600px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-attachment: scroll;
            will-change: transform;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: 1;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            max-width: 800px;
            padding: 0 20px;
        }
        
        .hero-title {
            font-size: 3rem !important;
            font-weight: 700;
            margin-bottom: 1rem !important;
            line-height: 1.2 !important;
        }
        
        .hero-subtitle {
            font-size: 1.5rem !important;
            font-weight: 500;
            margin-bottom: 2rem !important;
            line-height: 1.4 !important;
        }
        
        .join-button {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            font-size: 1.125rem;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            border-radius: 0.375rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .join-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }
        
        .content-section {
            max-width: 1200px !important;
            margin: 0 auto !important;
            padding: 4rem 1rem !important;
        }
        
        .content-section img {
            max-width: 100%;
            height: auto;
            border-radius: 0.5rem;
        }
        
        .membership-card {
            transition: all 0.3s ease;
            border: 1px solid #e5e7eb;            
            display: flex;
            flex-direction: column;
            height: 100%;
            will-change: transform;
        }
        
        .membership-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        .membership-card .p-6 {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .membership-card ul {
            margin-bottom: auto;
        }
        
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.25rem !important;
            }
            
            .hero-subtitle {
                font-size: 1.25rem !important;
            }
            
            .hero-section {
                min-height: 450px !important;
            }
        }
    </style>
</head>
<body class="font-sans antialiased pt-16">
    <!-- Header Navigation with User Menu -->
    @include('components.landing-page-nav')
    
    <!-- Hero Section -->
    <section class="hero-section" style="background-image: url('{{ Storage::url($landingPage->header_image) }}');" loading="eager">
        <div class="hero-content">
            <h1 class="hero-title" style="color: {{ $landingPage->header_text_color }};">{{ $landingPage->title }}</h1>
            
            @if($landingPage->subtitle)
                <p class="hero-subtitle" style="color: {{ $landingPage->header_text_color }};">{{ $landingPage->subtitle }}</p>
            @endif
            
            @if($landingPage->show_join_button && $landingPage->join_button_text && $landingPage->join_button_url)
                <a href="{{ $landingPage->join_button_url }}" class="join-button" style="background-color: {{ $landingPage->join_button_color }}; color: white;">
                    {{ $landingPage->join_button_text }}
                </a>
            @endif
        </div>
    </section>
    
    <!-- Content Section -->
    <section class="content-section" loading="lazy">
        <div class="prose prose-lg max-w-none">
            {!! $landingPage->content !!}
        </div>
    </section>
    
    <!-- Training Sessions Section -->
    @include('components.training-sessions-section')
    
    <!-- Membership Types Section -->
    <section class="bg-gray-50 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold text-gray-900">خطط العضوية</h2>
                <p class="mt-4 text-xl text-gray-600">اختر الخطة المناسبة لاحتياجاتك</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 items-stretch">
                @php
                    try {
                        $membershipTypes = \App\Models\MembershipType::where('is_active', true)
                            ->where('is_protected', false)
                            ->orderBy('sort_order')
                            ->orderBy('price')
                            ->get();
                    } catch (\Exception $e) {
                        $membershipTypes = collect([]);
                    }
                @endphp
                
                @forelse($membershipTypes as $membershipType)
                    <div class="bg-white rounded-lg overflow-hidden shadow-sm membership-card flex flex-col h-full" loading="lazy">
                        <div class="p-6 flex flex-col h-full">
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $membershipType->name }}</h3>
                            
                            @if($membershipType->description)
                                <p class="text-gray-600 mb-4">{{ $membershipType->description }}</p>
                            @endif
                            
                            <div class="flex items-baseline mb-6">
                                <span class="text-4xl font-bold text-indigo-600">{{ $membershipType->formatted_price }}</span>
                                <span class="text-gray-500 mr-2">/ {{ $membershipType->duration_text }}</span>
                            </div>

                            @if($membershipType->features && is_array($membershipType->features) && count($membershipType->features) > 0)
                                <ul class="space-y-3 mb-6">
                                    @foreach($membershipType->features as $feature)
                                        <li class="flex items-center">
                                            <svg class="w-5 h-5 text-green-500 ml-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            <span>{{ $feature }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            <a href="{{ route('register') }}" class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-md transition-colors">
                                اشترك الآن
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-8">
                        <p class="text-gray-500">لا توجد خطط عضوية متاحة حالياً</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    @php
        $articlesEnabled = \App\Models\SiteSetting::get('articles_enabled', true);
        $homepageArticlesCount = (int) \App\Models\SiteSetting::get('articles_count', 3);
        $homepageArticles = collect();
        if ($articlesEnabled) {
            try {
                $homepageArticles = \App\Models\Article::published()
                    ->latest('published_at')
                    ->latest()
                    ->take(max(1, $homepageArticlesCount))
                    ->get();
            } catch (\Throwable $e) {
                $homepageArticles = collect();
            }
        }
    @endphp

    @if($articlesEnabled)
    <section class="bg-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold text-gray-900">أحدث المقالات</h2>
                <p class="mt-4 text-xl text-gray-600">اطلع على مقالاتنا وآخر التحديثات.</p>
            </div>

            @if($homepageArticles->isEmpty())
                <div class="text-center text-gray-500">لا توجد مقالات منشورة حالياً.</div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($homepageArticles as $article)
                        <article class="rounded-lg border border-gray-200 bg-white shadow-sm overflow-hidden">
                            @if($article->image)
                                <img src="{{ Storage::url($article->image) }}" alt="{{ $article->title }}" class="h-44 w-full object-cover">
                            @endif
                            <div class="p-5">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $article->title }}</h3>
                                <p class="text-sm text-gray-600 mb-4">{{ \Illuminate\Support\Str::limit(strip_tags($article->content), 120) }}</p>
                                <a href="{{ route('articles.public.show', $article) }}" class="text-indigo-600 font-medium hover:text-indigo-800">اقرأ المزيد</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif

            <div class="text-center mt-8">
                <a href="{{ route('articles.public.index') }}" class="inline-flex items-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                    عرض كافة المقالات
                </a>
            </div>
        </div>
    </section>
    @endif
    
    <!-- Navigation Links -->
    <section class="bg-gray-50 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-2xl font-bold text-gray-900 mb-8">استكشف المزيد</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <a href="{{ route('home') }}" class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                        <svg class="h-12 w-12 text-indigo-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">الرئيسية</h3>
                        <p class="text-gray-600">العودة إلى الصفحة الرئيسية</p>
                    </a>
                    
                    <a href="{{ route('faqs.index') }}" class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                        <svg class="h-12 w-12 text-indigo-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">الأسئلة الشائعة</h3>
                        <p class="text-gray-600">تصفح الأسئلة والإجابات الشائعة</p>
                    </a>
                    
                    @auth
                        <a href="{{ route('dashboard') }}" class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                            <svg class="h-12 w-12 text-indigo-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">لوحة التحكم</h3>
                            <p class="text-gray-600">الذهاب إلى لوحة التحكم الخاصة بك</p>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                            <svg class="h-12 w-12 text-indigo-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">تسجيل الدخول</h3>
                            <p class="text-gray-600">الدخول إلى حسابك</p>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </section>
    
    <!-- Testimonials Section -->
    @include('components.testimonials-section')
    
    <!-- FAQ Section -->
    <section class="bg-white py-16" loading="lazy">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900">الأسئلة الشائعة</h2>
                <p class="mt-4 text-xl text-gray-600">إجابات على الأسئلة الأكثر شيوعاً</p>
            </div>
            
            <div class="max-w-3xl mx-auto">
                <div class="space-y-6" x-data="{
                    activeAccordion: null,
                    setActiveAccordion(id) {
                        this.activeAccordion = this.activeAccordion === id ? null : id
                    }
                }">
                    @php
                        try {
                            $faqs = \App\Models\Faq::active()->ordered()->take(6)->get();
                        } catch (\Exception $e) {
                            $faqs = collect([]);
                        }
                    @endphp
                    
                    @forelse($faqs as $index => $faq)
                        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                            <button 
                                @click="setActiveAccordion({{ $index }})" 
                                class="flex justify-between items-center w-full px-6 py-4 text-lg font-medium text-right text-gray-900 focus:outline-none"
                                :aria-expanded="activeAccordion === {{ $index }}"
                            >
                                <span>{{ $faq->question }}</span>
                                <svg class="w-5 h-5 text-gray-500 transition-transform duration-300" :class="{'rotate-180': activeAccordion === {{ $index }}}" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                            <div 
                                x-show="activeAccordion === {{ $index }}" 
                                x-collapse
                                class="px-6 pb-4 text-gray-600 prose prose-sm max-w-none"
                            >
                                {!! $faq->answer !!}
                            </div>
                        </div>
                    @empty
                        <!-- Fallback FAQs if no database entries -->
                        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                            <button 
                                @click="setActiveAccordion(1)" 
                                class="flex justify-between items-center w-full px-6 py-4 text-lg font-medium text-right text-gray-900 focus:outline-none"
                                :aria-expanded="activeAccordion === 1"
                            >
                                <span>ما هي مميزات العضوية المدفوعة؟</span>
                                <svg class="w-5 h-5 text-gray-500 transition-transform duration-300" :class="{'rotate-180': activeAccordion === 1}" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                            <div 
                                x-show="activeAccordion === 1" 
                                x-collapse
                                class="px-6 pb-4 text-gray-600"
                            >
                                <p>العضوية المدفوعة توفر لك مجموعة من المميزات الحصرية مثل الوصول إلى محتوى متميز، وجداول غذائية مخصصة، ودعم فني أولوي، بالإضافة إلى تحديثات منتظمة للمحتوى. يمكنك الاطلاع على تفاصيل كل خطة عضوية لمعرفة المميزات المحددة التي تقدمها.</p>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                            <button 
                                @click="setActiveAccordion(2)" 
                                class="flex justify-between items-center w-full px-6 py-4 text-lg font-medium text-right text-gray-900 focus:outline-none"
                                :aria-expanded="activeAccordion === 2"
                            >
                                <span>كيف يمكنني إلغاء اشتراكي؟</span>
                                <svg class="w-5 h-5 text-gray-500 transition-transform duration-300" :class="{'rotate-180': activeAccordion === 2}" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                            <div 
                                x-show="activeAccordion === 2" 
                                x-collapse
                                class="px-6 pb-4 text-gray-600"
                            >
                                <p>يمكنك إلغاء اشتراكك في أي وقت من خلال الذهاب إلى صفحة "إعدادات الحساب" في لوحة التحكم الخاصة بك، ثم النقر على "إدارة الاشتراك" واختيار "إلغاء الاشتراك". سيظل بإمكانك الاستفادة من مميزات العضوية حتى نهاية فترة الاشتراك الحالية.</p>
                            </div>
                        </div>
                    @endforelse
                </div>
                
                <!-- Contact CTA -->
                <div class="mt-10 text-center">
                    <p class="text-gray-600 mb-4">لم تجد إجابة لسؤالك؟</p>
                    <a href="{{ route('faqs.index') }}" class="inline-flex items-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        عرض جميع الأسئلة الشائعة
                    </a>
                    <a href="#" class="inline-flex items-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-indigo-700 bg-white border-indigo-600 hover:bg-indigo-50 mr-4">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        تواصل معنا
                    </a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Site Footer -->
    @include('layouts.footer')
    
    <script>        
        // RTL support for the page
        document.documentElement.dir = 'rtl';
        
        // Performance optimizations
        document.addEventListener('DOMContentLoaded', function() {
            // Lazy load images that are not in viewport
            const images = document.querySelectorAll('img[loading="lazy"]');
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.src = img.dataset.src || img.src;
                            img.classList.remove('lazy');
                            observer.unobserve(img);
                        }
                    });
                });
                
                images.forEach(img => imageObserver.observe(img));
            }
            
            // Optimize scroll performance
            let ticking = false;
            function updateScrollPosition() {
                // Throttle scroll events
                if (!ticking) {
                    requestAnimationFrame(() => {
                        ticking = false;
                    });
                    ticking = true;
                }
            }
            
            window.addEventListener('scroll', updateScrollPosition, { passive: true });
        });
    </script>
</body>
</html>