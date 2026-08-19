@extends('layouts.admin')

@section('title', 'ملف العميل')
@section('header', 'ملف العميل: ' . $user->name)

@section('header_actions')
<div class="flex gap-2">
    <form method="POST" action="{{ route('coach.clients.remind', $user) }}">
        @csrf
        <button type="submit" class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium text-white bg-amber-600 hover:bg-amber-700">إرسال تذكير</button>
    </form>
    <a href="{{ route('clients.progress.index', $user) }}" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">عرض التقدم</a>
    <a href="{{ route('clients.progress.create', $user) }}" class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">إضافة Check-in</a>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white shadow rounded-lg p-5">
            <div class="text-sm text-gray-500">التزام التمارين (7 أيام)</div>
            <div class="mt-2 text-3xl font-bold {{ $workoutCompletionRate >= 50 ? 'text-emerald-600' : 'text-red-600' }}">{{ $workoutCompletionRate }}%</div>
        </div>
        <div class="bg-white shadow rounded-lg p-5">
            <div class="text-sm text-gray-500">درجة المخاطر</div>
            <div class="mt-2 text-3xl font-bold text-orange-600">{{ $riskAssessment['risk_score'] }}%</div>
        </div>
        <div class="bg-white shadow rounded-lg p-5">
            <div class="text-sm text-gray-500">العادات الأسبوعية</div>
            <div class="mt-2 text-3xl font-bold text-indigo-600">{{ $riskAssessment['habit_weekly_completion'] }}%</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900">البيانات الأساسية</h3>
            <dl class="mt-4 space-y-3 text-sm">
                <div>
                    <dt class="text-gray-500">البريد الإلكتروني</dt>
                    <dd class="font-medium text-gray-900">{{ $user->email }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">المدرب المسؤول</dt>
                    <dd class="font-medium text-gray-900">{{ $user->coach->name ?? 'غير محدد' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">نوع العضوية الحالية</dt>
                    <dd class="font-medium text-gray-900">{{ $user->membershipType->name ?? 'غير محدد' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">تاريخ انتهاء العضوية</dt>
                    <dd class="font-medium text-gray-900">{{ $user->membership_expires_at?->format('Y-m-d') ?? 'غير محدد' }}</dd>
                </div>
            </dl>
        </div>

        <div class="bg-white shadow rounded-lg p-6 lg:col-span-2">
            <h3 class="text-lg font-semibold text-gray-900">الملف الرياضي</h3>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <div class="text-gray-500">الهدف</div>
                    <div class="mt-1 font-medium text-gray-900">{{ $user->clientProfile->fitness_goal ?? 'غير محدد' }}</div>
                </div>
                <div>
                    <div class="text-gray-500">الوزن المستهدف</div>
                    <div class="mt-1 font-medium text-gray-900">{{ $user->clientProfile->target_weight ? $user->clientProfile->target_weight . ' كجم' : 'غير محدد' }}</div>
                </div>
                <div>
                    <div class="text-gray-500">مستوى النشاط</div>
                    <div class="mt-1 font-medium text-gray-900">{{ $user->clientProfile->activity_level ?? 'beginner' }}</div>
                </div>
                <div>
                    <div class="text-gray-500">أسبوع البرنامج الحالي</div>
                    <div class="mt-1 font-medium text-gray-900">{{ $user->clientProfile->current_program_week ?? 1 }}</div>
                </div>
                <div>
                    <div class="text-gray-500">وسيلة التواصل المفضلة</div>
                    <div class="mt-1 font-medium text-gray-900">{{ $user->clientProfile->preferred_contact_method ?? 'whatsapp' }}</div>
                </div>
                <div class="md:col-span-2">
                    <div class="text-gray-500">الإصابات أو القيود</div>
                    <div class="mt-1 font-medium text-gray-900">{{ $user->clientProfile->injuries ?? 'لا توجد بيانات' }}</div>
                </div>
                <div class="md:col-span-2">
                    <div class="text-gray-500">ملاحظات إضافية</div>
                    <div class="mt-1 font-medium text-gray-900">{{ $user->clientProfile->medical_notes ?? 'لا توجد ملاحظات' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900">آخر Check-ins</h3>
            <div class="mt-4 space-y-4">
                @forelse($user->progressCheckIns as $checkIn)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <div class="font-medium text-gray-900">{{ $checkIn->checked_in_at?->format('Y-m-d H:i') }}</div>
                                <div class="text-sm text-gray-500">وزن: {{ $checkIn->weight ?? '—' }} | التزام: {{ $checkIn->average_adherence ?? '—' }}/10</div>
                            </div>
                            <a href="{{ route('progress-check-ins.show', $checkIn) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">عرض</a>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">لا توجد تحديثات تقدم حتى الآن.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900">الحجوزات القادمة</h3>
            <div class="mt-4 space-y-4">
                @forelse($upcomingBookings as $booking)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="font-medium text-gray-900">{{ $booking->trainingSession->title ?? 'جلسة' }}</div>
                        <div class="mt-1 text-sm text-gray-500">{{ $booking->formatted_booking_datetime }}</div>
                        <div class="mt-1 text-sm text-gray-500">الحالة: {{ $booking->status }}</div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">لا توجد حجوزات قادمة.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">النظام الغذائي للعميل</h3>
        <p class="text-sm text-gray-500 mb-4">اختر وجبات من المكتبة لتعيينها لهذا العميل. إن وُجدت تعيينات، سيظهر للعميل فقط ما عيّنته (بدل المكتبة الكاملة).</p>

        @if(session('success'))
            <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 px-3 py-2 text-sm">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('coach.clients.meals.assign', $user) }}" class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
            @csrf
            <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">وجبات من المكتبة</label>
                <select name="meal_plan_ids[]" multiple size="8" required class="w-full rounded-md border-gray-300 text-sm">
                    @foreach($libraryMeals as $meal)
                        <option value="{{ $meal->id }}">
                            [{{ $meal->meal_type }}] {{ $meal->name }}
                            @if($meal->name_en) — {{ $meal->name_en }} @endif
                            ({{ $meal->calories }} سعرة)
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">اضغط Ctrl/Cmd لاختيار أكثر من وجبة</p>
            </div>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">توقيت الوجبة (اختياري)</label>
                    <select name="meal_slot" class="w-full rounded-md border-gray-300 text-sm">
                        @foreach($mealSlots as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات</label>
                    <textarea name="notes" rows="3" class="w-full rounded-md border-gray-300 text-sm" placeholder="مثال: خطة خسارة وزن أسبوع 1"></textarea>
                </div>
                <button type="submit" class="w-full rounded-md bg-indigo-600 text-white text-sm py-2.5 font-medium hover:bg-indigo-700">تعيين للنظام الغذائي</button>
            </div>
        </form>

        <div class="border-t border-gray-100 pt-4">
            <h4 class="text-sm font-semibold text-gray-800 mb-3">الوجبات المعيّنة حاليًا ({{ $mealAssignments->count() }})</h4>
            <div class="space-y-2">
                @forelse($mealAssignments as $assignment)
                    <div class="flex items-center justify-between gap-3 border border-gray-200 rounded-lg p-3">
                        <div class="min-w-0">
                            <div class="font-medium text-gray-900 truncate">{{ $assignment->mealPlan?->name ?? 'وجبة محذوفة' }}</div>
                            <div class="text-xs text-gray-500">
                                {{ $mealSlots[$assignment->meal_slot ?? ''] ?? 'أي وقت' }}
                                @if($assignment->mealPlan?->calories)
                                    · {{ $assignment->mealPlan->calories }} سعرة
                                @endif
                                @if($assignment->notes)
                                    · {{ $assignment->notes }}
                                @endif
                            </div>
                        </div>
                        <form method="POST" action="{{ route('coach.clients.meals.unassign', [$user, $assignment]) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-rose-600 hover:underline">إزالة</button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">لم يُعيَّن نظام غذائي خاص بعد — العميل يرى المكتبة العامة النشطة.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">نظرة أسبوع التمارين</h3>
        <div class="grid grid-cols-7 gap-2 text-center text-xs">
            @foreach($weekOverview as $day)
                <div class="rounded-lg p-2 {{ $day['is_today'] ? 'bg-indigo-100 ring-1 ring-indigo-300' : 'bg-gray-50' }}">
                    <div class="font-medium text-gray-700">{{ $day['day_label'] }}</div>
                    <div class="mt-2 h-2 rounded-full {{ $day['is_completed'] ? 'bg-emerald-500' : ($day['is_skipped'] ? 'bg-amber-400' : ($day['has_workout'] ? 'bg-gray-300' : 'bg-transparent')) }}"></div>
                    <div class="mt-1 text-gray-500 truncate">{{ $day['workout_name'] ?? '—' }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
