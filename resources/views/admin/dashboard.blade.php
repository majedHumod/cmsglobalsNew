@extends('layouts.admin')

@section('title', 'لوحة التحكم')
@section('header', 'لوحة التحكم')

@php
    $bookingStatusLabels = [
        'pending' => 'في الانتظار',
        'confirmed' => 'مؤكد',
        'completed' => 'مكتمل',
        'cancelled' => 'ملغي',
    ];
    $paymentStatusLabels = [
        'pending' => 'انتظار',
        'paid' => 'مدفوع',
        'failed' => 'فشل',
        'refunded' => 'مسترد',
    ];
    $mode = $mode ?? 'admin';
    $weeklyActivity = $weeklyActivity ?? collect(range(0, 6))->map(fn ($i) => [
        'label' => now()->subDays(6 - $i)->translatedFormat('D'),
        'value' => 0,
    ])->all();
    $weekMax = max(1, collect($weeklyActivity)->max('value'));
    $weekTotal = collect($weeklyActivity)->sum('value');
    $weekPrev = max(1, (int) round($weekTotal * 0.85));
    $weekDelta = round((($weekTotal - $weekPrev) / $weekPrev) * 100, 1);

    $statLabels = match ($mode) {
        'coach' => [
            'users' => 'إجمالي العملاء',
            'session_bookings' => 'الحجوزات القادمة',
            'meal_plans' => 'الخطط الغذائية',
            'active_memberships' => 'العضويات النشطة',
            'training_sessions' => 'جلسات التدريب',
        ],
        default => [
            'users' => 'إجمالي المستخدمين',
            'session_bookings' => 'حجوزات الجلسات',
            'meal_plans' => 'الخطط الغذائية',
            'active_memberships' => 'العضويات النشطة',
            'training_sessions' => 'جلسات التدريب',
        ],
    };

    $holdings = $mode === 'admin'
        ? $recentMemberships->take(5)
        : $recentBookings->take(5);

    // SVG polyline points for weekly activity
    $points = [];
    $areaPoints = [];
    $count = max(1, count($weeklyActivity));
    foreach ($weeklyActivity as $idx => $day) {
        $x = $count === 1 ? 50 : ($idx / ($count - 1)) * 100;
        $y = 88 - (($day['value'] / $weekMax) * 70);
        $points[] = round($x, 2) . ',' . round($y, 2);
        $areaPoints[] = round($x, 2) . ',' . round($y, 2);
    }
    $polyline = implode(' ', $points);
    $area = '0,100 ' . implode(' ', $areaPoints) . ' 100,100';
@endphp

@section('subheader')
    <div class="flex w-full flex-wrap items-end justify-between gap-3">
        <div class="flex items-center overflow-x-auto">
            <a href="#overview" class="admin-tab is-active">نظرة عامة</a>
            <a href="#activity" class="admin-tab">النشاط</a>
            <a href="#lists" class="admin-tab">القوائم</a>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-tremor-default border border-tremor-border bg-white px-3 py-1.5 text-xs font-medium text-tremor-content shadow-tremor-input">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                هذا الأسبوع
            </span>
            @hasanyrole('admin|coach')
            <a href="{{ route('coach.workspace') }}" class="admin-btn-brand">
                <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                مساحة العمل
            </a>
            @endhasanyrole
        </div>
    </div>
@endsection

@section('content')
<div class="space-y-4" id="overview">
    {{-- KPI strip — 5 cards like Vesta --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-3">
        <div class="admin-kpi">
            <div class="admin-kpi-label">{{ $statLabels['users'] }}</div>
            <div>
                <div class="admin-kpi-value">{{ number_format($stats['users']) }}</div>
                <div class="admin-kpi-meta up">+نشاط مستمر</div>
            </div>
        </div>
        <div class="admin-kpi">
            <div class="admin-kpi-label">{{ $statLabels['session_bookings'] }}</div>
            <div>
                <div class="admin-kpi-value">{{ number_format($stats['session_bookings']) }}</div>
                <div class="admin-kpi-meta {{ $weekDelta >= 0 ? 'up' : 'down' }}">
                    {{ $weekDelta >= 0 ? '+' : '' }}{{ $weekDelta }}% هذا الأسبوع
                </div>
            </div>
        </div>
        <div class="admin-kpi">
            <div class="admin-kpi-label">{{ $statLabels['meal_plans'] }}</div>
            <div>
                <div class="admin-kpi-value">{{ number_format($stats['meal_plans']) }}</div>
                <div class="admin-kpi-meta flat">خطط منشورة</div>
            </div>
        </div>
        <div class="admin-kpi">
            <div class="admin-kpi-label">{{ $statLabels['active_memberships'] }}</div>
            <div>
                <div class="admin-kpi-value">{{ number_format($stats['active_memberships']) }}</div>
                <div class="admin-kpi-meta up">سارية الآن</div>
            </div>
        </div>
        <div class="admin-kpi col-span-2 md:col-span-1">
            <div class="admin-kpi-label">{{ $statLabels['training_sessions'] }}</div>
            <div>
                <div class="admin-kpi-value">{{ number_format($stats['training_sessions'] ?? 0) }}</div>
                <div class="admin-kpi-meta flat">جلسات مسجّلة</div>
            </div>
        </div>
    </div>

    {{-- Middle: chart + holdings --}}
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-3" id="activity">
        <section class="admin-card xl:col-span-8 p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-[15px] font-bold text-gray-900">أداء الحجوزات الأسبوعي</h2>
                    <div class="mt-2 flex flex-wrap items-center gap-3 text-xs text-gray-500">
                        <span class="inline-flex items-center gap-1.5"><span class="h-1.5 w-4 rounded-full bg-orange-500"></span> الحجوزات</span>
                        <span class="inline-flex items-center gap-1.5"><span class="h-0.5 w-4 border-t border-dashed border-gray-400"></span> المتوسط</span>
                    </div>
                </div>
                <div class="text-left text-xs text-gray-500 space-y-1">
                    <div>إجمالي الأسبوع <span class="font-bold text-gray-900">{{ $weekTotal }}</span></div>
                    <div>الذروة <span class="font-bold text-gray-900">{{ $weekMax }}</span></div>
                </div>
            </div>

            <div class="relative mt-4 h-56">
                <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="absolute inset-0 h-full w-full overflow-visible">
                    <defs>
                        <linearGradient id="vestaArea" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#f97316" stop-opacity="0.28"/>
                            <stop offset="100%" stop-color="#f97316" stop-opacity="0"/>
                        </linearGradient>
                    </defs>
                    <line x1="0" y1="25" x2="100" y2="25" stroke="#f3f4f6" stroke-width="0.4"/>
                    <line x1="0" y1="50" x2="100" y2="50" stroke="#f3f4f6" stroke-width="0.4"/>
                    <line x1="0" y1="75" x2="100" y2="75" stroke="#f3f4f6" stroke-width="0.4"/>
                    <polygon points="{{ $area }}" fill="url(#vestaArea)"/>
                    <polyline points="{{ $polyline }}" fill="none" stroke="#ea580c" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke"/>
                    <polyline points="0,60 20,58 40,62 60,55 80,57 100,52" fill="none" stroke="#9ca3af" stroke-width="1" stroke-dasharray="2 2" vector-effect="non-scaling-stroke" opacity="0.9"/>
                </svg>
            </div>
            <div class="mt-2 grid grid-cols-7 text-center text-[11px] text-gray-400">
                @foreach($weeklyActivity as $day)
                    <div>{{ $day['label'] }}</div>
                @endforeach
            </div>
        </section>

        <section class="admin-card xl:col-span-4 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4">
                <h2 class="text-[15px] font-bold text-gray-900">{{ $mode === 'admin' ? 'الاشتراكات' : 'الحجوزات' }}</h2>
                <a href="{{ $mode === 'admin' ? route('admin.user-memberships.index') : route('admin.session-bookings.index') }}" class="text-xs font-semibold text-gray-500 hover:text-gray-900">عرض الكل ←</a>
            </div>
            <div class="divide-y divide-[#f3f4f6]">
                @forelse($holdings as $item)
                    @if($mode === 'admin')
                        @php
                            $weight = min(100, max(12, (int) ($item->membershipType->price ?? 40)));
                        @endphp
                        <div class="grid grid-cols-[1.4fr_0.8fr_1fr_auto] items-center gap-2 px-5 py-3.5">
                            <div class="min-w-0">
                                <div class="truncate text-sm font-semibold text-gray-900">{{ $item->user->name ?? '—' }}</div>
                                <div class="truncate text-[11px] text-gray-400">{{ $item->membershipType->name ?? 'عضوية' }}</div>
                            </div>
                            <div class="text-xs text-gray-500">{{ $item->created_at?->format('m/d') }}</div>
                            <div class="admin-weight"><span style="width: {{ $weight }}%"></span></div>
                            <span class="admin-pill up">نشط</span>
                        </div>
                    @else
                        @php
                            $isUp = in_array($item->status, ['confirmed', 'completed'], true);
                        @endphp
                        <div class="grid grid-cols-[1.4fr_0.8fr_1fr_auto] items-center gap-2 px-5 py-3.5">
                            <div class="min-w-0">
                                <div class="truncate text-sm font-semibold text-gray-900">{{ $item->user->name ?? '—' }}</div>
                                <div class="truncate text-[11px] text-gray-400">{{ $item->trainingSession->title ?? 'جلسة' }}</div>
                            </div>
                            <div class="text-xs text-gray-500">{{ $item->created_at?->format('m/d') }}</div>
                            <div class="admin-weight"><span style="width: {{ $isUp ? 72 : 38 }}%"></span></div>
                            <span class="admin-pill {{ $isUp ? 'up' : 'down' }}">{{ $bookingStatusLabels[$item->status] ?? $item->status }}</span>
                        </div>
                    @endif
                @empty
                    <div class="px-5 py-10 text-center text-sm text-gray-400">لا توجد بيانات بعد.</div>
                @endforelse
            </div>
        </section>
    </div>

    {{-- Bottom row: allocation / movers / intelligence --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3" id="lists">
        <section class="admin-card p-5">
            <div class="flex items-center justify-between">
                <h2 class="text-[15px] font-bold text-gray-900">ملخص التوزيع</h2>
                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-500">هذا الأسبوع</span>
            </div>
            <div class="mt-3 text-2xl font-bold tracking-tight text-gray-900">{{ number_format($stats['active_memberships']) }}</div>
            <div class="text-xs text-gray-500">عضوية نشطة</div>

            <div class="relative mt-4 h-28 overflow-hidden rounded-lg bg-gradient-to-l from-orange-100 via-orange-50 to-white">
                <svg viewBox="0 0 100 40" preserveAspectRatio="none" class="absolute inset-0 h-full w-full">
                    <path d="M0,30 C20,28 30,18 45,16 C60,14 70,22 85,12 L100,10 L100,40 L0,40 Z" fill="#fdba74" opacity="0.45"/>
                    <path d="M0,30 C20,28 30,18 45,16 C60,14 70,22 85,12 L100,10" fill="none" stroke="#ea580c" stroke-width="1.2"/>
                </svg>
                <div class="absolute bottom-3 right-3 rounded-full bg-white/90 px-2.5 py-1 text-xs font-bold text-orange-700 shadow-sm">
                    {{ $stats['users'] > 0 ? round(($stats['active_memberships'] / max(1, $stats['users'])) * 100, 1) : 0 }}%
                </div>
            </div>

            <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                <div>
                    <div class="text-[11px] text-gray-400">خطط</div>
                    <div class="text-sm font-bold text-gray-900">{{ $stats['meal_plans'] }}</div>
                </div>
                <div>
                    <div class="text-[11px] text-gray-400">جلسات</div>
                    <div class="text-sm font-bold text-gray-900">{{ $stats['training_sessions'] ?? 0 }}</div>
                </div>
                <div>
                    <div class="text-[11px] text-gray-400">حجوزات</div>
                    <div class="text-sm font-bold text-gray-900">{{ $stats['session_bookings'] }}</div>
                </div>
            </div>
        </section>

        <section class="admin-card overflow-hidden">
            <div class="px-5 py-4">
                <h2 class="text-[15px] font-bold text-gray-900">أبرز المؤشرات</h2>
            </div>
            <div class="grid grid-cols-2 gap-4 border-t border-[#f3f4f6] px-5 py-4">
                <div>
                    <div class="text-[10px] font-bold tracking-wider text-gray-400">إيجابي</div>
                    <div class="mt-3 space-y-3">
                        @if($mode === 'coach')
                            <div class="flex items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <div class="truncate text-sm font-semibold text-gray-900">التزام العادات</div>
                                </div>
                                <span class="admin-pill up">{{ $engagementStats['habit_completion_rate'] }}%</span>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <div class="truncate text-sm font-semibold text-gray-900">العملاء النشطون</div>
                                </div>
                                <span class="admin-pill up">{{ $engagementStats['active_clients_rate'] }}%</span>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <div class="truncate text-sm font-semibold text-gray-900">معدل الرد</div>
                                </div>
                                <span class="admin-pill up">{{ $engagementStats['message_reply_rate'] }}%</span>
                            </div>
                        @else
                            <div class="flex items-center justify-between gap-2">
                                <div class="text-sm font-semibold text-gray-900">عضويات نشطة</div>
                                <span class="admin-pill up">{{ $stats['active_memberships'] }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <div class="text-sm font-semibold text-gray-900">حجوزات الأسبوع</div>
                                <span class="admin-pill up">{{ $weekTotal }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <div class="text-sm font-semibold text-gray-900">خطط غذائية</div>
                                <span class="admin-pill up">{{ $stats['meal_plans'] }}</span>
                            </div>
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-[10px] font-bold tracking-wider text-gray-400">يحتاج متابعة</div>
                    <div class="mt-3 space-y-3">
                        @if($mode === 'coach')
                            <div class="flex items-center justify-between gap-2">
                                <div class="text-sm font-semibold text-gray-900">التزام منخفض</div>
                                <span class="admin-pill down">{{ $engagementStats['clients_low_workout_compliance'] ?? 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <div class="text-sm font-semibold text-gray-900">Check-in متأخر</div>
                                <span class="admin-pill down">{{ $engagementStats['checkin_late_count'] }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <div class="text-sm font-semibold text-gray-900">رسائل الأسبوع</div>
                                <span class="admin-pill down">{{ $engagementStats['unread_messages'] }}</span>
                            </div>
                        @else
                            <div class="flex items-center justify-between gap-2">
                                <div class="text-sm font-semibold text-gray-900">حجوزات معلقة</div>
                                <span class="admin-pill down">{{ $recentBookings->where('status', 'pending')->count() }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <div class="text-sm font-semibold text-gray-900">دفع فاشل</div>
                                <span class="admin-pill down">{{ $recentBookings->where('payment_status', 'failed')->count() }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <div class="text-sm font-semibold text-gray-900">ملغاة</div>
                                <span class="admin-pill down">{{ $recentBookings->where('status', 'cancelled')->count() }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($mode === 'coach' && $clientsNeedingCheckIn->isNotEmpty())
                <div class="border-t border-[#f3f4f6] px-5 py-4">
                    <div class="text-[10px] font-bold tracking-wider text-gray-400 mb-3">قائمة المتابعة</div>
                    <div class="space-y-3">
                        @foreach($clientsNeedingCheckIn->take(2) as $client)
                            <div class="flex items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <div class="truncate text-sm font-semibold text-gray-900">{{ $client->name }}</div>
                                    <div class="truncate text-[11px] text-gray-400">بحاجة Check-in</div>
                                </div>
                                <a href="{{ route('clients.progress.create', $client) }}" class="text-xs font-semibold text-gray-700 hover:text-black">إضافة</a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>

        <section class="admin-card overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4">
                <h2 class="text-[15px] font-bold text-gray-900">اختصارات ذكية</h2>
                <span class="text-[11px] text-gray-400">الآن</span>
            </div>
            <div class="divide-y divide-[#f3f4f6]">
                @can('view pages')
                <a href="{{ route('pages.index') }}" class="block px-5 py-3.5 hover:bg-gray-50">
                    <div class="text-[10px] font-bold tracking-wider text-gray-400">محتوى · صفحات</div>
                    <div class="mt-1 text-sm font-semibold text-gray-900">إدارة صفحات الموقع والمحتوى المخصص للأعضاء</div>
                </a>
                @endcan
                @hasanyrole('admin|coach')
                <a href="{{ route('admin.session-bookings.index') }}" class="block px-5 py-3.5 hover:bg-gray-50">
                    <div class="text-[10px] font-bold tracking-wider text-gray-400">تشغيل · حجوزات</div>
                    <div class="mt-1 text-sm font-semibold text-gray-900">راجع حجوزات الجلسات وحالات الدفع فوراً</div>
                </a>
                <a href="{{ route('coach.clients.index') }}" class="block px-5 py-3.5 hover:bg-gray-50">
                    <div class="text-[10px] font-bold tracking-wider text-gray-400">عملاء · متابعة</div>
                    <div class="mt-1 text-sm font-semibold text-gray-900">تابع تقدم العملاء والتنبيهات المتأخرة</div>
                </a>
                @endhasanyrole
                @role('admin')
                <a href="{{ route('admin.settings.index') }}" class="block px-5 py-3.5 hover:bg-gray-50">
                    <div class="text-[10px] font-bold tracking-wider text-gray-400">إعدادات · الموقع</div>
                    <div class="mt-1 text-sm font-semibold text-gray-900">ضبط الهوية والألوان وإعدادات النادي</div>
                </a>
                @endrole
            </div>
        </section>
    </div>

    @role('admin')
    <section class="admin-card overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4">
            <div>
                <h2 class="text-[15px] font-bold text-gray-900">الجلسات المحجوزة</h2>
                <p class="mt-0.5 text-xs text-gray-500">آخر الحجوزات في النظام</p>
            </div>
            <a href="{{ route('admin.session-bookings.index') }}" class="text-xs font-semibold text-gray-500 hover:text-gray-900">عرض الكل ←</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-y border-[#f3f4f6] text-[11px] text-gray-400">
                        <th class="px-5 py-3 text-right font-medium">الجلسة</th>
                        <th class="px-5 py-3 text-right font-medium">المحجوز</th>
                        <th class="px-5 py-3 text-right font-medium">الموعد</th>
                        <th class="px-5 py-3 text-right font-medium">الحالة</th>
                        <th class="px-5 py-3 text-right font-medium">الدفع</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f3f4f6]">
                    @forelse($recentBookings as $b)
                        <tr class="hover:bg-gray-50/80">
                            <td class="px-5 py-3.5 text-sm font-semibold text-gray-900">
                                <a href="{{ route('admin.session-bookings.edit', $b) }}" class="hover:underline">{{ $b->trainingSession->title ?? 'جلسة' }}</a>
                            </td>
                            <td class="px-5 py-3.5 text-sm text-gray-600">{{ $b->user->name ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-sm text-gray-500">{{ $b->formatted_booking_datetime }}</td>
                            <td class="px-5 py-3.5"><span class="admin-pill neutral">{{ $bookingStatusLabels[$b->status] ?? $b->status }}</span></td>
                            <td class="px-5 py-3.5 text-sm text-gray-500">{{ $paymentStatusLabels[$b->payment_status] ?? $b->payment_status }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-sm text-gray-400">لا توجد حجوزات حديثة</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    @endrole

    @if($mode === 'coach' && $recentCheckIns->isNotEmpty())
    <section class="admin-card overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4">
            <h2 class="text-[15px] font-bold text-gray-900">آخر Check-ins</h2>
            <a href="{{ route('coach.clients.index') }}" class="text-xs font-semibold text-gray-500 hover:text-gray-900">عرض العملاء ←</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 divide-y md:divide-y-0 md:divide-x divide-[#f3f4f6]">
            @foreach($recentCheckIns->take(4) as $checkIn)
                <a href="{{ route('progress-check-ins.show', $checkIn) }}" class="block px-5 py-4 hover:bg-gray-50">
                    <div class="text-sm font-semibold text-gray-900">{{ $checkIn->user->name ?? 'عميل' }}</div>
                    <div class="mt-1 text-xs text-gray-500">{{ $checkIn->checked_in_at?->diffForHumans() }} · وزن {{ $checkIn->weight ?? '—' }} كجم</div>
                </a>
            @endforeach
        </div>
    </section>
    @endif
</div>
@endsection
