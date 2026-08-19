@extends('layouts.admin')

@section('title', 'متابعة العادات')
@section('header', 'متابعة العادات')

@section('content')
    <div class="space-y-6">
        @if(auth()->user()->hasAnyRole(['admin', 'coach']))
            <div class="bg-white rounded-lg shadow p-4">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <label class="text-sm text-gray-600 md:self-center">اختيار العميل</label>
                    <select name="client_id" class="border-gray-300 rounded-lg">
                        @foreach(\App\Models\User::query()->clients()->when(auth()->user()->hasRole('coach'), fn ($q) => $q->where('coach_id', auth()->id()))->orderBy('name')->get() as $candidate)
                            <option value="{{ $candidate->id }}" @selected((int)$candidate->id === (int)$client->id)>{{ $candidate->name }}</option>
                        @endforeach
                    </select>
                    <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg">عرض</button>
                </form>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="text-lg font-semibold">العميل: {{ $client->name }}</h2>
            <p class="text-sm text-gray-600 mt-1">نسبة الالتزام الأسبوعي: <span class="font-bold text-indigo-600">{{ $weeklyCompletion }}%</span></p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-4">
                <div class="p-3 rounded bg-indigo-50">
                    <div class="text-xs text-gray-500">Current Streak</div>
                    <div class="font-bold text-indigo-700">{{ $insights['active_streak'] ?? 0 }}</div>
                </div>
                <div class="p-3 rounded bg-emerald-50">
                    <div class="text-xs text-gray-500">Best Streak</div>
                    <div class="font-bold text-emerald-700">{{ $insights['best_streak'] ?? 0 }}</div>
                </div>
                <div class="p-3 rounded bg-amber-50">
                    <div class="text-xs text-gray-500">Missed Days</div>
                    <div class="font-bold text-amber-700">{{ $insights['missed_days'] ?? 0 }}</div>
                </div>
                <div class="p-3 rounded bg-gray-100">
                    <div class="text-xs text-gray-500">Trend</div>
                    <div class="font-bold text-gray-700">{{ $insights['trend'] ?? 'steady' }}</div>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4">
                <div class="p-3 rounded bg-violet-50">
                    <div class="text-xs text-gray-500">Gamification Points</div>
                    <div class="font-bold text-violet-700">{{ $gamification['points'] ?? 0 }}</div>
                </div>
                <div class="p-3 rounded bg-cyan-50">
                    <div class="text-xs text-gray-500">Badges Count</div>
                    <div class="font-bold text-cyan-700">{{ $gamification['badges_count'] ?? 0 }}</div>
                </div>
            </div>
            @if($activeChallenge)
                <div class="mt-3 p-3 rounded bg-amber-50 text-sm">
                    <div class="font-medium text-amber-800">{{ $activeChallenge->title }}</div>
                    <div class="text-amber-700 mt-1">
                        تقدمك: {{ $challengeProgress }} / {{ $activeChallenge->target_value }}
                    </div>
                </div>
            @endif
            @if($badges->isNotEmpty())
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach($badges as $badge)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-gray-100 text-gray-700">
                            🏅 {{ $badge->badge->name ?? 'Badge' }}
                        </span>
                    @endforeach
                </div>
            @endif
        </div>

        @if(auth()->user()->hasAnyRole(['admin', 'coach']))
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="font-semibold mb-3">إضافة عادة جديدة</h3>
                <form method="POST" action="{{ route('habits.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    @csrf
                    <input type="hidden" name="client_user_id" value="{{ $client->id }}">
                    <input name="name" placeholder="اسم العادة" class="border-gray-300 rounded-lg" required>
                    <input name="target_value" type="number" min="1" value="1" class="border-gray-300 rounded-lg">
                    <input name="unit" placeholder="الوحدة (اختياري)" class="border-gray-300 rounded-lg">
                    <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg">إضافة</button>
                </form>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-right text-xs text-gray-500">العادة</th>
                        <th class="px-4 py-3 text-right text-xs text-gray-500">الهدف</th>
                        <th class="px-4 py-3 text-right text-xs text-gray-500">إنجاز 7 أيام</th>
                        <th class="px-4 py-3 text-right text-xs text-gray-500">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($habits as $habit)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ $habit->name }}</div>
                                <div class="text-xs text-gray-500">{{ $habit->is_active ? 'نشطة' : 'متوقفة' }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $habit->target_value }} {{ $habit->unit }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $habit->logs->where('is_completed', true)->count() }} / 7</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <form method="POST" action="{{ route('habits.log', $habit) }}">
                                        @csrf
                                        <input type="hidden" name="logged_on" value="{{ now()->toDateString() }}">
                                        <input type="hidden" name="value" value="{{ $habit->target_value }}">
                                        <input type="hidden" name="is_completed" value="1">
                                        <button class="text-xs px-3 py-1 rounded bg-green-100 text-green-800">تسجيل اليوم</button>
                                    </form>
                                    @if(auth()->user()->hasAnyRole(['admin', 'coach']))
                                        <form method="POST" action="{{ route('habits.toggle', $habit) }}">
                                            @csrf
                                            <button class="text-xs px-3 py-1 rounded bg-gray-100 text-gray-800">تفعيل/إيقاف</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">لا توجد عادات مضافة حتى الآن.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
