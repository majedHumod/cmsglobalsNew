@extends('layouts.client')

@section('title', 'المحادثة')

@section('content')
<div class="space-y-4 flex flex-col min-h-[70vh]">
    @php
        $other = $conversation->participants->first(fn ($p) => (int) $p->user_id !== (int) auth()->id());
    @endphp
    <div class="rounded-2xl bg-white px-4 py-3 shadow-sm">
        <a href="{{ route('client.messages.index') }}" class="text-sm text-indigo-600">← الرجوع</a>
        <h1 class="font-semibold text-slate-900 mt-1">{{ optional($other?->user)->name ?? 'محادثة' }}</h1>
    </div>

    <div class="flex-1 space-y-3">
        @foreach($conversation->messages->sortBy('sent_at') as $message)
            <div class="flex {{ (int) $message->sender_user_id === (int) auth()->id() ? 'justify-start' : 'justify-end' }}">
                <div class="max-w-[85%] rounded-2xl px-4 py-3 text-sm {{ (int) $message->sender_user_id === (int) auth()->id() ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-200 text-slate-800' }}">
                    <div>{{ $message->body }}</div>
                    <div class="text-[10px] opacity-70 mt-1">{{ optional($message->sent_at)->format('H:i') }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <form method="POST" action="{{ route('client.messages.send', $conversation) }}" class="rounded-2xl bg-white p-3 shadow-sm sticky bottom-20">
        @csrf
        <div class="flex gap-2">
            <input type="text" name="message" class="flex-1 rounded-xl border-slate-300 text-sm" placeholder="اكتب ردك..." required>
            <button type="submit" class="bg-indigo-600 text-white px-4 rounded-xl text-sm font-medium">إرسال</button>
        </div>
    </form>
</div>
@endsection
