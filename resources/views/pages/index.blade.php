@extends('layouts.admin')

@section('title', 'إدارة الصفحات')
@section('header', 'إدارة الصفحات')

@section('header_actions')
@can('create', \App\Models\Page::class)
    <x-admin.button :href="route('pages.create')" variant="primary">إضافة صفحة جديدة</x-admin.button>
@endcan
@endsection

@section('subheader')
<div class="flex w-full flex-wrap items-end justify-between gap-3">
    <div class="flex items-center overflow-x-auto">
        <a href="{{ route('pages.index', ['tab' => 'all']) }}" class="admin-tab {{ ($tab ?? 'all') === 'all' ? 'is-active' : '' }}">الكل</a>
        <a href="{{ route('pages.index', ['tab' => 'published']) }}" class="admin-tab {{ ($tab ?? '') === 'published' ? 'is-active' : '' }}">منشور</a>
        <a href="{{ route('pages.index', ['tab' => 'draft']) }}" class="admin-tab {{ ($tab ?? '') === 'draft' ? 'is-active' : '' }}">مسودة</a>
        @if(auth()->user()->hasRole('admin'))
            <a href="{{ route('pages.index', ['tab' => 'mine']) }}" class="admin-tab {{ ($tab ?? '') === 'mine' ? 'is-active' : '' }}">صفحاتي</a>
        @endif
        <a href="{{ route('pages.index', ['tab' => 'stats']) }}" class="admin-tab {{ ($tab ?? '') === 'stats' ? 'is-active' : '' }}">الإحصائيات</a>
    </div>
    <span class="text-xs text-tremor-content">
        @if(($tab ?? 'all') !== 'stats' && method_exists($pages, 'total'))
            {{ $pages->total() }} صفحة
        @else
            {{ $stats['total'] ?? $pages->count() }} صفحة
        @endif
    </span>
</div>
@endsection

@section('content')
@php $tab = $tab ?? 'all'; @endphp

<div class="space-y-4">
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-3">
        <div class="admin-kpi">
            <div class="admin-kpi-label">إجمالي الصفحات</div>
            <div class="admin-kpi-value">{{ number_format($stats['total'] ?? 0) }}</div>
        </div>
        <div class="admin-kpi">
            <div class="admin-kpi-label">منشورة</div>
            <div class="admin-kpi-value">{{ number_format($stats['published'] ?? 0) }}</div>
            <div class="admin-kpi-meta up">جاهزة للعرض</div>
        </div>
        <div class="admin-kpi">
            <div class="admin-kpi-label">مسودات</div>
            <div class="admin-kpi-value">{{ number_format($stats['draft'] ?? 0) }}</div>
        </div>
        <div class="admin-kpi">
            <div class="admin-kpi-label">في القائمة</div>
            <div class="admin-kpi-value">{{ number_format($stats['in_menu'] ?? 0) }}</div>
        </div>
    </div>

    @if($tab === 'stats')
        <section class="admin-card overflow-hidden">
            <div class="border-b border-tremor-border px-5 py-4">
                <h3 class="text-sm font-semibold text-tremor-content-strong">آخر الصفحات</h3>
            </div>
            <div class="divide-y divide-tremor-border">
                @forelse($pages as $page)
                    <div class="flex items-center justify-between gap-3 px-5 py-3.5">
                        <div class="min-w-0 flex items-center gap-3">
                            <span class="h-2 w-2 rounded-full {{ $page->is_published ? 'bg-emerald-500' : 'bg-tremor-content-subtle' }} shrink-0"></span>
                            <div class="min-w-0">
                                <div class="truncate text-sm font-medium text-tremor-content-strong">{{ $page->title }}</div>
                                <div class="text-xs text-tremor-content-subtle">{{ $page->user->name ?? '—' }} · {{ $page->access_level_text }}</div>
                            </div>
                        </div>
                        <span class="text-xs text-tremor-content-subtle shrink-0">{{ $page->updated_at?->diffForHumans() ?? '—' }}</span>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-tremor-content">لا يوجد نشاط بعد.</div>
                @endforelse
            </div>
        </section>
    @else
        @if($pages->isEmpty())
            <div class="admin-card px-6 py-16 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-tremor-background-muted">
                    <svg class="h-7 w-7 text-tremor-content-subtle" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-sm font-semibold text-tremor-content-strong">لا توجد صفحات</h3>
                <p class="mt-1 text-sm text-tremor-content">ابدأ بإنشاء صفحة جديدة لموقعك.</p>
                @can('create', \App\Models\Page::class)
                <div class="mt-4">
                    <x-admin.button :href="route('pages.create')" variant="primary">إضافة صفحة جديدة</x-admin.button>
                </div>
                @endcan
            </div>
        @else
            <div class="admin-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-tremor-border text-tremor-label text-tremor-content-subtle">
                                <th class="px-4 py-3 text-right font-medium">العنوان</th>
                                <th class="px-4 py-3 text-right font-medium">الحالة</th>
                                <th class="px-4 py-3 text-right font-medium">الوصول</th>
                                <th class="px-4 py-3 text-right font-medium">المؤلف</th>
                                <th class="px-4 py-3 text-right font-medium">تاريخ النشر</th>
                                <th class="px-4 py-3 text-right font-medium">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-tremor-border">
                            @foreach($pages as $page)
                                <tr class="hover:bg-tremor-background-muted/80">
                                    <td class="px-4 py-3">
                                        <div class="text-sm font-semibold text-tremor-content-strong">{{ $page->title }}</div>
                                        <div class="text-xs text-tremor-content-subtle">{{ $page->slug }}</div>
                                        @if($page->excerpt)
                                            <div class="mt-0.5 text-xs text-tremor-content">{{ Str::limit($page->excerpt, 50) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-col gap-1 items-start">
                                            <span class="admin-pill {{ $page->is_published ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-100 text-gray-700 border-gray-200' }}">
                                                {{ $page->is_published ? 'منشور' : 'مسودة' }}
                                            </span>
                                            @if($page->show_in_menu)
                                                <span class="admin-pill bg-orange-50 text-orange-700 border-orange-200">في القائمة</span>
                                            @endif
                                            @if($page->is_premium)
                                                <span class="admin-pill bg-amber-50 text-amber-800 border-amber-200">مدفوع</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-tremor-content-emphasis">
                                        <div class="flex items-center gap-1">
                                            <span>{{ $page->access_level_icon }}</span>
                                            <span>{{ $page->access_level_text }}</span>
                                        </div>
                                        @if($page->access_level === 'membership')
                                            @php
                                                $membershipTypes = is_array($page->required_membership_types)
                                                    ? $page->required_membership_types
                                                    : (json_decode($page->required_membership_types ?? '[]', true) ?: []);
                                            @endphp
                                            <div class="text-xs text-tremor-content-subtle mt-0.5">{{ count($membershipTypes) }} عضوية</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-tremor-content">{{ $page->user->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-tremor-content-subtle whitespace-nowrap">
                                        {{ $page->published_at ? $page->published_at->format('d/m/Y H:i') : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <x-admin.actions>
                                            @if($page->is_published)
                                                <x-admin.action :href="route('pages.show', $page->slug)" target="_blank">عرض</x-admin.action>
                                            @endif
                                            @can('update', $page)
                                                <x-admin.action :href="route('pages.edit', $page)">تعديل</x-admin.action>
                                            @endcan
                                            @can('delete', $page)
                                                <form action="{{ route('pages.destroy', $page) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <x-admin.action type="submit" tone="danger" confirm="هل أنت متأكد من حذف هذه الصفحة؟">حذف</x-admin.action>
                                                </form>
                                            @endcan
                                        </x-admin.actions>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-tremor-border px-4 py-3">
                    {{ $pages->links() }}
                </div>
            </div>
        @endif
    @endif
</div>
@endsection
