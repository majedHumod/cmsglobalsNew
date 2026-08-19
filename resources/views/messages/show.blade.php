@extends('layouts.admin')

@section('title', 'تفاصيل المحادثة')
@section('header', 'تفاصيل المحادثة')

@section('content')
    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="font-semibold">{{ $conversation->subject ?: 'محادثة مباشرة' }}</h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $conversation->participants->pluck('user.name')->implode('، ') }}
            </p>
        </div>

        <div class="bg-white rounded-lg shadow p-4 space-y-3 max-h-[480px] overflow-y-auto">
            @forelse($conversation->messages as $message)
                <div class="p-3 rounded-lg {{ (int)$message->sender_user_id === (int)auth()->id() ? 'bg-indigo-50 border border-indigo-100' : 'bg-gray-50 border border-gray-100' }}">
                    <div class="text-xs text-gray-500">{{ $message->sender->name }} - {{ $message->sent_at?->format('Y-m-d H:i') }}</div>
                    <div class="mt-1 text-sm text-gray-800 whitespace-pre-wrap">{{ $message->body }}</div>
                </div>
            @empty
                <div class="text-gray-500 text-sm">لا توجد رسائل بعد.</div>
            @endforelse
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <form method="POST" action="{{ route('messages.send', $conversation) }}" class="space-y-3">
                @csrf
                @if($templates->isNotEmpty())
                    <div>
                        <label class="block text-sm text-gray-700 mb-1">إدراج قالب سريع</label>
                        <select id="quick-template" class="w-full border-gray-300 rounded-lg">
                            <option value="">اختر قالبًا...</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->body }}">{{ $template->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <textarea name="message" rows="3" class="w-full border-gray-300 rounded-lg" required placeholder="اكتب رسالتك..."></textarea>
                <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg">إرسال</button>
            </form>
        </div>
    </div>
    <script>
        const quickTemplate = document.getElementById('quick-template');
        if (quickTemplate) {
            quickTemplate.addEventListener('change', function () {
                const messageField = document.querySelector('textarea[name="message"]');
                if (messageField && this.value) {
                    messageField.value = this.value;
                }
            });
        }
    </script>
@endsection
