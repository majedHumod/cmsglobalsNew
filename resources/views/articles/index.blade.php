@extends('layouts.admin')

@section('title', 'إدارة المقالات')

@section('header', 'إدارة المقالات')

@section('header_actions')
    <x-admin.button :href="route('articles.create')" variant="primary">إضافة مقال جديد</x-admin.button>
@endsection

@section('content')
<x-admin.card>
    <x-admin.section-heading
        title="قائمة المقالات"
        description="إدارة وتنظيم مقالات الموقع من هنا."
    />

    @if($articles->isEmpty())
        <x-admin.empty-state
            title="لا توجد مقالات"
            description="ابدأ بإنشاء مقال جديد لموقعك."
        >
            <x-slot:actions>
                <x-admin.button :href="route('articles.create')" variant="primary">إضافة مقال جديد</x-admin.button>
            </x-slot:actions>
        </x-admin.empty-state>
    @else
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
            @foreach($articles as $article)
                <article class="overflow-hidden rounded-tremor-default border border-tremor-border bg-white shadow-tremor-card">
                    @if($article->image)
                        <img src="{{ Storage::url($article->image) }}" alt="{{ $article->title }}" class="h-44 w-full object-cover">
                    @else
                        <div class="flex h-44 w-full items-center justify-center bg-tremor-background-muted text-tremor-content-subtle">
                            <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    @endif

                    <div class="p-4">
                        <div class="mb-3 flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="line-clamp-2 text-base font-semibold text-tremor-content-strong">{{ $article->title }}</h3>
                                <div class="mt-2">
                                    <x-admin.badge :tone="$article->is_published ? 'success' : 'warning'">
                                        {{ $article->is_published ? 'منشور' : 'مسودة' }}
                                    </x-admin.badge>
                                </div>
                            </div>
                        </div>

                        <p class="mb-4 line-clamp-3 text-sm text-tremor-content">{{ Str::limit(strip_tags($article->content), 150) }}</p>

                        <div class="mb-4 flex items-center justify-between gap-2 text-xs text-tremor-content-subtle">
                            <span class="truncate">{{ $article->user->name }}</span>
                            <span class="shrink-0">{{ $article->created_at->format('d/m/Y') }}</span>
                        </div>

                        <x-admin.actions align="start">
                            @if($article->is_published)
                                <x-admin.action :href="route('articles.public.show', $article)" target="_blank" rel="noopener noreferrer">عرض</x-admin.action>
                            @endif
                            <x-admin.action :href="route('articles.edit', $article)">تعديل</x-admin.action>
                            <form action="{{ route('articles.destroy', $article) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <x-admin.action type="submit" tone="danger" confirm="هل أنت متأكد من حذف هذا المقال؟">حذف</x-admin.action>
                            </form>
                        </x-admin.actions>

                        @unless($article->is_published)
                            <x-admin.alert type="warning" class="mb-0 mt-4">
                                يجب نشر المقالة أولاً قبل عرضها في الموقع.
                            </x-admin.alert>
                        @endunless
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</x-admin.card>
@endsection
