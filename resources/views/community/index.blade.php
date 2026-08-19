@extends('layouts.admin')

@section('title', 'المجتمع')
@section('header', 'Community Feed')

@section('content')
    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow p-4">
            <form method="POST" action="{{ route('community.store') }}" class="space-y-3">
                @csrf
                <label class="block text-sm text-gray-700">منشور جديد</label>
                <textarea name="content" rows="3" class="w-full border-gray-300 rounded-lg" placeholder="شارك تقدمك أو سؤالًا للمجتمع..." required></textarea>
                <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg">نشر</button>
            </form>
        </div>

        <div class="space-y-4">
            @forelse($posts as $post)
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center justify-between">
                        <div class="font-medium">{{ $post->user->name ?? 'عضو' }}</div>
                        <div class="text-xs text-gray-500">{{ $post->created_at?->diffForHumans() }}</div>
                    </div>
                    <p class="mt-3 text-gray-800 whitespace-pre-wrap">{{ $post->content }}</p>

                    <div class="mt-3 flex items-center gap-3">
                        <form method="POST" action="{{ route('community.react', $post) }}">
                            @csrf
                            <input type="hidden" name="reaction" value="like">
                            <button class="text-sm px-3 py-1 rounded bg-indigo-50 text-indigo-700">👍 إعجاب ({{ $post->reactions->count() }})</button>
                        </form>
                    </div>

                    <div class="mt-3 space-y-2">
                        @foreach($post->comments as $comment)
                            <div class="p-2 rounded bg-gray-50 text-sm">
                                <span class="font-medium">{{ $comment->user->name ?? 'عضو' }}:</span>
                                <span>{{ $comment->content }}</span>
                            </div>
                        @endforeach
                    </div>

                    <form method="POST" action="{{ route('community.comment', $post) }}" class="mt-3 flex gap-2">
                        @csrf
                        <input name="content" class="flex-1 border-gray-300 rounded-lg" placeholder="اكتب تعليقًا..." required>
                        <button class="px-3 py-2 bg-gray-900 text-white rounded-lg">تعليق</button>
                    </form>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">لا توجد منشورات بعد.</div>
            @endforelse
        </div>

        <div>{{ $posts->links() }}</div>
    </div>
@endsection
