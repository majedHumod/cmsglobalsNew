@extends('layouts.client')

@section('title', 'صفحات العضوية')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between gap-3">
        <div>
            <h1 class="text-lg xl:text-2xl font-semibold text-slate-900">صفحات العضوية</h1>
            <p class="text-sm text-slate-500 mt-1">المحتوى المتاح لحسابك</p>
        </div>
        <a href="{{ route('client.more') }}" class="text-xs text-brand font-medium xl:hidden">رجوع</a>
    </div>

    <div class="space-y-3 xl:space-y-0 xl:grid xl:grid-cols-2 2xl:grid-cols-3 xl:gap-4">
        @forelse($pages as $page)
            <a href="{{ route('client.pages.show', $page->slug) }}" class="block rounded-2xl bg-white p-4 xl:p-5 shadow-sm border border-slate-100 hover:border-brand/30 h-full">
                <div class="font-medium text-slate-900">{{ $page->title }}</div>
                @if($page->excerpt)
                    <p class="text-sm text-slate-500 mt-1 line-clamp-2">{{ $page->excerpt }}</p>
                @endif
            </a>
        @empty
            <div class="rounded-2xl bg-white p-6 shadow-sm text-center text-sm text-slate-500 xl:col-span-2 2xl:col-span-3">
                لا توجد صفحات متاحة حالياً.
            </div>
        @endforelse
    </div>
</div>
@endsection
