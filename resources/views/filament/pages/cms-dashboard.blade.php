<x-filament-panels::page>
    @php
        $tabs = $this->tabs;
        $stats = $this->activeStats;
        $series = $this->weeklySeries;
        $activity = $this->recentActivity;
        $max = max(1, ...(collect($series)->pluck('count')->all() ?: [1]));
    @endphp

    <div class="space-y-6">
        {{-- Section tabs --}}
        <div class="flex flex-wrap gap-2 rounded-2xl bg-white p-2 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            @foreach ($tabs as $key => $tab)
                <button
                    type="button"
                    wire:click="setTab('{{ $key }}')"
                    @class([
                        'inline-flex items-center gap-2 rounded-xl px-3.5 py-2 text-sm font-medium transition',
                        'bg-primary-600 text-white shadow-sm' => $activeTab === $key,
                        'text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5' => $activeTab !== $key,
                    ])
                >
                    <x-filament::icon :icon="$tab['icon']" class="h-4 w-4" />
                    <span>{{ $tab['label'] }}</span>
                </button>
            @endforeach
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
            {{-- Stats grid --}}
            <div class="xl:col-span-8">
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($stats as $stat)
                        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                            <div class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-primary-50 text-primary-600 dark:bg-primary-400/10 dark:text-primary-300">
                                <x-filament::icon :icon="$stat['icon']" class="h-5 w-5" />
                            </div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $stat['label'] }}</div>
                            <div class="mt-1 text-2xl font-bold tracking-tight text-gray-950 dark:text-white">{{ $stat['value'] }}</div>
                        </div>
                    @endforeach
                </div>

                {{-- Weekly chart --}}
                <div class="mt-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-gray-950 dark:text-white">نشاط الحجوزات</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">آخر 7 أيام</p>
                        </div>
                        <span class="rounded-full bg-primary-50 px-3 py-1 text-xs font-medium text-primary-700 dark:bg-primary-400/10 dark:text-primary-300">
                            {{ $tabs[$activeTab]['label'] ?? '' }}
                        </span>
                    </div>

                    <div class="flex h-44 items-end gap-2 sm:gap-3">
                        @forelse ($series as $point)
                            @php
                                $height = (int) round(($point['count'] / $max) * 100);
                                $height = max($height, $point['count'] > 0 ? 8 : 4);
                            @endphp
                            <div class="flex flex-1 flex-col items-center gap-2">
                                <div class="relative flex h-36 w-full items-end justify-center">
                                    <div
                                        class="w-full max-w-[2.25rem] rounded-t-lg border-2 border-primary-500 bg-primary-50 dark:bg-primary-400/10"
                                        style="height: {{ $height }}%"
                                        title="{{ $point['count'] }}"
                                    ></div>
                                </div>
                                <div class="text-[11px] font-medium text-gray-500 dark:text-gray-400">{{ $point['day'] }}</div>
                            </div>
                        @empty
                            <div class="flex h-full w-full items-center justify-center text-sm text-gray-400">لا توجد بيانات</div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Recent activity --}}
            <div class="xl:col-span-4">
                <div class="h-full rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-base font-semibold text-gray-950 dark:text-white">آخر النشاط</h3>
                        <span class="text-xs text-gray-400">{{ count($activity) }} عنصر</span>
                    </div>

                    <div class="space-y-3">
                        @forelse ($activity as $item)
                            <div class="flex items-start justify-between gap-3 rounded-xl bg-gray-50 px-3 py-3 dark:bg-white/5">
                                <div class="min-w-0">
                                    <div class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $item['title'] }}</div>
                                    <div class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400">{{ $item['meta'] }}</div>
                                </div>
                                <span @class([
                                    'shrink-0 rounded-full px-2 py-0.5 text-[11px] font-medium',
                                    'bg-success-50 text-success-700 dark:bg-success-400/10 dark:text-success-300' => ($item['status_color'] ?? '') === 'success',
                                    'bg-warning-50 text-warning-700 dark:bg-warning-400/10 dark:text-warning-300' => ($item['status_color'] ?? '') === 'warning',
                                    'bg-danger-50 text-danger-700 dark:bg-danger-400/10 dark:text-danger-300' => ($item['status_color'] ?? '') === 'danger',
                                    'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300' => ! in_array($item['status_color'] ?? '', ['success', 'warning', 'danger'], true),
                                ])>
                                    {{ $item['status'] }}
                                </span>
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed border-gray-200 px-4 py-8 text-center text-sm text-gray-400 dark:border-white/10">
                                لا يوجد نشاط حديث
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
