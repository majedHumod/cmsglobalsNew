@extends('layouts.admin')

@section('title', 'العملاء')
@section('header', 'إدارة العملاء')

@section('header_actions')
    <a href="{{ route('coach.workspace') }}" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">مساحة العمل</a>
    <a href="{{ route('coach-availabilities.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
        إدارة التوفر الأسبوعي
    </a>
@endsection

@section('content')
@php
    use App\Services\CoachRiskService;
    use App\Services\WorkoutScheduleService;
    $scheduleService = app(WorkoutScheduleService::class);
    $coachRiskService = app(CoachRiskService::class);
@endphp
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white shadow rounded-lg p-5">
            <div class="text-sm text-gray-500">إجمالي العملاء</div>
            <div class="mt-2 text-3xl font-bold text-indigo-600">{{ $summary['clients'] }}</div>
        </div>
        <div class="bg-white shadow rounded-lg p-5">
            <div class="text-sm text-gray-500">معرّضون للانقطاع</div>
            <div class="mt-2 text-3xl font-bold text-red-600">{{ $summary['atRiskCount'] }}</div>
        </div>
        <div class="bg-white shadow rounded-lg p-5">
            <div class="text-sm text-gray-500">بحاجة إلى Check-in</div>
            <div class="mt-2 text-3xl font-bold text-orange-600">{{ $summary['clientsNeedingCheckIn'] }}</div>
        </div>
        <div class="bg-white shadow rounded-lg p-5">
            <div class="text-sm text-gray-500">التزام تمارين &lt; 50%</div>
            <div class="mt-2 text-3xl font-bold text-yellow-600">{{ $summary['clientsLowWorkoutCompliance'] }}</div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6 space-y-4">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('coach.clients.index', request()->only('search', 'coach_id')) }}" class="px-3 py-1.5 rounded-full text-sm {{ empty($filter) ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }}">الكل</a>
            <a href="{{ route('coach.clients.index', array_merge(request()->only('search', 'coach_id'), ['filter' => 'checkin_overdue'])) }}" class="px-3 py-1.5 rounded-full text-sm {{ ($filter ?? '') === 'checkin_overdue' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }}">Check-in متأخر</a>
            <a href="{{ route('coach.clients.index', array_merge(request()->only('search', 'coach_id'), ['filter' => 'low_compliance'])) }}" class="px-3 py-1.5 rounded-full text-sm {{ ($filter ?? '') === 'low_compliance' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }}">التزام منخفض</a>
            <a href="{{ route('coach.clients.index', array_merge(request()->only('search', 'coach_id'), ['filter' => 'low_nutrition'])) }}" class="px-3 py-1.5 rounded-full text-sm {{ ($filter ?? '') === 'low_nutrition' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }}">غذاء منخفض</a>
            <a href="{{ route('coach.clients.index', array_merge(request()->only('search', 'coach_id'), ['filter' => 'expiring'])) }}" class="px-3 py-1.5 rounded-full text-sm {{ ($filter ?? '') === 'expiring' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }}">عضوية تنتهي</a>
        </div>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @if(request('filter'))
                <input type="hidden" name="filter" value="{{ request('filter') }}">
            @endif
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700">بحث</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="اسم العميل أو البريد">
            </div>
            @role('admin')
            <div>
                <label for="coach_id" class="block text-sm font-medium text-gray-700">المدرب</label>
                <select name="coach_id" id="coach_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">كل المدربين</option>
                    @foreach($coaches as $coach)
                        <option value="{{ $coach->id }}" @selected((string) request('coach_id') === (string) $coach->id)>{{ $coach->name }}</option>
                    @endforeach
                </select>
            </div>
            @endrole
            <div class="flex items-end gap-3">
                <button type="submit" class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">تصفية</button>
                <a href="{{ route('coach.clients.index') }}" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">إعادة تعيين</a>
            </div>
        </form>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">العميل</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">المدرب</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">التزام التمارين</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">آخر Check-in</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">الحالة</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($clients as $client)
                        @php
                            $lastCheckIn = $client->progressCheckIns->first();
                            $compliance = $scheduleService->complianceRateForClient($client);
                            $assessment = $coachRiskService->assessClient($client);
                            $badgeClass = $compliance >= 70 ? 'bg-emerald-100 text-emerald-800' : ($compliance >= 50 ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800');
                        @endphp
                        <tr>
                            <td class="px-4 py-4">
                                <div class="font-medium text-gray-900">{{ $client->name }}</div>
                                <div class="text-sm text-gray-500">{{ $client->email }}</div>
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-700">
                                @if(auth()->user()->hasRole('admin'))
                                    <form method="POST" action="{{ route('coach.clients.assignment', $client) }}" class="flex items-center gap-2">
                                        @csrf
                                        @method('PUT')
                                        <select name="coach_id" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="">بدون مدرب</option>
                                            @foreach($coaches as $coach)
                                                <option value="{{ $coach->id }}" @selected((int) $client->coach_id === (int) $coach->id)>{{ $coach->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="px-3 py-2 text-xs font-medium rounded-md bg-gray-100 hover:bg-gray-200">حفظ</button>
                                    </form>
                                @else
                                    {{ $client->coach->name ?? '—' }}
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">{{ $compliance }}%</span>
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-700">
                                {{ $lastCheckIn?->checked_in_at?->diffForHumans() ?? 'لا يوجد' }}
                            </td>
                            <td class="px-4 py-4 text-sm">
                                @if($assessment['risk_score'] > 0)
                                    <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700">يحتاج متابعة</span>
                                @else
                                    <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">مستقر</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-sm font-medium">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('coach.clients.show', $client) }}" class="text-indigo-600 hover:text-indigo-800">الملف</a>
                                    <a href="{{ route('clients.progress.index', $client) }}" class="text-green-600 hover:text-green-800">التقدم</a>
                                    <form method="POST" action="{{ route('coach.clients.remind', $client) }}">
                                        @csrf
                                        <button type="submit" class="text-amber-600 hover:text-amber-800">تذكير</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">لا يوجد عملاء مطابقون للبحث الحالي.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-4 border-t border-gray-200">
            {{ $clients->links() }}
        </div>
    </div>
</div>
@endsection
