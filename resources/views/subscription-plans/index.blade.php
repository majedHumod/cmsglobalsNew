@extends('layouts.admin')

@section('title', 'خطط الاشتراك')

@section('header', 'خطط الاشتراك')

@section('breadcrumbs')
<li>
    <div class="flex items-center">
        <svg class="mx-1 h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
        <span class="text-sm font-medium text-gray-500">خطط الاشتراك</span>
    </div>
</li>
@endsection

@section('header_actions')
    <x-admin.button :href="route('subscription-plans.create')" variant="primary">
        إضافة خطة اشتراك
    </x-admin.button>
@endsection

@section('content')
<x-admin.card :padding="false">
    <x-slot:header>
        <x-admin.section-heading
            class="mb-0"
            title="إدارة العروض التجارية"
            description="السعر والمدة والمزايا تُدار هنا. مسار العضوية يحدد صلاحية الوصول للمحتوى."
        />
    </x-slot:header>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-tremor-border">
            <thead class="bg-tremor-background-muted">
                <tr>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-tremor-content-subtle">الخطة</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-tremor-content-subtle">المسار</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-tremor-content-subtle">المدة</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-tremor-content-subtle">السعر</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-tremor-content-subtle">الجنس</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-tremor-content-subtle">الحالة</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-tremor-border bg-white">
                @forelse($plans as $plan)
                    <tr class="hover:bg-tremor-background-muted/70">
                        <td class="px-5 py-4">
                            <div class="font-semibold text-tremor-content-strong">{{ $plan->name }}</div>
                            @if($plan->description)
                                <div class="mt-0.5 text-sm text-tremor-content">{{ Str::limit($plan->description, 80) }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-sm text-tremor-content-emphasis">{{ $plan->membershipType?->name }}</td>
                        <td class="px-5 py-4 text-sm text-tremor-content-emphasis">{{ $plan->duration_text }}</td>
                        <td class="px-5 py-4 text-sm text-tremor-content-emphasis">
                            <div class="font-medium">{{ $plan->formatted_price }}</div>
                            @if($plan->hasDiscount())
                                <div class="text-xs text-tremor-content-subtle" style="text-decoration: line-through;">{{ $plan->formatted_compare_at_price }}</div>
                                <div class="text-xs text-rose-600">خصم {{ $plan->discountPercent() }}%</div>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <x-admin.badge tone="brand">{{ $plan->gender_scope_label }}</x-admin.badge>
                        </td>
                        <td class="px-5 py-4">
                            <x-admin.badge :tone="$plan->is_active ? 'success' : 'neutral'">
                                {{ $plan->is_active ? 'مفعلة' : 'موقفة' }}
                            </x-admin.badge>
                        </td>
                        <td class="px-5 py-4">
                            <x-admin.actions>
                                <x-admin.action :href="route('subscription-plans.edit', $plan)">تعديل</x-admin.action>
                                <form action="{{ route('subscription-plans.destroy', $plan) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <x-admin.action type="submit" tone="danger" confirm="هل تريد حذف الخطة؟">حذف</x-admin.action>
                                </form>
                            </x-admin.actions>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <x-admin.empty-state
                                title="لا توجد خطط اشتراك بعد"
                                description="أنشئ أول عرض تجاري وربطه بمسار عضوية."
                            >
                                <x-slot:actions>
                                    <x-admin.button :href="route('subscription-plans.create')" variant="primary">إضافة خطة اشتراك</x-admin.button>
                                </x-slot:actions>
                            </x-admin.empty-state>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin.card>
@endsection
