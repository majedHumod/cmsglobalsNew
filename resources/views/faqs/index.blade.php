<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>الأسئلة الشائعة - {{ \App\Models\SiteSetting::get('site_name', config('app.name', 'Laravel')) }}</title>

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
        /* FAQ specific styles */
        .faq-content {
            direction: rtl;
            text-align: right;
        }
        
        .faq-answer {
            direction: rtl;
            text-align: right;
        }
        
        .faq-answer p {
            text-align: right;
            direction: rtl;
        }
        
        .faq-answer ul, .faq-answer ol {
            text-align: right;
            direction: rtl;
            padding-right: 1.5rem;
            padding-left: 0;
        }
        
        .category-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .category-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .category-card.active {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
        }
        
        .faq-item {
            transition: all 0.3s ease;
        }
        
        .faq-item:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="font-sans antialiased pt-16" dir="rtl">
    <div class="min-h-screen bg-gray-100">
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
                                <span class="ml-1 md:ml-2 text-sm font-medium text-gray-500">الأسئلة الشائعة</span>
                            </div>
                        </li>
                    </ol>
                </nav>

                <!-- Hero Section -->
                <div class="text-center mb-12">
                    <h1 class="text-4xl font-bold text-gray-900 mb-4">كيف يمكننا مساعدتك؟</h1>
                    
                    <!-- Search Bar -->
                    <div class="max-w-2xl mx-auto">
                        <div class="relative">
                            <input 
                                type="text" 
                                id="faq-search" 
                                placeholder="ابحث في الأسئلة الشائعة..." 
                                class="w-full px-6 py-4 text-lg border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm text-right"
                                dir="rtl"
                            >
                            <button 
                                id="search-button"
                                class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-medium transition-colors"
                            >
                                بحث
                            </button>
                        </div>
                    </div>
                </div>

                @php
                    // جلب الفئات الفعلية من قاعدة البيانات
                    try {
                        $actualCategories = \App\Models\Faq::select('category')
                            ->where('is_active', true)
                            ->distinct()
                            ->orderByRaw("CASE 
                                WHEN category = 'عام' THEN 1
                                WHEN category = 'العضويات' THEN 2
                                WHEN category = 'الدفع' THEN 3
                                WHEN category = 'الحساب' THEN 4
                                WHEN category = 'المحتوى' THEN 5
                                WHEN category = 'الدعم الفني' THEN 6
                                ELSE 7 END")
                            ->pluck('category')
                            ->toArray();
                    } catch (\Exception $e) {
                        $actualCategories = ['عام', 'العضويات', 'الدفع', 'الحساب'];
                    }

                    // تعيين الأيقونات والألوان للفئات
                    $categoryIcons = [
                        'عام' => [
                            'icon' => '<svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path></svg>',
                            'bg_color' => 'bg-orange-100',
                            'text_color' => 'text-orange-600',
                            'description' => 'أسئلة عامة ومعلومات أساسية'
                        ],
                        'العضويات' => [
                            'icon' => '<svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"></path></svg>',
                            'bg_color' => 'bg-purple-100',
                            'text_color' => 'text-purple-600',
                            'description' => 'معلومات حول العضويات والاشتراكات'
                        ],
                        'الدفع' => [
                            'icon' => '<svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"></path><path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"></path></svg>',
                            'bg_color' => 'bg-blue-100',
                            'text_color' => 'text-blue-600',
                            'description' => 'معلومات حول المدفوعات والفواتير'
                        ],
                        'الحساب' => [
                            'icon' => '<svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path></svg>',
                            'bg_color' => 'bg-green-100',
                            'text_color' => 'text-green-600',
                            'description' => 'إدارة حسابك وإعداداتك الشخصية'
                        ],
                        'المحتوى' => [
                            'icon' => '<svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path></svg>',
                            'bg_color' => 'bg-indigo-100',
                            'text_color' => 'text-indigo-600',
                            'description' => 'أسئلة حول المحتوى والصفحات'
                        ],
                        'الدعم الفني' => [
                            'icon' => '<svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"></path></svg>',
                            'bg_color' => 'bg-teal-100',
                            'text_color' => 'text-teal-600',
                            'description' => 'تواصل مع فريق الدعم الفني'
                        ],
                        'البرامج' => [
                            'icon' => '<svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"></path></svg>',
                            'bg_color' => 'bg-cyan-100',
                            'text_color' => 'text-cyan-600',
                            'description' => 'معلومات حول البرامج والخدمات'
                        ],
                        'التدريب' => [
                            'icon' => '<svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"></path><path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z"></path></svg>',
                            'bg_color' => 'bg-red-100',
                            'text_color' => 'text-red-600',
                            'description' => 'أسئلة حول التدريب والتمارين'
                        ],
                        'التغذية' => [
                            'icon' => '<svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12 1.586l-4 4v12.828l4-4V1.586zM3.707 3.293A1 1 0 002 4v4a1 1 0 00.293.707L6 12.414V5.586L3.707 3.293zM17.707 5.293L14 1.586v12.828l2.293 2.293A1 1 0 0018 16v-4a1 1 0 00-.293-.707z" clip-rule="evenodd"></path></svg>',
                            'bg_color' => 'bg-lime-100',
                            'text_color' => 'text-lime-600',
                            'description' => 'معلومات حول التغذية والوجبات'
                        ],
                        'الصحة' => [
                            'icon' => '<svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path></svg>',
                            'bg_color' => 'bg-pink-100',
                            'text_color' => 'text-pink-600',
                            'description' => 'نصائح ومعلومات صحية'
                        ],
                        'البرامج' => [
                            'icon' => '<svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"></path></svg>',
                            'bg_color' => 'bg-cyan-100',
                            'text_color' => 'text-cyan-600',
                            'description' => 'معلومات حول البرامج والخدمات'
                        ],
                        'التدريب' => [
                            'icon' => '<svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"></path><path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z"></path></svg>',
                            'bg_color' => 'bg-red-100',
                            'text_color' => 'text-red-600',
                            'description' => 'أسئلة حول التدريب والتمارين'
                        ],
                        'التغذية' => [
                            'icon' => '<svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12 1.586l-4 4v12.828l4-4V1.586zM3.707 3.293A1 1 0 002 4v4a1 1 0 00.293.707L6 12.414V5.586L3.707 3.293zM17.707 5.293L14 1.586v12.828l2.293 2.293A1 1 0 0018 16v-4a1 1 0 00-.293-.707z" clip-rule="evenodd"></path></svg>',
                            'bg_color' => 'bg-lime-100',
                            'text_color' => 'text-lime-600',
                            'description' => 'معلومات حول التغذية والوجبات'
                        ],
                        'الصحة' => [
                            'icon' => '<svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path></svg>',
                            'bg_color' => 'bg-pink-100',
                            'text_color' => 'text-pink-600',
                            'description' => 'نصائح ومعلومات صحية'
                        ]
                    ];
                @endphp

                <!-- Category Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-12">
                    <!-- View All Button -->
                    <div class="category-card bg-white rounded-xl shadow-sm border-2 border-gray-200 p-6 text-center" data-category="all">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4 mx-auto text-gray-600">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-2">جميع الأسئلة</h3>
                        <p class="text-xs text-gray-600">عرض كافة الأسئلة</p>
                    </div>

                    @foreach($actualCategories as $category)
                        @php
                            $categoryData = $categoryIcons[$category] ?? [
                                'icon' => '<svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path></svg>',
                                'bg_color' => 'bg-gray-100',
                                'text_color' => 'text-gray-600',
                                'description' => 'أسئلة متنوعة'
                            ];
                        @endphp
                        <div class="category-card bg-white rounded-xl shadow-sm border-2 border-gray-200 p-6 text-center {{ $loop->first ? 'active' : '' }}" data-category="{{ $category }}">
                            <div class="w-16 h-16 {{ $categoryData['bg_color'] }} rounded-full flex items-center justify-center mb-4 mx-auto {{ $categoryData['text_color'] }}">
                                {!! $categoryData['icon'] !!}
                            </div>
                            <h3 class="text-sm font-semibold text-gray-900 mb-2">{{ $category }}</h3>
                            <p class="text-xs text-gray-600">{{ $categoryData['description'] }}</p>
                        </div>
                    @endforeach
                </div>

                <!-- FAQ Content -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8" dir="rtl">
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2" id="section-title">المواضيع الشائعة</h2>
                        <p class="text-gray-600" id="section-description">الأسئلة الأكثر شيوعاً والتي يبحث عنها المستخدمون</p>
                    </div>

                    <!-- No Results Message -->
                    <div id="no-results" class="text-center py-12 hidden">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <h3 class="mt-2 text-lg font-medium text-gray-900">لا توجد نتائج</h3>
                        <p class="mt-1 text-sm text-gray-500">لم يتم العثور على أسئلة تطابق بحثك.</p>
                    </div>





                    
                    @if($faqs->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="mt-2 text-lg font-medium text-gray-900">لا توجد أسئلة شائعة</h3>
                            <p class="mt-1 text-sm text-gray-500">لم يتم إضافة أي أسئلة شائعة بعد.</p>
                        </div>
                   
                    @else
                        <!-- FAQ Content -->
                        <div id="faq-content">
                            @foreach($faqs as $categoryName => $categoryFaqs)
                                <div class="faq-category mb-8" data-category="{{ $categoryName }}" style="{{ $categoryName !== 'عام' ? 'display: none;' : '' }}">
                                    <h3 class="text-xl font-semibold text-gray-900 mb-6 faq-content">{{ $categoryName }}</h3>
                                    
                                    <!-- FAQ Grid for this category -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                                            @foreach($categoryFaqs as $faq)
                                                <div class="faq-item border border-gray-200 rounded-lg" 
                                                    data-question="{{ strtolower($faq->question) }}" 
                                                    data-answer="{{ strtolower(strip_tags($faq->answer)) }}">
                                                    
                                                    <button 
                                                        class="faq-toggle w-full text-right px-6 py-4 focus:outline-none hover:bg-gray-50 transition-colors duration-200 flex justify-between items-center"
                                                        data-target="faq-{{ $faq->id }}">
                                                        <span class="font-medium text-gray-900 text-sm faq-content">{{ $faq->question }}</span>
                                                        <svg class="w-5 h-5 text-gray-500 transition-transform duration-300 flex-shrink-0 mr-4 faq-arrow" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                        </svg>
                                                    </button>

                                                    <div id="faq-{{ $faq->id }}" class="faq-answer hidden px-6 py-4 border-t border-gray-200 text-gray-700 text-sm">
                                                        {!! $faq->answer !!}
                                                    </div>
                                                </div>
                                            @endforeach

                                    </div>
                                         
                                </div>

                            @endforeach
                        </div>

                        <!-- Contact CTA -->
                        <div class="mt-12 text-center bg-gray-50 rounded-xl p-8">
                            <h3 class="text-xl font-semibold text-gray-900 mb-2 faq-content">لم تجد إجابة لسؤالك؟</h3>
                            <p class="text-gray-600 mb-6 faq-content">تواصل مع فريق الدعم الفني للحصول على مساعدة شخصية</p>
                            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                                @php
                                    $contactEmail = \App\Models\SiteSetting::get('contact_email');
                                @endphp
                                <a href="{{ $contactEmail ? 'mailto:' . $contactEmail : '#' }}" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    تواصل معنا
                                </a>
                                @php
                                    $contactWhatsapp = \App\Models\SiteSetting::get('contact_whatsapp');
                                @endphp
                                <a href="{{ $contactWhatsapp ? 'https://wa.me/' . str_replace(['+', ' ', '-'], '', $contactWhatsapp) : '#' }}" target="_blank" class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-base font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"></path>
                                    </svg>
                                    واتساب
                                </a>
                            </div>
                        </div>

                        
                    @endif



                </div>
            </div>
        </main>
        
        <!-- Footer -->
        @include('layouts.footer')
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('faq-search');
            const searchButton = document.getElementById('search-button');
            const categoryCards = document.querySelectorAll('.category-card');
            const faqCategories = document.querySelectorAll('.faq-category');
            const faqItems = document.querySelectorAll('.faq-item');
            const faqToggles = document.querySelectorAll('.faq-toggle');
            const sectionTitle = document.getElementById('section-title');
            const sectionDescription = document.getElementById('section-description');
            const noResults = document.getElementById('no-results');
            
            let currentCategory = 'عام';
            let searchQuery = '';

            // Category selection
            categoryCards.forEach(card => {
                card.addEventListener('click', function() {
                    const category = this.dataset.category;
                    
                    // Update active state
                    categoryCards.forEach(c => c.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Update current category
                    currentCategory = category;
                    searchQuery = '';
                    searchInput.value = '';
                    
                    // Update section title
                    if (category === 'all') {
                        sectionTitle.textContent = 'جميع الأسئلة الشائعة';
                        sectionDescription.textContent = 'جميع الأسئلة والإجابات المتاحة';
                    } else {
                        sectionTitle.textContent = 'أسئلة ' + category;
                        sectionDescription.textContent = 'الأسئلة المتعلقة بـ ' + category;
                    }
                    
                    filterFAQs();
                });
            });

            // FAQ toggle functionality
            faqToggles.forEach(toggle => {
                toggle.addEventListener('click', function() {
                    const targetId = this.dataset.target;
                    const target = document.getElementById(targetId);
                    const arrow = this.querySelector('.faq-arrow');
                    
                    if (target.classList.contains('hidden')) {
                        target.classList.remove('hidden');
                        arrow.style.transform = 'rotate(180deg)';
                    } else {
                        target.classList.add('hidden');
                        arrow.style.transform = 'rotate(0deg)';
                    }
                });
            });

            // Search functionality
            function performSearch() {
                searchQuery = searchInput.value.trim().toLowerCase();
                filterFAQs();
            }

            searchButton.addEventListener('click', performSearch);
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    performSearch();
                }
            });

            // Real-time search
            searchInput.addEventListener('input', function() {
                searchQuery = this.value.trim().toLowerCase();
                if (searchQuery.length > 2 || searchQuery.length === 0) {
                    filterFAQs();
                }
            });

            // Filter FAQs based on category and search
            function filterFAQs() {
                let visibleCount = 0;
                
                faqCategories.forEach(categoryDiv => {
                    const categoryName = categoryDiv.dataset.category;
                    let categoryVisible = false;
                    
                    if (currentCategory === 'all' || currentCategory === categoryName) {
                        const faqsInCategory = categoryDiv.querySelectorAll('.faq-item');
                        
                        faqsInCategory.forEach(faqItem => {
                            const question = faqItem.dataset.question;
                            const answer = faqItem.dataset.answer;
                            
                            if (searchQuery === '' || 
                                question.includes(searchQuery) || 
                                answer.includes(searchQuery)) {
                                faqItem.style.display = 'block';
                                categoryVisible = true;
                                visibleCount++;
                            } else {
                                faqItem.style.display = 'none';
                            }
                        });
                        
                        categoryDiv.style.display = categoryVisible ? 'block' : 'none';
                    } else {
                        categoryDiv.style.display = 'none';
                    }
                });
                
                // Show/hide no results message
                if (visibleCount === 0 && (searchQuery !== '' || currentCategory !== 'all')) {
                    noResults.classList.remove('hidden');
                } else {
                    noResults.classList.add('hidden');
                }
            }

            // Initialize with General category
            filterFAQs();
        });
    </script>
</body>
</html>