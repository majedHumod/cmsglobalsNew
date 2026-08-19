<x-filament-panels::page>
    <div class="space-y-6">
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-content space-y-5 p-4 md:p-6">
                <form method="GET" action="{{ \App\Filament\Resources\WorkoutScheduleResource::getUrl('index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-3 md:items-end">
                    <div>
                        <label for="week" class="mb-1 inline-flex items-center gap-x-1 text-sm font-medium text-gray-950 dark:text-white">
                            الأسبوع
                        </label>
                        <select
                            id="week"
                            name="week"
                            class="fi-input block w-full rounded-lg border-none bg-white px-3 py-2 text-sm shadow-sm ring-1 ring-gray-950/10 focus:ring-2 focus:ring-primary-600 dark:bg-white/5 dark:ring-white/20"
                            onchange="this.form.submit()"
                        >
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" @selected((int) $weekNumber === $i)>الأسبوع {{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    @if($isAdmin)
                        <div>
                            <label for="coach" class="mb-1 inline-flex items-center gap-x-1 text-sm font-medium text-gray-950 dark:text-white">
                                المدرب
                            </label>
                            <select
                                id="coach"
                                name="coach"
                                class="fi-input block w-full rounded-lg border-none bg-white px-3 py-2 text-sm shadow-sm ring-1 ring-gray-950/10 focus:ring-2 focus:ring-primary-600 dark:bg-white/5 dark:ring-white/20"
                                onchange="this.form.submit()"
                            >
                                <option value="">كل المدربين</option>
                                @foreach($coachOptions as $id => $name)
                                    <option value="{{ $id }}" @selected((string) $coachId === (string) $id)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="text-sm text-gray-500 dark:text-gray-400 md:pb-2">
                        اختر اليوم ثم أضف موعداً مباشرة من الخلية.
                    </div>
                </form>

                <div>
                    <h3 class="mb-3 text-sm font-semibold text-gray-950 dark:text-white">ملخص الأسبوع {{ $weekNumber }}</h3>
                    <div class="flex flex-row flex-nowrap gap-3 overflow-x-auto pb-1">
                        <div class="min-w-[9.5rem] flex-1 rounded-lg bg-primary-50 px-4 py-3 text-center dark:bg-primary-400/10">
                            <div class="text-2xl font-bold text-primary-700 dark:text-primary-300">{{ $summary['total'] }}</div>
                            <div class="mt-1 text-xs text-gray-600 dark:text-gray-300">إجمالي المواعيد</div>
                        </div>
                        <div class="min-w-[9.5rem] flex-1 rounded-lg bg-success-50 px-4 py-3 text-center dark:bg-success-400/10">
                            <div class="text-2xl font-bold text-success-700 dark:text-success-300">{{ $summary['duration'] }}</div>
                            <div class="mt-1 text-xs text-gray-600 dark:text-gray-300">إجمالي الدقائق</div>
                        </div>
                        <div class="min-w-[9.5rem] flex-1 rounded-lg bg-warning-50 px-4 py-3 text-center dark:bg-warning-400/10">
                            <div class="text-2xl font-bold text-warning-700 dark:text-warning-300">{{ $summary['easy'] }}</div>
                            <div class="mt-1 text-xs text-gray-600 dark:text-gray-300">تمارين سهلة</div>
                        </div>
                        <div class="min-w-[9.5rem] flex-1 rounded-lg bg-danger-50 px-4 py-3 text-center dark:bg-danger-400/10">
                            <div class="text-2xl font-bold text-danger-700 dark:text-danger-300">{{ $summary['hard'] }}</div>
                            <div class="mt-1 text-xs text-gray-600 dark:text-gray-300">تمارين صعبة</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($summary['total'] === 0)
            <div class="rounded-xl bg-warning-50 px-4 py-3 text-sm text-warning-800 ring-1 ring-warning-600/20 dark:bg-warning-400/10 dark:text-warning-200">
                لا توجد مواعيد في الأسبوع {{ $weekNumber }}
                @if($isAdmin && filled($coachId))
                    لهذا المدرب
                @endif
                . يمكنك إضافة موعد من أي يوم أدناه أو تغيير رقم الأسبوع.
            </div>
        @endif

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4 2xl:grid-cols-7">
            @foreach($weeklySchedule as $session => $daySchedules)
                @php
                    $dayLabel = \App\Filament\Resources\WorkoutScheduleResource\Pages\WeeklyBoard::sessionDayLabels()[$session] ?? "الجلسة {$session}";
                @endphp
                <div class="fi-section flex min-h-[220px] flex-col rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="border-b border-gray-100 px-4 py-3 text-center dark:border-white/10">
                        <div class="text-sm font-semibold text-gray-950 dark:text-white">الجلسة {{ $session }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $dayLabel }}</div>
                    </div>

                    <div class="flex-1 space-y-3 p-3">
                        @forelse($daySchedules as $schedule)
                            <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
                                <div class="mb-2 text-sm font-medium text-gray-950 dark:text-white">
                                    {{ $schedule->workout?->name ?? '—' }}
                                </div>
                                <div class="space-y-1 text-xs text-gray-600 dark:text-gray-300">
                                    <div class="flex justify-between gap-2">
                                        <span>المدة</span>
                                        <span>{{ $schedule->workout?->duration ?? '—' }} د</span>
                                    </div>
                                    <div class="flex justify-between gap-2">
                                        <span>الصعوبة</span>
                                        <span>
                                            {{ match($schedule->workout?->difficulty) {
                                                'easy' => 'سهل',
                                                'hard' => 'صعب',
                                                default => 'متوسط',
                                            } }}
                                        </span>
                                    </div>
                                    @if($isAdmin && blank($coachId))
                                        <div class="flex justify-between gap-2">
                                            <span>المدرب</span>
                                            <span>{{ $schedule->user?->name ?? '—' }}</span>
                                        </div>
                                    @endif
                                    @if(filled($schedule->notes))
                                        <div class="mt-2 rounded bg-primary-50 px-2 py-1 text-[11px] text-primary-800 dark:bg-primary-400/10 dark:text-primary-200">
                                            {{ \Illuminate\Support\Str::limit($schedule->notes, 60) }}
                                        </div>
                                    @endif
                                </div>

                                @if(\App\Filament\Resources\WorkoutScheduleResource::canEdit($schedule))
                                    <div class="mt-3 flex items-center gap-3 text-xs">
                                        <a href="{{ $this->editUrl($schedule) }}" class="text-primary-600 hover:underline dark:text-primary-400">
                                            تعديل
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="py-6 text-center text-xs text-gray-400">
                                لا توجد تمارين
                            </div>
                        @endforelse
                    </div>

                    @if(\App\Filament\Resources\WorkoutScheduleResource::canCreate())
                        <div class="border-t border-gray-100 p-3 dark:border-white/10">
                            <a
                                href="{{ $this->createUrl($session) }}"
                                class="fi-btn relative grid-flow-col items-center justify-center gap-1.5 rounded-lg fi-btn-color-gray fi-btn-size-sm fi-btn-outline inline-grid w-full px-3 py-2 text-sm font-semibold outline-none transition duration-75"
                            >
                                إضافة موعد
                            </a>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
