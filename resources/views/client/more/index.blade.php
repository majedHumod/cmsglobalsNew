@extends('layouts.client')

@section('title', 'المزيد')

@section('content')
<div class="space-y-5">
    <div>
        <h1 class="text-lg xl:text-2xl font-semibold text-slate-900">المزيد</h1>
        <p class="text-sm text-slate-500 mt-1">محتوى العضوية والخدمات وحسابك</p>
    </div>

    <div class="space-y-5 xl:grid xl:grid-cols-3 xl:gap-6 xl:space-y-0 xl:items-start">
        <section class="rounded-2xl bg-white shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-900">محتوى العضوية</h2>
                <a href="{{ route('client.pages.index') }}" class="text-xs text-brand font-medium">عرض الكل</a>
            </div>
            @forelse($memberPages->take(6) as $page)
                <a href="{{ route('client.pages.show', $page->slug) }}" class="flex items-center justify-between gap-3 px-4 py-3 border-b border-slate-50 last:border-0 hover:bg-slate-50">
                    <div class="min-w-0 text-right">
                        <div class="text-sm font-medium text-slate-900 truncate">{{ $page->title }}</div>
                        @if($page->excerpt)
                            <div class="text-xs text-slate-500 mt-0.5 line-clamp-1">{{ $page->excerpt }}</div>
                        @endif
                    </div>
                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
            @empty
                <div class="px-4 py-6 text-sm text-slate-500 text-center">
                    لا توجد صفحات مخصّصة لك حالياً.
                </div>
            @endforelse
        </section>

        <section class="rounded-2xl bg-white shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100">
                <h2 class="text-sm font-semibold text-slate-900">الخدمات</h2>
            </div>
            <a href="{{ route('client.bookings.index') }}" class="flex items-center justify-between px-4 py-3 border-b border-slate-50 hover:bg-slate-50">
                <span class="text-sm text-slate-800">الحجوزات</span>
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <a href="{{ route('client.nutrition.index') }}" class="flex items-center justify-between px-4 py-3 border-b border-slate-50 hover:bg-slate-50">
                <span class="text-sm text-slate-800">التغذية</span>
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <a href="{{ route('client.community.index') }}" class="flex items-center justify-between px-4 py-3 border-b border-slate-50 hover:bg-slate-50">
                <span class="text-sm text-slate-800">المجتمع</span>
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <a href="{{ route('client.challenges.index') }}" class="flex items-center justify-between px-4 py-3 hover:bg-slate-50">
                <span class="text-sm text-slate-800">التحديات</span>
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
        </section>

        <section class="rounded-2xl bg-white shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100">
                <h2 class="text-sm font-semibold text-slate-900">الحساب</h2>
            </div>
            <a href="{{ route('client.notifications.index') }}" class="flex items-center justify-between px-4 py-3 border-b border-slate-50 hover:bg-slate-50">
                <span class="text-sm text-slate-800">الإشعارات</span>
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <a href="{{ $profileUrl }}" class="flex items-center justify-between px-4 py-3 border-b border-slate-50 hover:bg-slate-50">
                <span class="text-sm text-slate-800">الملف الشخصي</span>
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <a href="{{ route('home') }}" class="flex items-center justify-between px-4 py-3 border-b border-slate-50 hover:bg-slate-50">
                <span class="text-sm text-slate-800">الموقع الرئيسي</span>
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="px-4 py-3">
                @csrf
                <button type="submit" class="w-full text-right text-sm text-rose-600 font-medium">تسجيل الخروج</button>
            </form>
        </section>
    </div>
</div>
@endsection
