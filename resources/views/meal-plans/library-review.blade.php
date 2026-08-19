@extends('layouts.admin')

@section('title', 'مراجعة صور مكتبة الوجبات')
@section('header', 'مراجعة صور مكتبة الوجبات')

@section('header_actions')
<a href="{{ route('meal-plans.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">العودة للوجبات</a>
@endsection

@section('content')
@php
    $providers = $stockProviders ?? ['openverse' => true, 'pexels' => false, 'unsplash' => false];
@endphp

<div
    class="bg-white shadow-sm rounded-xl border border-gray-200 p-6 space-y-6"
    x-data="mealStockReview({
        searchUrl: @js(route('meal-plans.stock-images.search')),
        applyUrlTemplate: @js(url('/meal-plans/__ID__/stock-image')),
        csrf: @js(csrf_token()),
        providers: @js($providers)
    })"
>
    <div class="rounded-lg bg-sky-50 border border-sky-200 p-4 text-sm text-sky-900 space-y-1">
        <div class="font-medium">مصادر صور مفتوحة وقابلة للاستخدام التجاري</div>
        <ul class="list-disc mr-5 space-y-1">
            <li><strong>Openverse</strong>: مفعّل بدون مفتاح — يفلتر الرخص التجارية فقط.</li>
            <li><strong>Pexels</strong>: مجاني تجاريًا — يحتاج <code>PEXELS_API_KEY</code> {{ $providers['pexels'] ? '(مفعّل)' : '(غير مفعّل)' }}.</li>
            <li><strong>Unsplash</strong>: مجاني تجاريًا — يحتاج <code>UNSPLASH_ACCESS_KEY</code> {{ $providers['unsplash'] ? '(مفعّل)' : '(غير مفعّل)' }}.</li>
        </ul>
        <div>اختر صورة من البحث فتُحفظ محليًا على موقعك، أو ارفع صورة من جهازك.</div>
    </div>

    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="بحث بالاسم…" class="rounded-md border-gray-300">
        <select name="meal_type" class="rounded-md border-gray-300">
            <option value="">كل الأنواع</option>
            @foreach(['breakfast'=>'إفطار','lunch'=>'غداء','dinner'=>'عشاء','snack'=>'سناك'] as $value => $label)
                <option value="{{ $value }}" @selected(request('meal_type')===$value)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="rounded-md bg-indigo-600 text-white px-4 py-2 text-sm">تصفية</button>
    </form>

    @if(session('success'))
        <div class="rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-md bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 text-sm">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @foreach($mealPlans as $mealPlan)
            <div class="border border-gray-200 rounded-xl overflow-hidden" x-data="{ openStock: false }">
                <div class="aspect-[4/3] bg-gray-100">
                    @if($mealPlan->image_url)
                        <img src="{{ $mealPlan->image_url }}?v={{ optional($mealPlan->updated_at)->timestamp }}" alt="{{ $mealPlan->name }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-400 text-sm">بدون صورة</div>
                    @endif
                </div>
                <div class="p-4 space-y-2">
                    <div class="font-semibold text-gray-900">#{{ $mealPlan->id }} — {{ $mealPlan->name }}</div>
                    <div class="text-xs text-gray-500">{{ $mealPlan->name_en }}</div>
                    <div class="text-xs text-indigo-700">{{ $mealPlan->meal_type_name }} · {{ $mealPlan->calories }} سعرة</div>
                    <div class="flex gap-2 text-xs">
                        <a href="{{ route('meal-plans.edit', $mealPlan) }}" class="text-indigo-600 hover:underline">تعديل كامل</a>
                    </div>

                    <button
                        type="button"
                        @click="openStock = !openStock; if (openStock) openPicker(@js($mealPlan->id), @js($mealPlan->name_en ?: $mealPlan->name))"
                        class="w-full rounded-md bg-sky-600 text-white text-sm py-2"
                    >
                        بحث في مصادر مفتوحة
                    </button>

                    <div x-show="openStock && activeMealId === {{ (int) $mealPlan->id }}" x-cloak class="space-y-2 pt-2 border-t border-gray-100">
                        <div class="flex gap-2">
                            <input type="search" x-model="query" @keydown.enter.prevent="search()" class="flex-1 rounded-md border-gray-300 text-xs" placeholder="مثال: grilled shrimp rice">
                            <select x-model="provider" class="rounded-md border-gray-300 text-xs">
                                <option value="openverse">Openverse</option>
                                <option value="pexels" :disabled="!providers.pexels">Pexels</option>
                                <option value="unsplash" :disabled="!providers.unsplash">Unsplash</option>
                            </select>
                        </div>
                        <button type="button" @click="search()" class="w-full rounded-md bg-indigo-600 text-white text-xs py-1.5" :disabled="loading">
                            <span x-text="loading ? 'جاري البحث…' : 'بحث'"></span>
                        </button>
                        <p class="text-xs text-rose-600" x-show="error" x-text="error"></p>
                        <div class="grid grid-cols-3 gap-2 max-h-56 overflow-y-auto" x-show="results.length">
                            <template x-for="item in results" :key="item.id">
                                <button type="button" @click="apply(item)" class="border rounded overflow-hidden hover:ring-2 hover:ring-emerald-500 relative">
                                    <img :src="item.thumb_url" class="w-full h-20 object-cover" alt="">
                                    <span class="absolute bottom-0 inset-x-0 bg-black/60 text-[10px] text-white px-1 truncate" x-text="item.provider"></span>
                                </button>
                            </template>
                        </div>
                        <p class="text-[11px] text-gray-500" x-show="searched && !results.length && !loading">لا توجد نتائج. جرّب كلمات إنجليزية أدق.</p>
                    </div>

                    <form action="{{ route('meal-plans.update-image', $mealPlan) }}" method="POST" enctype="multipart/form-data" class="space-y-2 pt-2 border-t border-gray-100">
                        @csrf
                        @method('PUT')
                        <input type="file" name="image" accept="image/*" required class="block w-full text-xs">
                        <button type="submit" class="w-full rounded-md bg-emerald-600 text-white text-sm py-2">رفع من الجهاز</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

    <div>{{ $mealPlans->links() }}</div>
</div>

@push('scripts')
<script>
function mealStockReview({ searchUrl, applyUrlTemplate, csrf, providers }) {
    return {
        searchUrl,
        applyUrlTemplate,
        csrf,
        providers,
        activeMealId: null,
        query: '',
        provider: providers.openverse ? 'openverse' : (providers.pexels ? 'pexels' : 'unsplash'),
        results: [],
        loading: false,
        searched: false,
        error: '',
        openPicker(mealId, suggestedQuery) {
            this.activeMealId = mealId;
            this.query = suggestedQuery || this.query;
            this.results = [];
            this.searched = false;
            this.error = '';
        },
        async search() {
            if (!this.query || this.query.trim().length < 2) {
                this.error = 'أدخل كلمة بحث (يفضّل بالإنجليزية).';
                return;
            }
            this.loading = true;
            this.error = '';
            this.searched = true;
            try {
                const params = new URLSearchParams({
                    q: this.query.trim(),
                    provider: this.provider,
                    per_page: '12',
                });
                const res = await fetch(`${this.searchUrl}?${params}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                const json = await res.json();
                if (!res.ok) {
                    this.error = json.message || 'فشل البحث';
                    this.results = [];
                    return;
                }
                this.results = json.data || [];
            } catch (e) {
                this.error = 'تعذر الاتصال بمصدر الصور';
                this.results = [];
            } finally {
                this.loading = false;
            }
        },
        async apply(item) {
            if (!this.activeMealId) return;
            this.loading = true;
            this.error = '';
            try {
                const url = this.applyUrlTemplate.replace('__ID__', String(this.activeMealId));
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        image_url: item.full_url,
                        attribution: item.attribution,
                        attribution_url: item.attribution_url,
                        provider: item.provider,
                    }),
                });
                const json = await res.json().catch(() => ({}));
                if (!res.ok) {
                    this.error = json.message || 'تعذر حفظ الصورة';
                    return;
                }
                window.location.reload();
            } catch (e) {
                this.error = 'تعذر حفظ الصورة';
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endpush
@endsection
