@extends('layouts.admin')

@section('title', 'الملاحظات')
@section('header', 'الملاحظات')

@section('header_actions')
    <x-admin.button :href="route('notes.create')" variant="primary">إضافة ملاحظة</x-admin.button>
@endsection

@section('subheader')
<div class="flex w-full flex-wrap items-end justify-between gap-3">
    <div class="flex items-center overflow-x-auto">
        <a href="{{ route('notes.index', ['tab' => 'all']) }}" class="admin-tab {{ ($tab ?? 'all') === 'all' ? 'is-active' : '' }}">جميع الملاحظات</a>
        @if(auth()->user()->hasRole('admin'))
            <a href="{{ route('notes.index', ['tab' => 'mine']) }}" class="admin-tab {{ ($tab ?? '') === 'mine' ? 'is-active' : '' }}">ملاحظاتي</a>
        @endif
        <a href="{{ route('notes.index', ['tab' => 'stats']) }}" class="admin-tab {{ ($tab ?? '') === 'stats' ? 'is-active' : '' }}">الإحصائيات</a>
    </div>
    <span class="text-xs text-tremor-content">
        @if(($tab ?? 'all') !== 'stats' && method_exists($notes, 'total'))
            {{ $notes->total() }} ملاحظة
        @else
            {{ $stats['total'] ?? $notes->count() }} ملاحظة
        @endif
    </span>
</div>
@endsection

@section('content')
@php $tab = $tab ?? 'all'; @endphp

<div class="space-y-4">
    <div class="grid grid-cols-2 xl:grid-cols-3 gap-3">
        <div class="admin-kpi">
            <div class="admin-kpi-label">إجمالي الملاحظات</div>
            <div class="admin-kpi-value">{{ number_format($stats['total'] ?? 0) }}</div>
        </div>
        <div class="admin-kpi">
            <div class="admin-kpi-label">ملاحظاتي</div>
            <div class="admin-kpi-value">{{ number_format($stats['mine'] ?? 0) }}</div>
        </div>
        <div class="admin-kpi col-span-2 xl:col-span-1">
            <div class="admin-kpi-label">هذا الشهر</div>
            <div class="admin-kpi-value">{{ number_format($stats['month'] ?? 0) }}</div>
            <div class="admin-kpi-meta up">إضافات جديدة</div>
        </div>
    </div>

    @if($tab === 'stats')
        <section class="admin-card overflow-hidden">
            <div class="border-b border-tremor-border px-5 py-4">
                <h3 class="text-sm font-semibold text-tremor-content-strong">آخر الملاحظات</h3>
            </div>
            <div class="divide-y divide-tremor-border">
                @forelse($notes as $note)
                    <div class="flex items-center justify-between gap-3 px-5 py-3.5">
                        <div class="min-w-0 flex items-center gap-3">
                            <span class="h-2 w-2 rounded-full bg-tremor-brand shrink-0"></span>
                            <div class="min-w-0">
                                <div class="truncate text-sm font-medium text-tremor-content-strong">{{ $note->title }}</div>
                                <div class="text-xs text-tremor-content-subtle">{{ $note->user->name ?? '—' }}</div>
                            </div>
                        </div>
                        <span class="text-xs text-tremor-content-subtle shrink-0">{{ $note->created_at?->diffForHumans() ?? '—' }}</span>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-tremor-content">لا يوجد نشاط بعد.</div>
                @endforelse
            </div>
        </section>
    @elseif($tab === 'mine')
        <div class="admin-card overflow-hidden">
            @if($notes->isEmpty())
                <div class="px-6 py-14 text-center">
                    <h3 class="text-sm font-semibold text-tremor-content-strong">لا توجد ملاحظات شخصية</h3>
                    <a href="{{ route('notes.create') }}" class="admin-btn-brand mt-4 inline-flex">إضافة ملاحظة</a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-tremor-border text-tremor-label text-tremor-content-subtle">
                                <th class="px-4 py-3 text-right font-medium">العنوان</th>
                                <th class="px-4 py-3 text-right font-medium">المحتوى</th>
                                <th class="px-4 py-3 text-right font-medium">التاريخ</th>
                                <th class="px-4 py-3 text-right font-medium">إجراء</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-tremor-border">
                            @foreach($notes as $note)
                                <tr class="hover:bg-tremor-background-muted/80">
                                    <td class="px-4 py-3 text-sm font-semibold text-tremor-content-strong">{{ $note->title }}</td>
                                    <td class="px-4 py-3 text-sm text-tremor-content max-w-md truncate">{{ Str::limit(strip_tags($note->content), 100) }}</td>
                                    <td class="px-4 py-3 text-sm text-tremor-content-subtle whitespace-nowrap">{{ $note->created_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <x-admin.actions>
                                            <x-admin.action :href="route('notes.edit', $note)">تعديل</x-admin.action>
                                            <form action="{{ route('notes.destroy', $note) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <x-admin.action type="submit" tone="danger" confirm="حذف هذه الملاحظة؟">حذف</x-admin.action>
                                            </form>
                                        </x-admin.actions>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-tremor-border px-4 py-3">
                    {{ $notes->links() }}
                </div>
            @endif
        </div>
    @else
        @if($notes->isEmpty())
            <div class="admin-card px-6 py-16 text-center">
                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-tremor-default bg-tremor-brand-faint text-tremor-brand">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h3 class="text-tremor-title font-semibold text-tremor-content-strong">لا توجد ملاحظات بعد</h3>
                <p class="mt-1 text-sm text-tremor-content">ابدأ بإضافة ملاحظة لتنظيم أفكارك.</p>
                <a href="{{ route('notes.create') }}" class="admin-btn-brand mt-5 inline-flex">إضافة ملاحظة</a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                @foreach($notes as $note)
                    <article class="admin-card flex flex-col p-4">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="text-sm font-semibold text-tremor-content-strong line-clamp-2">{{ $note->title }}</h3>
                            @if(auth()->user()->hasRole('admin') || $note->user_id === auth()->id())
                                <x-admin.actions class="shrink-0">
                                    <x-admin.action :href="route('notes.edit', $note)">تعديل</x-admin.action>
                                    <form action="{{ route('notes.destroy', $note) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <x-admin.action type="submit" tone="danger" confirm="حذف هذه الملاحظة؟">حذف</x-admin.action>
                                    </form>
                                </x-admin.actions>
                            @endif
                        </div>

                        <p class="mt-3 line-clamp-4 flex-1 text-sm text-tremor-content">{{ Str::limit(strip_tags($note->content), 180) }}</p>

                        <div class="mt-4 flex items-center justify-between gap-2 border-t border-tremor-border pt-3 text-[11px] text-tremor-content-subtle">
                            <span class="truncate">{{ $note->user->name ?? 'مستخدم محذوف' }}</span>
                            <span class="shrink-0">{{ $note->created_at?->format('d/m/Y') ?? '—' }}</span>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="admin-card px-4 py-3">
                {{ $notes->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
