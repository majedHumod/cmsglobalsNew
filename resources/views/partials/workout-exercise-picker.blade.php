@php
    use App\Models\Exercise;

    $selectedExercises = collect(old('exercises', $selectedExercises ?? []));
    if ($selectedExercises->isEmpty() && isset($workout)) {
        $workout->loadMissing('exercises');
        $selectedExercises = $workout->exercises->map(fn ($ex) => [
            'id' => $ex->id,
            'name' => $ex->localized_name,
            'name_en' => $ex->name,
            'image_url' => $ex->image_url,
            'sets' => $ex->pivot->sets,
            'reps' => $ex->pivot->reps,
            'rest_seconds' => $ex->pivot->rest_seconds,
            'coach_cue' => $ex->pivot->coach_cue,
            'attribution_required' => $ex->attribution_required,
            'attribution_text' => $ex->attribution_text,
            'attribution_url' => $ex->attribution_url,
        ])->values();
    } elseif ($selectedExercises->isNotEmpty()) {
        $byId = Exercise::query()
            ->whereIn('id', $selectedExercises->pluck('id')->filter())
            ->get()
            ->keyBy('id');
        $selectedExercises = $selectedExercises->map(function ($row) use ($byId) {
            $exercise = $byId->get((int) ($row['id'] ?? 0));

            return [
                'id' => (int) ($row['id'] ?? 0),
                'name' => $exercise?->localized_name ?? ('#'.($row['id'] ?? '')),
                'name_en' => $exercise?->name,
                'image_url' => $exercise?->image_url,
                'sets' => $row['sets'] ?? 3,
                'reps' => $row['reps'] ?? '8-10',
                'rest_seconds' => $row['rest_seconds'] ?? 60,
                'coach_cue' => $row['coach_cue'] ?? '',
                'attribution_required' => $exercise?->attribution_required ?? true,
                'attribution_text' => $exercise?->attribution_text,
                'attribution_url' => $exercise?->attribution_url,
            ];
        })->values();
    }
@endphp

<div
    class="border-b border-gray-200 py-6"
    x-data="workoutExercisePicker({
        searchUrl: @js(route('exercises.search')),
        initial: @js($selectedExercises->values())
    })"
>
    <h3 class="text-lg font-medium text-gray-900">حركات من المكتبة</h3>
    <p class="mt-1 text-sm text-gray-500">اختر حركات من مكتبة RepDB لإرفاقها بهذه الجلسة. سيظهر النسب تلقائياً عند عرض الصور.</p>

    <div class="mt-4 flex gap-2">
        <input
            type="search"
            x-model="query"
            @input.debounce.300ms="search()"
            placeholder="ابحث عن حركة…"
            class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        >
        <button type="button" @click="search()" class="px-4 py-2 text-sm rounded-md bg-indigo-600 text-white hover:bg-indigo-700">بحث</button>
    </div>

    <div class="mt-3 max-h-56 overflow-y-auto border border-gray-200 rounded-md divide-y" x-show="results.length" x-cloak>
        <template x-for="item in results" :key="item.id">
            <button type="button" @click="add(item)" class="w-full flex items-center gap-3 p-2 text-right hover:bg-indigo-50">
                <img x-show="item.image_url" :src="item.image_url" class="w-12 h-12 rounded object-cover bg-gray-100" alt="">
                <div class="flex-1">
                    <div class="text-sm font-medium text-gray-900" x-text="item.name"></div>
                    <div class="text-xs text-gray-500" x-text="[item.body_part_label || item.body_part, item.equipment_label || item.equipment].filter(Boolean).join(' · ')"></div>
                    <div class="text-[10px] text-gray-400" x-show="item.name_en && item.name_en !== item.name" x-text="item.name_en"></div>
                </div>
                <span class="text-indigo-600 text-xs">إضافة</span>
            </button>
        </template>
    </div>
    <p class="text-xs text-gray-500 mt-2" x-show="searched && !results.length" x-cloak>لا توجد نتائج.</p>

    <div class="mt-4 space-y-3" x-show="selected.length" x-cloak>
        <template x-for="(row, index) in selected" :key="row.id + '-' + index">
            <div class="border border-gray-200 rounded-lg p-3 bg-gray-50">
                <input type="hidden" :name="'exercises['+index+'][id]'" :value="row.id">
                <div class="flex items-start gap-3">
                    <img x-show="row.image_url" :src="row.image_url" class="w-14 h-14 rounded object-cover bg-white border" alt="">
                    <div class="flex-1">
                        <div class="flex justify-between gap-2">
                            <div class="text-sm font-medium text-gray-900" x-text="row.name"></div>
                            <button type="button" @click="remove(index)" class="text-xs text-red-600">إزالة</button>
                        </div>
                        <div class="mt-2 grid grid-cols-2 md:grid-cols-4 gap-2">
                            <div>
                                <label class="block text-xs text-gray-500">مجموعات</label>
                                <input type="number" min="1" max="20" class="mt-1 w-full rounded-md border-gray-300 text-sm" :name="'exercises['+index+'][sets]'" x-model="row.sets">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500">تكرارات</label>
                                <input type="text" class="mt-1 w-full rounded-md border-gray-300 text-sm" :name="'exercises['+index+'][reps]'" x-model="row.reps" placeholder="8-10">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500">راحة (ث)</label>
                                <input type="number" min="0" max="600" class="mt-1 w-full rounded-md border-gray-300 text-sm" :name="'exercises['+index+'][rest_seconds]'" x-model="row.rest_seconds">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500">ملاحظة</label>
                                <input type="text" class="mt-1 w-full rounded-md border-gray-300 text-sm" :name="'exercises['+index+'][coach_cue]'" x-model="row.coach_cue">
                            </div>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-2" x-show="row.attribution_required && row.image_url">
                            <a :href="row.attribution_url" target="_blank" rel="noopener" class="underline" x-text="row.attribution_text"></a>
                        </p>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

@once
@push('scripts')
<script>
function workoutExercisePicker({ searchUrl, initial }) {
    return {
        searchUrl,
        query: '',
        results: [],
        searched: false,
        selected: Array.isArray(initial) ? initial.map((row) => ({
            id: row.id,
            name: row.name,
            image_url: row.image_url || null,
            sets: row.sets ?? 3,
            reps: row.reps ?? '8-10',
            rest_seconds: row.rest_seconds ?? 60,
            coach_cue: row.coach_cue ?? '',
            attribution_required: !!row.attribution_required,
            attribution_text: row.attribution_text || 'Exercise data by RepDB (repdb.co)',
            attribution_url: row.attribution_url || 'https://repdb.co',
        })) : [],
        async search() {
            const params = new URLSearchParams({ q: this.query || '', limit: '20' });
            const response = await fetch(`${this.searchUrl}?${params.toString()}`, {
                headers: { 'Accept': 'application/json' },
            });
            const data = await response.json();
            this.results = (data.exercises || []).filter((item) => !this.selected.some((s) => Number(s.id) === Number(item.id)));
            this.searched = true;
        },
        add(item) {
            this.selected.push({
                id: item.id,
                name: item.name,
                image_url: item.image_url,
                sets: 3,
                reps: '8-10',
                rest_seconds: 60,
                coach_cue: '',
                attribution_required: !!item.attribution_required,
                attribution_text: item.attribution_text || 'Exercise data by RepDB (repdb.co)',
                attribution_url: item.attribution_url || 'https://repdb.co',
            });
            this.results = this.results.filter((r) => Number(r.id) !== Number(item.id));
        },
        remove(index) {
            this.selected.splice(index, 1);
        },
    };
}
</script>
@endpush
@endonce
