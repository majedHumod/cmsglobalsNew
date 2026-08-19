@extends('layouts.admin')

@section('title', 'مركز الإشعارات')
@section('header', 'مركز الإشعارات')

@section('content')
    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow p-4">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <input type="text" name="type" value="{{ request('type') }}" class="border-gray-300 rounded-lg" placeholder="نوع الإشعار">
                <select name="state" class="border-gray-300 rounded-lg">
                    <option value="">الكل</option>
                    <option value="unread" @selected(request('state') === 'unread')>غير مقروء</option>
                    <option value="read" @selected(request('state') === 'read')>مقروء</option>
                </select>
                <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg">تصفية</button>
            </form>
            <form method="POST" action="{{ route('notifications.read-all') }}" class="mt-3">
                @csrf
                <button class="text-sm text-indigo-600 hover:text-indigo-800">تحديد الكل كمقروء</button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow divide-y divide-gray-200">
            @forelse($notifications as $notification)
                <div class="p-4 {{ $notification->read_at ? 'bg-white' : 'bg-indigo-50' }}">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="font-semibold text-gray-800">{{ $notification->title }}</h3>
                            <p class="text-sm text-gray-600 mt-1">{{ $notification->body }}</p>
                            <p class="text-xs text-gray-500 mt-2">{{ $notification->type }} - {{ $notification->created_at?->diffForHumans() }}</p>
                        </div>
                        @if(!$notification->read_at)
                            <form method="POST" action="{{ route('notifications.read', $notification) }}">
                                @csrf
                                <button class="text-xs px-3 py-1 rounded bg-white border border-gray-300">تحديد كمقروء</button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500">لا توجد إشعارات حالياً.</div>
            @endforelse
        </div>
        <div>{{ $notifications->links() }}</div>
    </div>
@endsection
