@extends('layouts.client')

@section('title', 'الإشعارات')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-lg font-semibold text-slate-900">الإشعارات</h1>
        <form method="POST" action="{{ route('client.notifications.read-all') }}">
            @csrf
            <button type="submit" class="text-sm text-indigo-600">تحديد الكل كمقروء</button>
        </form>
    </div>

    <div class="flex gap-2 text-sm">
        <a href="{{ route('client.notifications.index') }}" class="px-3 py-1 rounded-full {{ !request('state') ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600' }}">الكل</a>
        <a href="{{ route('client.notifications.index', ['state' => 'unread']) }}" class="px-3 py-1 rounded-full {{ request('state') === 'unread' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600' }}">غير مقروء</a>
    </div>

    <div class="space-y-2">
        @forelse($notifications as $notification)
            <div class="rounded-2xl bg-white p-4 shadow-sm {{ $notification->read_at ? '' : 'ring-1 ring-indigo-200' }}">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="font-medium text-slate-900">{{ $notification->title }}</div>
                        @if($notification->body)
                            <p class="text-sm text-slate-600 mt-1">{{ $notification->body }}</p>
                        @endif
                        <p class="text-xs text-slate-400 mt-2">{{ $notification->created_at?->diffForHumans() }}</p>
                    </div>
                    @unless($notification->read_at)
                        <form method="POST" action="{{ route('client.notifications.read', $notification) }}">
                            @csrf
                            <button type="submit" class="text-xs px-2 py-1 rounded-lg bg-slate-100 text-slate-600">مقروء</button>
                        </form>
                    @endunless
                </div>
            </div>
        @empty
            <div class="rounded-2xl bg-white p-8 text-center text-slate-500 shadow-sm">لا توجد إشعارات حالياً.</div>
        @endforelse
    </div>

    <div>{{ $notifications->links() }}</div>
</div>
@endsection
