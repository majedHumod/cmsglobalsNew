@extends('layouts.public-page')

@section('title', 'المقالات')

@section('content')
<div class="py-10">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <nav class="mb-4 flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 space-x-reverse md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('home') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-indigo-600">
                        <svg class="ml-2 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                        </svg>
                        الرئيسية
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="h-6 w-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">المقالات</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
            <header class="mb-8 text-right" dir="rtl">
                <h1 class="mb-3 text-3xl font-bold text-gray-900 md:text-4xl">جميع المقالات</h1>
                <p class="text-lg text-gray-600">تصفح أحدث المقالات المنشورة على الموقع.</p>
            </header>

            @if($articles->isEmpty())
                <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 px-6 py-12 text-center">
                    <p class="text-gray-500">لا توجد مقالات منشورة حالياً.</p>
                </div>
            @else
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($articles as $article)
                        <article class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                            @if($article->image)
                                <div class="h-52 w-full overflow-hidden bg-gray-100">
                                    <img src="{{ Storage::url($article->image) }}" alt="{{ $article->title }}" class="h-full w-full object-cover">
                                </div>
                            @endif

                            <div class="p-6 text-right" dir="rtl">
                                <div class="mb-4 flex items-center text-sm text-gray-500">
                                    <span>نُشر بواسطة {{ $article->user?->name ?? 'إدارة الموقع' }}</span>
                                    <span class="mx-2">•</span>
                                    <time datetime="{{ optional($article->published_at ?? $article->created_at)->toISOString() }}">
                                        {{ ($article->published_at ?? $article->created_at)?->format('d F Y') }}
                                    </time>
                                </div>

                                <h2 class="mb-3 text-xl font-bold text-gray-900">{{ $article->title }}</h2>
                                <p class="mb-5 text-sm leading-7 text-gray-600">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($article->content), 140) }}
                                </p>

                                <a href="{{ route('articles.public.show', $article) }}" class="inline-flex items-center rounded-md border border-indigo-300 bg-indigo-50 px-4 py-2 text-sm font-medium text-indigo-700 transition hover:bg-indigo-100">
                                    اقرأ المزيد
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $articles->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
