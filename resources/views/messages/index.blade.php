@extends('layouts.admin')

@section('title', 'الرسائل')
@section('header', 'صندوق الرسائل')

@section('content')
    <div class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4">بدء محادثة جديدة</h2>
                <form method="POST" action="{{ route('messages.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm text-gray-700 mb-1">المستلم</label>
                        <select name="user_id" class="w-full border-gray-300 rounded-lg">
                            @foreach($potentialUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-700 mb-1">عنوان (اختياري)</label>
                        <input name="subject" class="w-full border-gray-300 rounded-lg" />
                    </div>
                    <div>
                        <label class="block text-sm text-gray-700 mb-1">الرسالة</label>
                        <textarea name="message" rows="3" class="w-full border-gray-300 rounded-lg" required></textarea>
                    </div>
                    <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg">إرسال</button>
                </form>
            </div>

            @hasanyrole('admin|coach')
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4">Broadcast للعملاء</h2>
                <form method="POST" action="{{ route('messages.broadcast') }}" class="space-y-3">
                    @csrf
                    <input name="title" class="w-full border-gray-300 rounded-lg" placeholder="عنوان الرسالة (اختياري)">
                    <select name="segment_type" class="w-full border-gray-300 rounded-lg">
                        <option value="all_clients">كل العملاء</option>
                        <option value="coach_clients">عملاء المدرب فقط</option>
                        <option value="inactive_clients">العملاء غير النشطين</option>
                        <option value="membership_expiring">العضويات التي تقترب من الانتهاء</option>
                    </select>
                    <textarea name="body" rows="3" class="w-full border-gray-300 rounded-lg" placeholder="نص الرسالة الجماعية"></textarea>
                    <button class="px-4 py-2 bg-emerald-600 text-white rounded-lg">إرسال البث</button>
                </form>
            </div>
            @endhasanyrole
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">قوالب الرسائل السريعة</h2>
            <form method="POST" action="{{ route('messages.templates.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                @csrf
                <input name="name" class="border-gray-300 rounded-lg" placeholder="اسم القالب" required>
                <input name="category" class="border-gray-300 rounded-lg" placeholder="الفئة (coach/general)">
                <input name="body" class="md:col-span-2 border-gray-300 rounded-lg" placeholder="نص القالب" required>
                <button class="md:col-span-4 px-4 py-2 bg-gray-900 text-white rounded-lg">حفظ القالب</button>
            </form>
            @if($templates->isNotEmpty())
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach($templates as $template)
                        <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-700">{{ $template->name }}</span>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-right text-xs text-gray-500">الموضوع</th>
                        <th class="px-4 py-3 text-right text-xs text-gray-500">الأطراف</th>
                        <th class="px-4 py-3 text-right text-xs text-gray-500">غير مقروء</th>
                        <th class="px-4 py-3 text-right text-xs text-gray-500">آخر تحديث</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($conversations as $conversation)
                        <tr>
                            <td class="px-4 py-3">{{ $conversation->subject ?: 'محادثة مباشرة' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $conversation->participants->pluck('user.name')->implode('، ') }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if(($conversation->unread_count ?? 0) > 0)
                                    <span class="inline-flex min-w-[22px] justify-center px-2 py-1 rounded-full bg-red-100 text-red-800">{{ $conversation->unread_count }}</span>
                                @else
                                    <span class="text-gray-400">0</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ optional($conversation->last_message_at)->diffForHumans() ?: '-' }}</td>
                            <td class="px-4 py-3 text-left">
                                <a href="{{ route('messages.show', $conversation) }}" class="text-indigo-600 hover:text-indigo-800">فتح</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">لا توجد محادثات بعد.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-4">{{ $conversations->links() }}</div>
        </div>
    </div>
@endsection
