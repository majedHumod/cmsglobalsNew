@extends('layouts.public-page')

@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($article->content), 160))

@section('title', $article->title)

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
                        <a href="{{ route('articles.public.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-indigo-600 md:ml-2">المقالات</a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="h-6 w-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ $article->title }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
            <article>
                @if($article->image)
                    <div class="mb-8 h-64 w-full overflow-hidden rounded-lg md:h-80">
                        <img src="{{ Storage::url($article->image) }}" alt="{{ $article->title }}" class="h-full w-full object-cover">
                    </div>
                @endif

                <header class="mb-8 text-right" dir="rtl">
                    <h1 class="mb-4 text-3xl font-bold text-gray-900 md:text-4xl">{{ $article->title }}</h1>

                    <div class="mt-4 flex items-center text-sm text-gray-500">
                        <span>نُشر بواسطة {{ $article->user?->name ?? 'إدارة الموقع' }}</span>
                        <span class="mx-2">•</span>
                        <time datetime="{{ optional($article->published_at ?? $article->created_at)->toISOString() }}">
                            {{ ($article->published_at ?? $article->created_at)?->format('d F Y') }}
                        </time>
                    </div>
                </header>

                <div class="prose prose-lg max-w-none text-right" dir="rtl">
                    {!! nl2br(e($article->content)) !!}
                </div>

                @if($article->updated_at != $article->created_at)
                    <footer class="mt-8 border-t border-gray-200 pt-6 text-right" dir="rtl">
                        <p class="text-sm text-gray-500">
                            آخر تحديث: {{ $article->updated_at->format('d F Y H:i') }}
                        </p>
                    </footer>
                @endif
            </article>
        </div>

        @if($relatedArticles->isNotEmpty())
            <section class="mt-10 rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
                <div class="mb-6 flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900">مقالات ذات صلة</h2>
                    <a href="{{ route('articles.public.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                        عرض كافة المقالات
                    </a>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    @foreach($relatedArticles as $related)
                        <a href="{{ route('articles.public.show', $related) }}" class="rounded-lg border border-gray-200 bg-white p-4 text-right text-sm text-gray-700 shadow-sm transition hover:border-indigo-300 hover:shadow-md" dir="rtl">
                            <div class="mb-2 text-xs text-gray-500">
                                {{ ($related->published_at ?? $related->created_at)?->format('d F Y') }}
                            </div>
                            <h3 class="mb-2 text-base font-semibold text-gray-900">{{ $related->title }}</h3>
                            <p>{{ \Illuminate\Support\Str::limit(strip_tags($related->content), 90) }}</p>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</div>
@endsection
