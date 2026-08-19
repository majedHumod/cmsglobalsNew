@extends('layouts.client')

@section('title', $page->title)

@section('content')
<article class="space-y-4 xl:max-w-3xl xl:mx-auto">
    <div class="flex items-center justify-between gap-3">
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('client.pages.index') }}" class="text-xs text-brand font-medium">رجوع</a>
        <a href="{{ route('client.more') }}" class="text-xs text-slate-500 xl:hidden">المزيد</a>
    </div>

    <header class="rounded-2xl bg-white p-5 xl:p-8 shadow-sm border border-slate-100">
        <h1 class="text-xl xl:text-3xl font-bold text-slate-900 leading-snug">{{ $page->title }}</h1>
        @if($page->excerpt)
            <p class="text-sm xl:text-base text-slate-500 mt-2">{{ $page->excerpt }}</p>
        @endif
        @if($page->featured_image)
            <img src="{{ Storage::url($page->featured_image) }}" alt="{{ $page->title }}" class="mt-4 w-full rounded-xl object-cover max-h-56 xl:max-h-80">
        @endif
    </header>

    <div class="rounded-2xl bg-white p-5 xl:p-8 shadow-sm border border-slate-100 prose prose-sm xl:prose-base max-w-none text-slate-800">
        {!! $page->content !!}
    </div>
</article>

@push('head')
<style>
    .prose img { max-width: 100%; height: auto; border-radius: 0.75rem; margin: 1rem 0; }
    .prose table { width: 100%; border-collapse: collapse; margin: 1rem 0; font-size: 0.875rem; }
    .prose th, .prose td { border: 1px solid #e2e8f0; padding: 0.5rem; }
</style>
@endpush
@endsection
