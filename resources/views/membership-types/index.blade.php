@extends('layouts.admin')

@section('title', 'إدارة أنواع العضويات')

@section('header', 'إدارة أنواع العضويات')

@section('header_actions')
    <div class="flex flex-wrap gap-2">
        <x-admin.button :href="route('subscription-plans.index')" variant="secondary">إدارة الخطط</x-admin.button>
        <x-admin.button :href="route('membership-types.create')" variant="primary">إضافة نوع عضوية جديد</x-admin.button>
    </div>
@endsection

@section('content')
@php
    $activeMemberships = $membershipTypes->where('is_active', true);
    $totalSubscribers = $membershipTypes->sum(fn ($type) => $type->getActiveSubscribersCount());
    $withPlans = $membershipTypes->where('subscription_plans_count', '>', 0)->count();
    $withoutPlans = $membershipTypes->where('subscription_plans_count', 0)->count();
    $totalTypes = $membershipTypes->count();
@endphp

<div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
    <x-admin.stat label="إجمالي أنواع العضويات" :value="$membershipTypes->count()" />
    <x-admin.stat label="العضويات النشطة" :value="$activeMemberships->count()" />
    <x-admin.stat label="خطط الاشتراك المرتبطة" :value="$membershipTypes->sum('subscription_plans_count')" />
    <x-admin.stat label="العضويات المحمية" :value="$membershipTypes->where('is_protected', true)->count()" />
</div>

<x-admin.card>
    <x-admin.section-heading
        title="إدارة أنواع العضويات"
        description="أنواع العضويات تمثل المسارات التدريبية، أما المدة والسعر القابلان للبيع فيداران من خلال خطط الاشتراك."
    />

    <div class="mb-6 border-b border-tremor-border">
        <ul class="flex flex-wrap" id="membershipTabs" role="tablist">
            <li role="presentation">
                <button class="admin-tab is-active" id="all-memberships-tab" data-tabs-target="#all-memberships" type="button" role="tab" aria-controls="all-memberships" aria-selected="true">
                    جميع أنواع العضويات
                </button>
            </li>
            <li role="presentation">
                <button class="admin-tab" id="active-memberships-tab" data-tabs-target="#active-memberships" type="button" role="tab" aria-controls="active-memberships" aria-selected="false">
                    العضويات النشطة
                </button>
            </li>
            <li role="presentation">
                <button class="admin-tab" id="statistics-tab" data-tabs-target="#statistics" type="button" role="tab" aria-controls="statistics" aria-selected="false">
                    الإحصائيات
                </button>
            </li>
        </ul>
    </div>

    <div id="membershipTabContent">
        <div class="block" id="all-memberships" role="tabpanel" aria-labelledby="all-memberships-tab">
            @if($membershipTypes->isEmpty())
                <x-admin.empty-state
                    title="لا توجد أنواع عضويات"
                    description="ابدأ بإنشاء نوع عضوية جديد لإدارة اشتراكات المستخدمين."
                >
                    <x-slot:actions>
                        <x-admin.button :href="route('membership-types.create')" variant="primary">إضافة نوع عضوية جديد</x-admin.button>
                    </x-slot:actions>
                </x-admin.empty-state>
            @else
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($membershipTypes as $membershipType)
                        <div @class([
                            'overflow-hidden rounded-tremor-default border border-tremor-border bg-white shadow-tremor-card',
                            'ring-2 ring-rose-200 bg-rose-50/40' => $membershipType->is_protected,
                        ])>
                            <div class="p-5">
                                <div class="mb-3 flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="text-lg font-semibold text-tremor-content-strong">{{ $membershipType->name }}</h3>
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            @if($membershipType->is_protected)
                                                <x-admin.badge tone="danger">محمي</x-admin.badge>
                                            @else
                                                <x-admin.badge :tone="$membershipType->is_active ? 'success' : 'neutral'">
                                                    {{ $membershipType->is_active ? 'نشط' : 'غير نشط' }}
                                                </x-admin.badge>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                @if($membershipType->description)
                                    <p class="mb-4 line-clamp-2 text-sm text-tremor-content">{{ $membershipType->description }}</p>
                                @endif

                                <div class="mb-4 rounded-tremor-default bg-tremor-brand-faint px-3 py-3">
                                    <div class="text-sm font-medium text-tremor-brand-emphasis">
                                        {{ $membershipType->subscription_plans_count ?? $membershipType->subscriptionPlans->count() }} خطة اشتراك
                                    </div>
                                    @if(($membershipType->subscriptionPlans ?? collect())->isNotEmpty())
                                        <ul class="mt-2 space-y-1 text-xs text-tremor-content-emphasis">
                                            @foreach($membershipType->subscriptionPlans->take(3) as $linkedPlan)
                                                <li>{{ $linkedPlan->name }} — {{ $linkedPlan->formatted_price }} / {{ $linkedPlan->duration_text }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="mt-1 text-xs text-tremor-content">لا توجد عروض بعد. أضف خطة اشتراك لهذا المسار.</p>
                                    @endif
                                </div>

                                <p class="mb-4 text-sm text-tremor-content">
                                    {{ $membershipType->active_user_memberships_count ?? $membershipType->getActiveSubscribersCount() }} مشترك نشط
                                </p>
                            </div>

                            <div class="border-t border-tremor-border bg-tremor-background-muted/60 px-4 py-3">
                                <x-admin.actions align="start">
                                    <x-admin.action :href="route('membership-types.show', $membershipType)">عرض</x-admin.action>

                                    @if($membershipType->canBeModified())
                                        <x-admin.action :href="route('membership-types.edit', $membershipType)">تعديل</x-admin.action>

                                        <form action="{{ route('membership-types.toggle-status', $membershipType) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <x-admin.action type="submit">
                                                {{ $membershipType->is_active ? 'إيقاف' : 'تفعيل' }}
                                            </x-admin.action>
                                        </form>
                                    @else
                                        <x-admin.badge tone="danger">محمي</x-admin.badge>
                                    @endif

                                    @if($membershipType->canBeDeleted())
                                        <form action="{{ route('membership-types.destroy', $membershipType) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <x-admin.action type="submit" tone="danger" confirm="هل أنت متأكد من حذف هذا النوع من العضوية؟">حذف</x-admin.action>
                                        </form>
                                    @endif
                                </x-admin.actions>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="hidden" id="active-memberships" role="tabpanel" aria-labelledby="active-memberships-tab">
            @if($activeMemberships->isEmpty())
                <x-admin.empty-state
                    title="لا توجد عضويات نشطة"
                    description="قم بتفعيل بعض أنواع العضويات لتظهر هنا."
                />
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-tremor-border">
                        <thead class="bg-tremor-background-muted">
                            <tr>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-tremor-content-subtle">اسم المسار</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-tremor-content-subtle">خطط الاشتراك</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-tremor-content-subtle">المشتركين</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-tremor-border bg-white">
                            @foreach($activeMemberships as $membershipType)
                                <tr class="hover:bg-tremor-background-muted/70">
                                    <td class="px-4 py-4">
                                        <div class="font-medium text-tremor-content-strong">{{ $membershipType->name }}</div>
                                        @if($membershipType->description)
                                            <div class="text-sm text-tremor-content">{{ Str::limit($membershipType->description, 50) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-sm text-tremor-content-emphasis">
                                        {{ $membershipType->subscription_plans_count ?? $membershipType->subscriptionPlans->count() }}
                                    </td>
                                    <td class="px-4 py-4 text-sm text-tremor-content">
                                        {{ $membershipType->active_user_memberships_count ?? $membershipType->getActiveSubscribersCount() }}
                                    </td>
                                    <td class="px-4 py-4">
                                        <x-admin.actions>
                                            <x-admin.action :href="route('membership-types.show', $membershipType)">عرض</x-admin.action>
                                            @if($membershipType->canBeModified())
                                                <x-admin.action :href="route('membership-types.edit', $membershipType)">تعديل</x-admin.action>
                                            @endif
                                        </x-admin.actions>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="hidden" id="statistics" role="tabpanel" aria-labelledby="statistics-tab">
            <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
                <x-admin.stat label="مسارات لها خطط" :value="$withPlans" />
                <x-admin.stat label="إجمالي المشتركين" :value="$totalSubscribers" />
                <x-admin.stat label="إجمالي خطط الاشتراك" :value="$membershipTypes->sum('subscription_plans_count')" />
            </div>

            <div class="rounded-tremor-default border border-tremor-border bg-tremor-background-muted/50 p-5">
                <h3 class="mb-4 text-base font-semibold text-tremor-content-strong">توزيع المسارات حسب وجود خطط اشتراك</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-sm font-medium text-tremor-content-strong">مسارات بخطط اشتراك</span>
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-tremor-content">{{ $withPlans }}</span>
                            <div class="admin-weight w-32">
                                <span style="width: {{ $totalTypes > 0 ? ($withPlans / $totalTypes) * 100 : 0 }}%"></span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-sm font-medium text-tremor-content-strong">مسارات بدون خطط</span>
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-tremor-content">{{ $withoutPlans }}</span>
                            <div class="admin-weight w-32">
                                <span style="width: {{ $totalTypes > 0 ? ($withoutPlans / $totalTypes) * 100 : 0 }}%; background: #f59e0b;"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin.card>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabButtons = document.querySelectorAll('#membershipTabs [role="tab"]');
        const tabPanels = document.querySelectorAll('#membershipTabContent [role="tabpanel"]');

        tabButtons.forEach((button) => {
            button.addEventListener('click', () => {
                tabPanels.forEach((panel) => panel.classList.add('hidden'));

                const panelId = button.getAttribute('data-tabs-target').substring(1);
                document.getElementById(panelId)?.classList.remove('hidden');

                tabButtons.forEach((btn) => {
                    btn.setAttribute('aria-selected', 'false');
                    btn.classList.remove('is-active');
                });

                button.setAttribute('aria-selected', 'true');
                button.classList.add('is-active');
            });
        });
    });
</script>
@endsection
