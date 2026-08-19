<x-filament-panels::page>
    <div class="space-y-6">
        <div class="text-sm text-gray-500 dark:text-gray-400">
            متابعة تقدم العملاء ومخاطر الانقطاع
            @if ($this->isAdmin)
                — عرض نادٍ / مدير النظام
            @endif
        </div>

        @if ($this->isAdmin)
            <x-filament::section>
                <x-slot name="heading">عرض حسب المدرب</x-slot>
                <div class="flex flex-wrap gap-2">
                    <x-filament::button
                        wire:click="setCoachId(null)"
                        size="sm"
                        :color="blank($this->coachId) ? 'primary' : 'gray'"
                    >
                        كل المدربين
                    </x-filament::button>
                    @foreach ($this->coaches as $coach)
                        <x-filament::button
                            wire:click="setCoachId({{ $coach['id'] }})"
                            size="sm"
                            :color="(int) $this->coachId === (int) $coach['id'] ? 'primary' : 'gray'"
                        >
                            {{ $coach['name'] }}
                        </x-filament::button>
                    @endforeach
                </div>
                @if (($this->summary['unassigned_clients'] ?? 0) > 0)
                    <p class="mt-3 text-sm text-amber-700 dark:text-amber-300">
                        عملاء بدون مدرب: {{ $this->summary['unassigned_clients'] }}
                    </p>
                @endif
            </x-filament::section>
        @endif

        @livewire(\App\Filament\Widgets\CoachWorkspaceStats::class, ['coachId' => $this->coachId], key('coach-stats-' . ($this->coachId ?? 'all')))

        <x-filament::section>
            <x-slot name="heading">تصفية قائمة المتابعة</x-slot>
            <div class="flex flex-wrap gap-2">
                @php
                    $filters = [
                        ['key' => null, 'label' => 'الكل'],
                        ['key' => 'checkin_overdue', 'label' => 'Check-in متأخر'],
                        ['key' => 'low_compliance', 'label' => 'التزام منخفض'],
                        ['key' => 'low_nutrition', 'label' => 'غذاء منخفض'],
                        ['key' => 'expiring', 'label' => 'عضوية تنتهي'],
                    ];
                @endphp
                @foreach ($filters as $filterOption)
                    @php
                        $isActive = ($filterOption['key'] === null && blank($this->filter))
                            || ($this->filter === $filterOption['key']);
                    @endphp
                    <x-filament::button
                        wire:click="setFilter(@js($filterOption['key']))"
                        size="sm"
                        :color="$isActive ? 'primary' : 'gray'"
                    >
                        {{ $filterOption['label'] }}
                    </x-filament::button>
                @endforeach
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">عملاء يحتاجون متابعة</x-slot>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-white/10">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr>
                            <th class="px-4 py-3 text-right font-medium text-gray-500">العميل</th>
                            @if ($this->isAdmin)
                                <th class="px-4 py-3 text-right font-medium text-gray-500">المدرب</th>
                            @endif
                            <th class="px-4 py-3 text-right font-medium text-gray-500">المخاطر</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-500">آخر Check-in</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-500">تمارين</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-500">عادات</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-500">تغذية</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-500">الأسباب</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-500">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-white/10 dark:bg-transparent">
                        @forelse ($this->atRiskClients as $client)
                            <tr wire:key="risk-client-{{ $client['user_id'] }}">
                                <td class="px-4 py-4">
                                    <div class="font-medium text-gray-950 dark:text-white">{{ $client['name'] }}</div>
                                    <div class="text-xs text-gray-500">{{ $client['email'] }}</div>
                                </td>
                                @if ($this->isAdmin)
                                    <td class="px-4 py-4 text-gray-700 dark:text-gray-300">{{ $client['coach_name'] ?? 'غير معيّن' }}</td>
                                @endif
                                <td class="px-4 py-4">
                                    <span @class(['inline-flex rounded-full px-2 py-1 text-xs font-semibold', $this->priorityClasses($client['priority'] ?? 'low')])>
                                        {{ $client['risk_score'] }}%
                                    </span>
                                </td>
                                <td class="px-4 py-4 {{ in_array('checkin_overdue', $client['risk_reasons'] ?? [], true) ? 'font-medium text-warning-600' : 'text-gray-700 dark:text-gray-300' }}">
                                    {{ $client['last_check_in_label'] ?? 'لا يوجد' }}
                                </td>
                                <td class="px-4 py-4 {{ $this->rateClasses($client['workout_completion_rate'] ?? 0) }}">
                                    {{ $client['workout_completion_rate'] ?? 0 }}%
                                </td>
                                <td class="px-4 py-4 {{ $this->rateClasses($client['habit_weekly_completion'] ?? 0, 40) }}">
                                    {{ $client['habit_weekly_completion'] ?? 0 }}%
                                </td>
                                <td class="px-4 py-4 {{ $this->rateClasses($client['nutrition_adherence'] ?? 0, 40) }}">
                                    {{ $client['nutrition_adherence'] ?? 0 }}%
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($client['risk_reasons'] as $reason)
                                            <x-filament::badge color="gray">
                                                {{ $this->reasonLabel($reason) }}
                                            </x-filament::badge>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex flex-wrap items-center gap-3 font-medium">
                                        <a href="{{ $client['profile_url'] }}" class="text-primary-600 hover:underline">الملف</a>
                                        <a href="{{ $client['edit_url'] }}" class="text-success-600 hover:underline">تعديل</a>
                                        <button
                                            type="button"
                                            wire:click="sendReminder({{ $client['user_id'] }})"
                                            wire:confirm="إرسال تذكير لهذا العميل؟"
                                            class="text-warning-600 hover:underline"
                                        >
                                            تذكير
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $this->isAdmin ? 9 : 8 }}" class="px-4 py-8 text-center text-gray-500">
                                    لا يوجد عملاء معرّضون للانقطاع حالياً ضمن هذا العرض.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
