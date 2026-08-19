@extends('layouts.client')

@section('title', 'الرسائل')

@section('content')
<div class="space-y-4">
    @if($coach)
        <section class="rounded-2xl bg-white p-4 shadow-sm">
            <h2 class="font-semibold text-slate-900 mb-3">رسالة سريعة لمدربك</h2>
            <form method="POST" action="{{ route('client.messages.store') }}" class="space-y-3">
                @csrf
                <textarea name="message" rows="3" class="w-full rounded-xl border-slate-300 text-sm" placeholder="اكتب رسالتك..." required></textarea>
                <button type="submit" class="w-full bg-indigo-600 text-white rounded-xl py-2.5 text-sm font-medium">إرسال</button>
            </form>
        </section>
    @else
        <div class="rounded-2xl bg-amber-50 text-amber-800 p-4 text-sm">لم يُعيَّن مدرب لك بعد.</div>
    @endif

    <section class="space-y-2">
        <h2 class="font-semibold text-slate-900">المحادثات</h2>
        @forelse($conversations as $conversation)
            @php
                $other = $conversation->participants->first(fn ($p) => (int) $p->user_id !== (int) auth()->id());
                $lastMessage = $conversation->messages->first();
            @endphp
            <a href="{{ route('client.messages.show', $conversation) }}" class="block rounded-2xl bg-white p-4 shadow-sm border border-slate-100">
                <div class="font-medium text-slate-900">{{ optional($other?->user)->name ?? 'محادثة' }}</div>
                <p class="text-sm text-slate-500 mt-1 line-clamp-2">{{ $lastMessage?->body ?? 'لا توجد رسائل' }}</p>
                <div class="text-xs text-slate-400 mt-2">{{ optional($conversation->last_message_at)->diffForHumans() }}</div>
            </a>
        @empty
            <div class="rounded-2xl bg-white p-6 text-center text-slate-500 text-sm">لا توجد محادثات بعد.</div>
        @endforelse
    </section>

    {{ $conversations->links() }}
</div>
@endsection
