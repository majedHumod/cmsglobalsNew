@extends('layouts.client')

@section('title', 'المجتمع')

@section('content')
<div class="space-y-4">
    <section class="rounded-2xl bg-white p-4 shadow-sm">
        <h2 class="font-semibold text-slate-900 mb-3">شارك تقدّمك</h2>
        <form method="POST" action="{{ route('client.community.store') }}" class="space-y-3">
            @csrf
            <textarea name="content" rows="3" class="w-full rounded-xl border-slate-300 text-sm" placeholder="اكتب منشوراً..." required></textarea>
            <button type="submit" class="w-full bg-indigo-600 text-white rounded-xl py-2.5 text-sm font-medium">نشر</button>
        </form>
    </section>

    @forelse($posts as $post)
        <article class="rounded-2xl bg-white p-4 shadow-sm border border-slate-100">
            <div class="flex items-center justify-between gap-2">
                <div class="font-medium text-slate-900">{{ $post->user->name ?? 'عضو' }}</div>
                <div class="text-xs text-slate-400">{{ $post->created_at->diffForHumans() }}</div>
            </div>
            <p class="text-sm text-slate-700 mt-2 whitespace-pre-line">{{ $post->content }}</p>
            <div class="flex items-center gap-3 mt-3 text-xs">
                <form method="POST" action="{{ route('client.community.react', $post) }}">
                    @csrf
                    <button type="submit" class="text-rose-600">❤️ {{ $post->reactions->count() }}</button>
                </form>
                <span class="text-slate-500">{{ $post->comments->count() }} تعليق</span>
            </div>
            @foreach($post->comments->take(3) as $comment)
                <div class="mt-2 text-xs bg-slate-50 rounded-lg px-3 py-2">
                    <span class="font-medium">{{ $comment->user->name ?? 'عضو' }}:</span>
                    {{ $comment->content }}
                </div>
            @endforeach
            <form method="POST" action="{{ route('client.community.comment', $post) }}" class="mt-3 flex gap-2">
                @csrf
                <input type="text" name="content" class="flex-1 rounded-xl border-slate-300 text-xs" placeholder="أضف تعليقاً..." required>
                <button type="submit" class="text-xs text-indigo-600 px-2">إرسال</button>
            </form>
        </article>
    @empty
        <div class="rounded-2xl bg-white p-6 text-center text-slate-500 text-sm">لا توجد منشورات بعد. كن أول من يشارك!</div>
    @endforelse

    {{ $posts->links() }}
</div>
@endsection
