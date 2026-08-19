@extends('layouts.admin')

@section('title', 'الجداول الغذائية')
@section('header', 'الجداول الغذائية')

@section('header_actions')
<div class="flex flex-wrap items-center gap-2">
    <a href="{{ route('meal-plans.library-review') }}" class="inline-flex items-center rounded-tremor-default border border-tremor-border bg-white px-3 py-2 text-sm font-medium text-tremor-content-emphasis shadow-tremor-input hover:bg-tremor-background-muted">
        مراجعة الصور
    </a>
    <a href="{{ route('meal-plans.public') }}" class="inline-flex items-center rounded-tremor-default border border-tremor-border bg-white px-3 py-2 text-sm font-medium text-tremor-content-emphasis shadow-tremor-input hover:bg-tremor-background-muted">
        العرض العام
    </a>
    <a href="{{ route('meal-plans.create') }}" class="admin-btn-brand">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        إضافة وجبة
    </a>
</div>
@endsection

@section('subheader')
<div class="flex w-full flex-wrap items-end justify-between gap-3">
    <div class="flex items-center overflow-x-auto">
        <a href="{{ route('meal-plans.index', ['tab' => 'all']) }}" class="admin-tab {{ ($tab ?? 'all') === 'all' ? 'is-active' : '' }}">جميع الوجبات</a>
        @if(auth()->user()->hasRole('admin'))
            <a href="{{ route('meal-plans.index', ['tab' => 'mine']) }}" class="admin-tab {{ ($tab ?? '') === 'mine' ? 'is-active' : '' }}">وجباتي</a>
        @endif
        <a href="{{ route('meal-plans.index', ['tab' => 'stats']) }}" class="admin-tab {{ ($tab ?? '') === 'stats' ? 'is-active' : '' }}">الإحصائيات</a>
    </div>
    <span class="text-xs text-tremor-content">
        @if(($tab ?? 'all') !== 'stats' && method_exists($mealPlans, 'total'))
            {{ $mealPlans->total() }} وجبة
        @else
            {{ $stats['total'] ?? $mealPlans->count() }} وجبة
        @endif
    </span>
</div>
@endsection

@section('content')
@php
    $tab = $tab ?? 'all';
@endphp

<div class="space-y-4">
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-3">
        <div class="admin-kpi">
            <div class="admin-kpi-label">إجمالي الوجبات</div>
            <div class="admin-kpi-value">{{ number_format($stats['total'] ?? 0) }}</div>
        </div>
        <div class="admin-kpi">
            <div class="admin-kpi-label">نشطة</div>
            <div class="admin-kpi-value">{{ number_format($stats['active'] ?? 0) }}</div>
            <div class="admin-kpi-meta up">جاهزة للعرض</div>
        </div>
        <div class="admin-kpi">
            <div class="admin-kpi-label">وجباتي</div>
            <div class="admin-kpi-value">{{ number_format($stats['mine'] ?? 0) }}</div>
        </div>
        <div class="admin-kpi">
            <div class="admin-kpi-label">هذا الشهر</div>
            <div class="admin-kpi-value">{{ number_format($stats['month'] ?? 0) }}</div>
            <div class="admin-kpi-meta flat">إضافات جديدة</div>
        </div>
    </div>

    @if($tab === 'stats')
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="admin-kpi">
                <div class="admin-kpi-label">إجمالي الوجبات</div>
                <div class="admin-kpi-value">{{ number_format($stats['total'] ?? 0) }}</div>
            </div>
            <div class="admin-kpi">
                <div class="admin-kpi-label">وجباتي</div>
                <div class="admin-kpi-value">{{ number_format($stats['mine'] ?? 0) }}</div>
            </div>
            <div class="admin-kpi">
                <div class="admin-kpi-label">هذا الشهر</div>
                <div class="admin-kpi-value">{{ number_format($stats['month'] ?? 0) }}</div>
                <div class="admin-kpi-meta up">نمو المكتبة</div>
            </div>
        </div>

        <section class="admin-card overflow-hidden">
            <div class="border-b border-tremor-border px-5 py-4">
                <h3 class="text-sm font-semibold text-tremor-content-strong">آخر الإضافات</h3>
            </div>
            <div class="divide-y divide-tremor-border">
                @forelse($mealPlans as $mealPlan)
                    <div class="flex items-center justify-between gap-3 px-5 py-3.5">
                        <div class="min-w-0 flex items-center gap-3">
                            <span class="h-2 w-2 rounded-full bg-tremor-brand shrink-0"></span>
                            <div class="min-w-0">
                                <div class="truncate text-sm font-medium text-tremor-content-strong">{{ $mealPlan->name }}</div>
                                <div class="text-xs text-tremor-content-subtle">{{ $mealPlan->meal_type_name }}</div>
                            </div>
                        </div>
                        <span class="text-xs text-tremor-content-subtle shrink-0">{{ $mealPlan->created_at?->diffForHumans() ?? '—' }}</span>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-tremor-content">لا يوجد نشاط بعد.</div>
                @endforelse
            </div>
        </section>
    @elseif($tab === 'mine')
        <div class="admin-card overflow-hidden">
            @if($mealPlans->isEmpty())
                <div class="px-6 py-14 text-center">
                    <h3 class="text-sm font-semibold text-tremor-content-strong">لا توجد وجبات شخصية</h3>
                    <a href="{{ route('meal-plans.create') }}" class="admin-btn-brand mt-4 inline-flex">إضافة وجبة</a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-tremor-border text-tremor-label text-tremor-content-subtle">
                                <th class="px-4 py-3 text-right font-medium">الوجبة</th>
                                <th class="px-4 py-3 text-right font-medium">النوع</th>
                                <th class="px-4 py-3 text-right font-medium">السعرات</th>
                                <th class="px-4 py-3 text-right font-medium">بروتين</th>
                                <th class="px-4 py-3 text-right font-medium">كربوهيدرات</th>
                                <th class="px-4 py-3 text-right font-medium">دهون</th>
                                <th class="px-4 py-3 text-right font-medium">الحالة</th>
                                <th class="px-4 py-3 text-right font-medium">إجراء</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-tremor-border">
                            @foreach($mealPlans as $mealPlan)
                                <tr class="hover:bg-tremor-background-muted/80">
                                    <td class="px-4 py-3">
                                        <div class="text-sm font-semibold text-tremor-content-strong">{{ $mealPlan->name }}</div>
                                        <div class="text-xs text-tremor-content-subtle">{{ $mealPlan->difficulty_name }}</div>
                                    </td>
                                    <td class="px-4 py-3"><span class="admin-pill neutral">{{ $mealPlan->meal_type_name }}</span></td>
                                    <td class="px-4 py-3 text-sm text-tremor-content-emphasis">{{ $mealPlan->calories ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-tremor-content-emphasis">{{ $mealPlan->protein ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-tremor-content-emphasis">{{ $mealPlan->carbs ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-tremor-content-emphasis">{{ $mealPlan->fats ?? '—' }}</td>
                                    <td class="px-4 py-3"><span class="admin-pill {{ $mealPlan->is_active ? 'up' : 'down' }}">{{ $mealPlan->is_active ? 'نشط' : 'موقوف' }}</span></td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="flex gap-2">
                                            <a href="{{ route('meal-plans.show', $mealPlan) }}" class="font-medium text-tremor-content-emphasis hover:text-black">عرض</a>
                                            <a href="{{ route('meal-plans.edit', $mealPlan) }}" class="font-medium text-tremor-brand-emphasis hover:text-orange-700">تعديل</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-tremor-border px-4 py-3">
                    {{ $mealPlans->links() }}
                </div>
            @endif
        </div>
    @else
        @if($mealPlans->isEmpty())
            <div class="admin-card px-6 py-16 text-center">
                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-tremor-default bg-tremor-brand-faint text-tremor-brand">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                </div>
                <h3 class="text-tremor-title font-semibold text-tremor-content-strong">لا توجد وجبات بعد</h3>
                <p class="mt-1 text-sm text-tremor-content">ابدأ بإضافة وجبة لبناء مكتبتك الغذائية.</p>
                <a href="{{ route('meal-plans.create') }}" class="admin-btn-brand mt-5 inline-flex">إضافة وجبة</a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                @foreach($mealPlans as $mealPlan)
                    <article class="admin-card overflow-hidden flex flex-col">
                        <div class="relative h-40 bg-tremor-background-subtle">
                            @if($mealPlan->image)
                                <img src="{{ Storage::url($mealPlan->image) }}" alt="{{ $mealPlan->name }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full items-center justify-center text-tremor-content-subtle">
                                    <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif
                            <div class="absolute top-3 right-3">
                                <span class="admin-pill {{ $mealPlan->is_active ? 'up' : 'down' }}">{{ $mealPlan->is_active ? 'نشط' : 'موقوف' }}</span>
                            </div>
                        </div>

                        <div class="flex flex-1 flex-col p-4">
                            <div class="min-w-0">
                                <h3 class="truncate text-sm font-semibold text-tremor-content-strong">{{ $mealPlan->name }}</h3>
                                @if($mealPlan->name_en)
                                    <p class="mt-0.5 truncate text-xs text-tremor-content-subtle">{{ $mealPlan->name_en }}</p>
                                @endif
                            </div>

                            <div class="mt-3 flex flex-wrap gap-1.5">
                                <span class="admin-pill neutral">{{ $mealPlan->meal_type_name }}</span>
                                <span class="admin-pill neutral">{{ $mealPlan->difficulty_name }}</span>
                                @if($mealPlan->source === \App\Models\MealPlan::SOURCE_ARABIC_LIBRARY)
                                    <span class="admin-pill up">مكتبة</span>
                                @endif
                                @if($mealPlan->nutrition_is_estimated)
                                    <span class="admin-pill down">تقديري</span>
                                @endif
                            </div>

                            <div class="mt-4 grid grid-cols-3 gap-2 rounded-tremor-default bg-tremor-background-muted px-3 py-2.5 text-center">
                                <div>
                                    <div class="text-[10px] text-tremor-content-subtle">سعرات</div>
                                    <div class="text-sm font-semibold text-tremor-content-strong">{{ $mealPlan->calories ?? '—' }}</div>
                                </div>
                                <div>
                                    <div class="text-[10px] text-tremor-content-subtle">بروتين</div>
                                    <div class="text-sm font-semibold text-tremor-content-strong">{{ $mealPlan->protein ? $mealPlan->protein.'ج' : '—' }}</div>
                                </div>
                                <div>
                                    <div class="text-[10px] text-tremor-content-subtle">حصص</div>
                                    <div class="text-sm font-semibold text-tremor-content-strong">{{ $mealPlan->servings }}</div>
                                </div>
                            </div>

                            @if($mealPlan->description)
                                <p class="mt-3 line-clamp-2 text-xs text-tremor-content">{{ $mealPlan->description }}</p>
                            @endif

                            <div class="mt-auto pt-4 flex items-center justify-between gap-2 border-t border-tremor-border">
                                <div class="min-w-0 text-[11px] text-tremor-content-subtle truncate">
                                    {{ $mealPlan->user->name ?? '—' }} · {{ $mealPlan->created_at?->format('d/m/Y') ?? '—' }}
                                </div>
                                @if(auth()->user()->hasRole('admin') || $mealPlan->user_id === auth()->id())
                                    <div class="flex items-center gap-1 shrink-0">
                                        <a href="{{ route('meal-plans.show', $mealPlan) }}" class="rounded-tremor-default px-2.5 py-1.5 text-xs font-semibold text-tremor-content-emphasis hover:bg-tremor-background-muted">عرض</a>
                                        <a href="{{ route('meal-plans.edit', $mealPlan) }}" class="rounded-tremor-default px-2.5 py-1.5 text-xs font-semibold text-tremor-brand-emphasis hover:bg-tremor-brand-faint">تعديل</a>
                                        <form action="{{ route('meal-plans.destroy', $mealPlan) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-tremor-default px-2.5 py-1.5 text-xs font-semibold text-tremor-content-strong hover:bg-tremor-background-subtle" onclick="return confirm('حذف هذه الوجبة؟')">حذف</button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="admin-card px-4 py-3">
                {{ $mealPlans->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
