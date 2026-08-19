@extends('layouts.client')

@section('title', 'التغذية')

@section('content')
<div class="space-y-4">
    <section class="rounded-2xl bg-gradient-to-l from-emerald-600 to-teal-600 text-white p-5 shadow-sm">
        <div class="text-sm opacity-90">التزامك الغذائي هذا الأسبوع</div>
        <div class="text-3xl font-bold mt-1">{{ $weeklyAdherence }}%</div>
    </section>

    <section class="rounded-2xl bg-amber-50 border border-amber-200 p-4 text-sm text-amber-900">
        {{ config('meal_library.nutrition_disclaimer_ar') }}
    </section>

    <section
        class="rounded-2xl bg-white p-5 shadow-sm"
        x-data="mealLibraryPicker({
            searchUrl: @js(route('meal-plans.search')),
            plans: @js($mealPlans->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->localized_name,
                'name_en' => $p->name_en,
                'meal_type' => $p->meal_type,
                'calories' => $p->calories,
                'protein' => $p->protein,
                'carbs' => $p->carbs,
                'fats' => $p->fats,
                'nutrition_is_estimated' => (bool) $p->nutrition_is_estimated,
                'image_url' => $p->image_url,
            ])->values()),
            initialSlot: @js(old('meal_slot', 'breakfast')),
            initialPlanId: @js(old('meal_plan_id'))
        })"
    >
        <h2 class="font-semibold text-slate-900 mb-3">سجّل وجبة اليوم</h2>
        <form method="POST" action="{{ route('client.nutrition.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-sm text-slate-700 mb-1">نوع الوجبة</label>
                <select name="meal_slot" x-model="slot" @change="onSlotChange()" class="w-full rounded-xl border-slate-300" required>
                    @foreach($mealSlots as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm text-slate-700 mb-1">اختر من مكتبة الوجبات</label>
                <input type="hidden" name="meal_plan_id" :value="selectedId || ''">
                <input
                    type="search"
                    x-model="query"
                    @input.debounce.300ms="search()"
                    placeholder="ابحث بالعربي أو الإنجليزي…"
                    class="w-full rounded-xl border-slate-300 mb-2"
                >
                <div class="max-h-64 overflow-y-auto space-y-2 border border-slate-100 rounded-xl p-2" x-show="filtered.length" x-cloak>
                    <template x-for="plan in filtered" :key="plan.id">
                        <button
                            type="button"
                            @click="select(plan)"
                            class="w-full flex items-center gap-3 p-2 rounded-xl text-right hover:bg-emerald-50 border"
                            :class="selectedId === plan.id ? 'border-emerald-500 bg-emerald-50' : 'border-transparent'"
                        >
                            <img x-show="plan.image_url" :src="plan.image_url" class="w-14 h-14 rounded-lg object-cover bg-slate-100" alt="">
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-slate-900 truncate" x-text="plan.name"></div>
                                <div class="text-xs text-slate-500 truncate" x-show="plan.name_en" x-text="plan.name_en"></div>
                                <div class="text-xs text-emerald-700 mt-0.5">
                                    <span x-text="(plan.calories || 0) + ' سعرة'"></span>
                                    · ب <span x-text="plan.protein || 0"></span>
                                    · ك <span x-text="plan.carbs || 0"></span>
                                    · د <span x-text="plan.fats || 0"></span>
                                    <span x-show="plan.nutrition_is_estimated" class="text-amber-600"> · تقديري</span>
                                </div>
                            </div>
                        </button>
                    </template>
                </div>
                <p class="text-xs text-slate-500 mt-1" x-show="selected" x-cloak>
                    المختار: <span class="font-medium text-slate-800" x-text="selected?.name"></span>
                    <button type="button" class="text-rose-600 mr-2" @click="clear()">إلغاء</button>
                </p>
            </div>

            <div>
                <label class="block text-sm text-slate-700 mb-1">درجة الالتزام /10</label>
                <input type="number" name="adherence_score" min="0" max="10" value="{{ old('adherence_score', 8) }}" class="w-full rounded-xl border-slate-300" required>
            </div>
            <div>
                <label class="block text-sm text-slate-700 mb-1">ملاحظات</label>
                <textarea name="notes" rows="2" class="w-full rounded-xl border-slate-300" placeholder="ماذا أكلت اليوم؟">{{ old('notes') }}</textarea>
            </div>
            <button type="submit" class="w-full bg-emerald-600 text-white rounded-xl py-3 font-medium">حفظ</button>
        </form>
    </section>

    <section class="space-y-2">
        <h2 class="font-semibold text-slate-900">وجبات اليوم</h2>
        @forelse($todayLogs as $log)
            <div class="rounded-2xl bg-white p-4 shadow-sm border border-slate-100">
                <div class="font-medium text-slate-900">{{ $mealSlots[$log->meal_slot] ?? $log->meal_slot }}</div>
                @if($log->mealPlan)
                    <div class="text-sm text-slate-500">{{ $log->mealPlan->localized_name }}</div>
                    @if($log->mealPlan->nutrition_is_estimated)
                        <div class="text-xs text-amber-600 mt-1">قيم غذائية تقديرية</div>
                    @endif
                @endif
                <div class="text-sm text-emerald-700 mt-1">التزام: {{ $log->adherence_score }}/10</div>
                @if($log->notes)
                    <p class="text-xs text-slate-500 mt-1">{{ $log->notes }}</p>
                @endif
            </div>
        @empty
            <div class="rounded-2xl bg-white p-6 text-center text-slate-500 text-sm">لم تُسجّل وجبات اليوم بعد.</div>
        @endforelse
    </section>
</div>

@push('scripts')
<script>
function mealLibraryPicker({ searchUrl, plans, initialSlot, initialPlanId }) {
    return {
        searchUrl,
        plans: plans || [],
        query: '',
        slot: initialSlot || 'breakfast',
        selectedId: initialPlanId ? Number(initialPlanId) : null,
        get selected() {
            return this.plans.find(p => Number(p.id) === Number(this.selectedId)) || null;
        },
        get filtered() {
            const q = (this.query || '').trim().toLowerCase();
            return this.plans
                .filter(p => !this.slot || p.meal_type === this.slot)
                .filter(p => {
                    if (!q) return true;
                    return (p.name || '').toLowerCase().includes(q)
                        || (p.name_en || '').toLowerCase().includes(q);
                })
                .slice(0, 30);
        },
        onSlotChange() {
            if (this.selected && this.selected.meal_type !== this.slot) {
                this.clear();
            }
        },
        select(plan) {
            this.selectedId = plan.id;
        },
        clear() {
            this.selectedId = null;
        },
        async search() {
            const params = new URLSearchParams({
                q: this.query || '',
                meal_type: this.slot || '',
                limit: '30',
            });
            try {
                const res = await fetch(`${this.searchUrl}?${params.toString()}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                if (!res.ok) return;
                const json = await res.json();
                if (Array.isArray(json.data)) {
                    this.plans = json.data;
                }
            } catch (e) {}
        }
    }
}
</script>
@endpush
@endsection
