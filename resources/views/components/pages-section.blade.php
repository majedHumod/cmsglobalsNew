<!-- قسم الصفحات -->
@php
    $pagesEnabled = (bool) \App\Models\SiteSetting::get('pages_enabled', true);
    $pagesTitle = \App\Models\SiteSetting::get('pages_title', 'صفحات الموقع');
    $pagesDescription = \App\Models\SiteSetting::get('pages_description', 'تصفح صفحات الموقع والمحتوى المتاح للجميع');
    $pagesCount = \App\Models\SiteSetting::get('pages_count', 6);
@endphp

@if($pagesEnabled)
<section class="bg-gray-50 py-16" dir="rtl">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @php
            try {
                $user = auth()->user();
                $allPages = \App\Models\Page::where('is_published', true)
                    ->orderBy('menu_order')
                    ->orderBy('created_at', 'desc')
                    ->get();
                
                $pages = $allPages
                    ->filter(fn ($page) => $page->canAccess($user))
                    ->take($pagesCount);
            } catch (\Exception $e) {
                $pages = collect([]);
            }
        @endphp

        @if($pages->count() > 0)
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">{{ $pagesTitle }}</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    {{ $pagesDescription }}
                </p>
            </div>

            <!-- Pages Content -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($pages as $page)
                        <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-200" loading="lazy">
                            @if($page->featured_image)
                                <div class="h-48 overflow-hidden">
                                    <img src="{{ Storage::url($page->featured_image) }}" alt="{{ $page->title }}" class="w-full h-full object-cover" loading="lazy" decoding="async">
                                </div>
                            @else
                                <div class="h-48 bg-gradient-to-br from-indigo-400 to-blue-500 flex items-center justify-center">
                                    <svg class="h-16 w-16 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                            @endif
                            
                            <div class="p-6">
                                <!-- Page Info -->
                                <div class="mb-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <h3 class="text-lg font-bold text-gray-900" style="direction: rtl; text-align: right;">
                                            {{ $page->title }}
                                        </h3>
                                        <span class="text-lg">{{ $page->access_level_icon }}</span>
                                    </div>
                                    
                                    @if($page->excerpt)
                                        <p class="text-gray-600 text-sm leading-relaxed" style="direction: rtl; text-align: right;">
                                            {{ Str::limit($page->excerpt, 120) }}
                                        </p>
                                    @endif
                                </div>

                                <!-- Page Details -->
                                <div class="flex items-center justify-between mb-4 text-sm text-gray-500">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span>{{ $page->user->name }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span>{{ $page->published_at ? $page->published_at->format('d/m/Y') : $page->created_at->format('d/m/Y') }}</span>
                                    </div>
                                </div>

                                <!-- Read More Button -->
                                <div class="mt-4">
                                    <a href="{{ route('pages.show', $page->slug) }}" class="block w-full text-center bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-md hover:shadow-lg">
                                        قراءة المزيد
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- View All Pages Button -->
                @php
                    $totalPages = $allPages->count();
                @endphp
                @if($totalPages > $pagesCount)
                    <div class="mt-8 text-center">
                        <a href="{{ route('pages.public') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            عرض جميع الصفحات ({{ $totalPages }})
                        </a>
                    </div>
                @endif
            </div>
        @endif
    </div>
</section>
@endif

<style>
    /* Pages specific styles */
    .page-card {
        transition: all 0.3s ease;
        will-change: transform;
        transform: translateZ(0);
    }
    
    .page-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px rgba(0, 0, 0, 0.1);
    }
    
    /* RTL support for pages */
    [dir="rtl"] .page-card {
        text-align: right;
    }
    
    /* Responsive design */
    @media (max-width: 768px) {
        .page-card {
            height: auto;
        }
        
        .page-card h3 {
            font-size: 1.125rem;
        }
        
        .page-card p {
            font-size: 0.875rem;
        }
    }
    
    /* Hover effects */
    .page-card:hover .bg-white {
        background-color: #f3f4f6;
    }
    
    .page-card:hover .transform {
        transform: scale(1.02);
    }
</style>