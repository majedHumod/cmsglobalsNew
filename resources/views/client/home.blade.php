@extends('layouts.client')

@section('title', 'اليوم')

@push('head')
@if (!empty($initialHomeData))
<script>
    window.__CLIENT_HOME_INITIAL__ = @json($initialHomeData);
</script>
@endif
@endpush

@section('content')
<div x-data="clientHome()" x-init="init()" class="space-y-4">
    <template x-if="loading">
        <div class="rounded-2xl bg-white p-6 shadow-sm text-center text-slate-500">جاري تحميل يومك...</div>
    </template>

    <template x-if="error">
        <div class="rounded-2xl bg-rose-50 text-rose-700 p-4 text-sm" x-text="error"></div>
    </template>

    <template x-if="!loading && data">
        <div class="space-y-4 xl:space-y-0 xl:grid xl:grid-cols-12 xl:gap-6 xl:items-start">
            <div class="space-y-4 xl:col-span-8">
                <section class="rounded-2xl bg-brand text-white p-5 xl:p-6 shadow-sm">
                    <div class="text-sm opacity-90">نقاط التقدم اليوم</div>
                    <div class="mt-1 text-3xl font-bold" x-text="data.progress_score + '%'"></div>
                    <div class="mt-3 text-sm bg-white/15 rounded-xl px-3 py-2" x-text="data.next_best_action"></div>
                    <div class="mt-4 grid grid-cols-3 gap-2 text-center text-xs">
                        <div class="bg-white/10 rounded-lg py-2">
                            <div class="font-bold" x-text="data.weekly_habit_completion + '%'"></div>
                            <div class="opacity-80">عادات</div>
                        </div>
                        <div class="bg-white/10 rounded-lg py-2">
                            <div class="font-bold" x-text="data.workout_compliance + '%'"></div>
                            <div class="opacity-80">تمارين</div>
                        </div>
                        <div class="bg-white/10 rounded-lg py-2">
                            <div class="font-bold" x-text="'أسبوع ' + data.current_program_week"></div>
                            <div class="opacity-80">البرنامج</div>
                        </div>
                    </div>
                </section>

                <template x-if="data.membership_days_remaining !== null && data.membership_days_remaining <= 7">
                    <section class="rounded-2xl bg-rose-50 border border-rose-200 p-4">
                        <div class="font-medium text-rose-900">اشتراكك ينتهي قريباً</div>
                        <p class="text-sm text-rose-700 mt-1" x-text="'متبقي ' + data.membership_days_remaining + ' يوم'"></p>
                        <a :href="data.renew_url" class="inline-block mt-3 text-sm font-medium text-white bg-rose-600 px-4 py-2 rounded-lg">جدّد الآن</a>
                    </section>
                </template>

                <section class="rounded-2xl bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="font-semibold text-slate-900">تمرين اليوم</h2>
                        <span class="text-xs text-slate-500" x-text="data.date"></span>
                    </div>

                    <template x-if="data.today_workouts.length === 0">
                        <p class="text-sm text-slate-500">لا يوجد تمرين مجدول لهذا اليوم.</p>
                    </template>

                    <template x-for="workout in data.today_workouts" :key="workout.workout_schedule_id">
                        <div class="border border-slate-200 rounded-xl p-4 space-y-3 mb-3 last:mb-0">
                            <div>
                                <div class="font-semibold text-slate-900" x-text="workout.name || 'تمرين'"></div>
                                <div class="text-sm text-slate-500 mt-1" x-text="workout.session_label + ' • ' + (workout.duration ? workout.duration + ' دقيقة' : 'بدون مدة محددة')"></div>
                                <p class="text-sm text-slate-600 mt-2" x-show="workout.description" x-text="workout.description"></p>
                            </div>
                            <div class="flex gap-2" x-show="!workout.is_completed && !workout.is_skipped">
                                <button type="button" @click="completeWorkout(workout.workout_schedule_id)" :disabled="actionLoading" class="flex-1 bg-emerald-600 text-white rounded-lg py-2.5 text-sm font-medium disabled:opacity-50">أنجزت التمرين</button>
                                <button type="button" @click="skipWorkout(workout.workout_schedule_id)" :disabled="actionLoading" class="px-4 border border-slate-300 rounded-lg py-2.5 text-sm text-slate-600 disabled:opacity-50">تخطيت</button>
                            </div>
                            <div x-show="workout.is_completed" class="text-sm text-emerald-700 font-medium">تم الإنجاز اليوم</div>
                            <div x-show="workout.is_skipped" class="text-sm text-amber-700 font-medium">تم التخطي اليوم</div>

                            <template x-if="workout.media_url && workout.media_type === 'animated_image'">
                                <div class="space-y-1">
                                    <img :src="workout.media_url" :alt="workout.name" class="w-full max-h-48 xl:max-h-64 object-cover rounded-lg border border-slate-200">
                                    <p class="text-[10px] text-slate-400" x-show="workout.media_source === 'first_exercise'">من أول حركة في الجلسة</p>
                                    <a
                                        x-show="workout.media_attribution?.required"
                                        :href="workout.media_attribution?.url"
                                        target="_blank"
                                        rel="noopener"
                                        class="inline-block text-[10px] text-slate-400 underline"
                                        x-text="workout.media_attribution?.text"
                                    ></a>
                                </div>
                            </template>

                            <a x-show="workout.video_url" :href="workout.video_url" target="_blank" class="inline-block text-sm text-brand">مشاهدة الفيديو</a>

                            <template x-if="workout.exercises && workout.exercises.length">
                                <div class="space-y-3 pt-2 border-t border-slate-100">
                                    <div class="text-xs font-medium text-slate-500">حركات الجلسة</div>
                                    <div class="space-y-3 xl:grid xl:grid-cols-2 xl:gap-3 xl:space-y-0">
                                        <template x-for="exercise in workout.exercises" :key="exercise.id">
                                            <div class="flex gap-3 items-start">
                                                <img x-show="exercise.image_url" :src="exercise.image_url" :alt="exercise.name" class="w-16 h-16 rounded-lg object-cover bg-slate-100 border border-slate-200">
                                                <div class="flex-1 min-w-0">
                                                    <div class="text-sm font-medium text-slate-800" x-text="exercise.name"></div>
                                                    <div class="text-xs text-slate-500 mt-0.5" x-text="[exercise.sets ? exercise.sets + ' مجموعات' : null, exercise.reps ? exercise.reps + ' تكرار' : null].filter(Boolean).join(' · ')"></div>
                                                    <a x-show="exercise.video_url" :href="exercise.video_url" target="_blank" class="inline-block text-[11px] text-brand underline mt-1">فيديو الحركة</a>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </section>

                <section class="rounded-2xl bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="font-semibold text-slate-900">عادات اليوم</h2>
                        <a href="{{ route('client.habits.index') }}" class="text-xs text-brand font-medium">عرض الكل</a>
                    </div>
                    <template x-if="data.habits.length === 0">
                        <p class="text-sm text-slate-500">لا توجد عادات مفعّلة.</p>
                    </template>
                    <div class="space-y-2 xl:grid xl:grid-cols-2 xl:gap-2 xl:space-y-0">
                        <template x-for="habit in data.habits" :key="habit.id">
                            <button type="button" @click="toggleHabit(habit)" class="w-full flex items-center justify-between border border-slate-200 rounded-xl px-4 py-3 text-right">
                                <span class="font-medium text-slate-800" x-text="habit.name"></span>
                                <span class="text-xs px-2 py-1 rounded-full" :class="habit.today_log?.is_completed ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'" x-text="habit.today_log?.is_completed ? 'مكتمل' : 'غير مكتمل'"></span>
                            </button>
                        </template>
                    </div>
                </section>

                <section class="rounded-2xl bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="font-semibold text-slate-900">حجوزات اليوم</h2>
                        <a href="{{ route('client.bookings.index') }}" class="text-xs text-brand font-medium">كل الحجوزات</a>
                    </div>
                    <template x-if="!data.bookings || data.bookings.length === 0">
                        <p class="text-sm text-slate-500">لا توجد حجوزات اليوم.</p>
                    </template>
                    <template x-for="booking in data.bookings" :key="booking.id">
                        <div class="border border-slate-200 rounded-xl p-4 mb-2">
                            <div class="font-medium" x-text="booking.title || 'جلسة'"></div>
                            <div class="text-sm text-slate-500 mt-1" x-text="booking.booking_time"></div>
                            <a x-show="booking.calendar_url" :href="booking.calendar_url" class="inline-block mt-2 text-xs text-brand">إضافة للتقويم</a>
                        </div>
                    </template>
                    <a href="{{ route('client.bookings.create') }}" class="inline-block mt-2 text-sm text-brand">حجز جلسة جديدة</a>
                </section>

                <section class="rounded-2xl bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="font-semibold text-slate-900">نظرة الأسبوع</h2>
                    </div>
                    <div class="grid grid-cols-7 gap-1 xl:gap-2 text-center text-[10px] xl:text-xs">
                        <template x-for="day in data.week_overview" :key="day.date">
                            <div class="rounded-lg p-1.5 xl:p-2" :class="day.is_today ? 'bg-brand-soft ring-1 ring-brand/40' : 'bg-slate-50'">
                                <div class="font-medium text-slate-700" x-text="day.day_label"></div>
                                <div class="mt-1 h-2 rounded-full" :class="day.is_completed ? 'bg-emerald-500' : (day.is_skipped ? 'bg-amber-400' : (day.has_workout ? 'bg-slate-300' : 'bg-transparent'))"></div>
                            </div>
                        </template>
                    </div>
                </section>
            </div>

            <aside class="space-y-4 xl:col-span-4 xl:sticky xl:top-24">
                <section class="rounded-2xl bg-white p-5 shadow-sm" x-show="data.member_pages && data.member_pages.length">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="font-semibold text-slate-900">محتوى لك</h2>
                        <a :href="data.pages_url || '{{ route('client.pages.index') }}'" class="text-xs text-brand font-medium">عرض الكل</a>
                    </div>
                    <div class="space-y-2">
                        <template x-for="page in data.member_pages" :key="page.id">
                            <a :href="page.url" class="block border border-slate-200 rounded-xl px-4 py-3 hover:border-brand/40">
                                <div class="font-medium text-slate-900 text-sm" x-text="page.title"></div>
                                <p class="text-xs text-slate-500 mt-1 line-clamp-1" x-show="page.excerpt" x-text="page.excerpt"></p>
                            </a>
                        </template>
                    </div>
                </section>

                <section class="rounded-2xl bg-white p-4 shadow-sm">
                    <h2 class="font-semibold text-slate-900 mb-3 text-sm">اختصارات</h2>
                    <div class="grid grid-cols-3 xl:grid-cols-1 gap-2">
                        <a :href="data.nutrition_url" class="text-center xl:text-right py-3 xl:px-4 rounded-xl bg-slate-50 text-xs text-slate-700 font-medium">التغذية</a>
                        <a :href="data.community_url" class="text-center xl:text-right py-3 xl:px-4 rounded-xl bg-slate-50 text-xs text-slate-700 font-medium">المجتمع</a>
                        <a :href="data.challenges_url || '{{ route('client.challenges.index') }}'" class="text-center xl:text-right py-3 xl:px-4 rounded-xl bg-slate-50 text-xs text-slate-700 font-medium">التحديات</a>
                    </div>
                    <a :href="data.more_url || '{{ route('client.more') }}'" class="block mt-3 text-center xl:text-right text-xs text-brand font-medium">المزيد من الخدمات والصفحات</a>
                </section>

                <template x-if="data.latest_message">
                    <section class="rounded-2xl bg-white p-4 shadow-sm border border-sky-100">
                        <div class="text-xs text-sky-600 mb-1">آخر رسالة من المدرب</div>
                        <p class="text-sm text-slate-700 line-clamp-2" x-text="data.latest_message.body"></p>
                        <a :href="data.messages_url" class="inline-block mt-2 text-sm text-sky-600">فتح المحادثة</a>
                    </section>
                </template>

                <a :href="data.check_in_url" class="block rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 p-4 text-center font-medium">
                    إرسال متابعة للمدرب
                </a>
            </aside>
        </div>
    </template>
</div>
@endsection
