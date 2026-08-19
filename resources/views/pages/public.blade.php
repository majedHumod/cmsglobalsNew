<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('صفحات الموقع') }}
            </h2>
            @can('view pages')
                <a href="{{ route('pages.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-orange-500 hover:bg-orange-600">
                    إدارة الصفحات
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($pages->isEmpty())
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">لا توجد صفحات منشورة</h3>
                    <p class="mt-1 text-sm text-gray-500">لم يتم نشر أي صفحات بعد.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($pages as $page)
                        <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                            @if($page->featured_image)
                                <img src="{{ Storage::url($page->featured_image) }}" alt="{{ $page->title }}" class="w-full h-48 object-cover">
                            @else
                                <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                    <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                            @endif
                            
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $page->title }}</h3>
                                
                                @if($page->excerpt)
                                    <p class="text-gray-600 text-sm mb-4">{{ Str::limit($page->excerpt, 120) }}</p>
                                @endif
                                
                                <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                    <span>{{ $page->user->name }}</span>
                                    @if($page->published_at)
                                        <span>{{ $page->published_at->format('d/m/Y') }}</span>
                                    @endif
                                </div>
                                
                                <a href="{{ route('pages.show', $page->slug) }}" class="block w-full text-center bg-orange-500 hover:bg-orange-600 text-white font-medium py-2 px-4 rounded-md transition-colors">
                                    قراءة المزيد
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-8">
                    {{ $pages->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>