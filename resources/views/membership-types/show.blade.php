@extends('layouts.admin')

@section('title', 'تفاصيل نوع العضوية')

@section('header', 'تفاصيل نوع العضوية: ' . $membershipType->name)

@section('header_actions')
    <div class="flex flex-wrap gap-2">
        @if($membershipType->canBeModified())
            <x-admin.button :href="route('membership-types.edit', $membershipType)" variant="primary">تعديل</x-admin.button>
        @endif
        <x-admin.button :href="route('membership-types.index')" variant="secondary">العودة للقائمة</x-admin.button>
    </div>
@endsection

@section('content')
<x-admin.alert type="info">
    المدة والسعر التجاريان يداران الآن من خلال
    <a href="{{ route('subscription-plans.index') }}" class="font-semibold underline">خطط الاشتراك</a>
    المرتبطة بهذا المسار.
</x-admin.alert>

<x-admin.card class="mb-6">
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-tremor-content-strong">{{ $membershipType->name }}</h1>
            <div class="mt-2">{!! $membershipType->status_badge !!}</div>
            @if($membershipType->description)
                <p class="mt-3 text-tremor-content">{{ $membershipType->description }}</p>
            @endif
        </div>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
        <x-admin.stat label="خطط اشتراك" :value="$membershipType->subscriptionPlans->count()" />
        <x-admin.stat label="مشترك نشط" :value="$membershipType->getActiveSubscribersCount()" />
        <x-admin.stat label="ترتيب العرض" :value="$membershipType->sort_order" />
    </div>

    <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-lg font-semibold text-tremor-content-strong">خطط الاشتراك المرتبطة</h2>
        <x-admin.button
            :href="route('subscription-plans.create', ['membership_type_id' => $membershipType->id])"
            variant="secondary"
            size="sm"
        >
            إضافة خطة
        </x-admin.button>
    </div>

    @forelse($membershipType->subscriptionPlans as $plan)
        <div class="flex items-center justify-between gap-3 border-t border-tremor-border py-3">
            <div>
                <div class="font-medium text-tremor-content-strong">{{ $plan->name }}</div>
                <div class="text-sm text-tremor-content">{{ $plan->formatted_price }} · {{ $plan->duration_text }} · {{ $plan->gender_scope_label }}</div>
            </div>
            <x-admin.action :href="route('subscription-plans.edit', $plan)">تعديل</x-admin.action>
        </div>
    @empty
        <p class="border-t border-tremor-border pt-3 text-sm text-tremor-content">لا توجد خطط اشتراك لهذا المسار بعد.</p>
    @endforelse
</x-admin.card>

<div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
    <x-admin.card :padding="false">
        <x-slot:header>
            <x-admin.section-heading class="mb-0" title="معلومات العضوية" description="تفاصيل تقنية عن نوع العضوية" />
        </x-slot:header>
        <dl class="space-y-4 px-5 py-5">
            <div class="flex justify-between gap-4">
                <dt class="text-sm text-tremor-content">الرمز المميز</dt>
                <dd class="text-sm font-medium text-tremor-content-strong">{{ $membershipType->slug }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-sm text-tremor-content">تاريخ الإنشاء</dt>
                <dd class="text-sm font-medium text-tremor-content-strong">{{ $membershipType->created_at->format('d/m/Y H:i') }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-sm text-tremor-content">آخر تحديث</dt>
                <dd class="text-sm font-medium text-tremor-content-strong">{{ $membershipType->updated_at->format('d/m/Y H:i') }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-sm text-tremor-content">محمي من النظام</dt>
                <dd><x-admin.badge :tone="$membershipType->is_protected ? 'danger' : 'success'">{{ $membershipType->is_protected ? 'نعم' : 'لا' }}</x-admin.badge></dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-sm text-tremor-content">يمكن تعديله</dt>
                <dd><x-admin.badge :tone="$membershipType->canBeModified() ? 'success' : 'neutral'">{{ $membershipType->canBeModified() ? 'نعم' : 'لا' }}</x-admin.badge></dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-sm text-tremor-content">يمكن حذفه</dt>
                <dd><x-admin.badge :tone="$membershipType->canBeDeleted() ? 'success' : 'neutral'">{{ $membershipType->canBeDeleted() ? 'نعم' : 'لا' }}</x-admin.badge></dd>
            </div>
        </dl>
    </x-admin.card>

    <x-admin.card :padding="false">
        <x-slot:header>
            <x-admin.section-heading class="mb-0" title="إحصائيات الاشتراكات" description="بيانات المشتركين في هذا النوع من العضوية" />
        </x-slot:header>
        @php
            $activeCount = $membershipType->activeUserMemberships->count();
            $totalMemberships = $membershipType->userMemberships->count();
            $expiredCount = $totalMemberships - $activeCount;
        @endphp
        <dl class="space-y-4 px-5 py-5">
            <div class="flex justify-between gap-4">
                <dt class="text-sm text-tremor-content">إجمالي الاشتراكات</dt>
                <dd class="text-sm font-medium text-tremor-content-strong">{{ $totalMemberships }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-sm text-tremor-content">الاشتراكات النشطة</dt>
                <dd class="text-sm font-medium text-tremor-content-strong">{{ $activeCount }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-sm text-tremor-content">الاشتراكات المنتهية</dt>
                <dd class="text-sm font-medium text-tremor-content-strong">{{ $expiredCount }}</dd>
            </div>
            @if($membershipType->userMemberships->where('payment_status', 'paid')->isNotEmpty())
                <div class="flex justify-between gap-4">
                    <dt class="text-sm text-tremor-content">إجمالي الإيرادات</dt>
                    <dd class="text-sm font-medium text-tremor-content-strong">
                        {{ number_format($membershipType->userMemberships->where('payment_status', 'paid')->sum('payment_amount'), 2) }} ريال
                    </dd>
                </div>
            @endif
        </dl>

        <div class="border-t border-tremor-border px-5 py-5">
            <h4 class="mb-3 text-sm font-medium text-tremor-content-emphasis">توزيع الاشتراكات</h4>
            @if($totalMemberships > 0)
                <div class="space-y-3">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-sm text-tremor-content">نشطة</span>
                        <div class="flex items-center gap-3">
                            <div class="admin-weight w-32">
                                <span style="width: {{ ($activeCount / $totalMemberships) * 100 }}%"></span>
                            </div>
                            <span class="text-sm text-tremor-content">{{ $activeCount }}</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-sm text-tremor-content">منتهية</span>
                        <div class="flex items-center gap-3">
                            <div class="admin-weight w-32">
                                <span style="width: {{ ($expiredCount / $totalMemberships) * 100 }}%; background: #f43f5e;"></span>
                            </div>
                            <span class="text-sm text-tremor-content">{{ $expiredCount }}</span>
                        </div>
                    </div>
                </div>
            @else
                <p class="text-sm text-tremor-content">لا توجد اشتراكات بعد</p>
            @endif
        </div>
    </x-admin.card>
</div>

@if($membershipType->userMemberships->count() > 0)
    <x-admin.card :padding="false">
        <x-slot:header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <x-admin.section-heading
                    class="mb-0"
                    title="المشتركين في هذه العضوية"
                    description="قائمة بجميع المستخدمين المشتركين في هذا النوع من العضوية"
                />
                <x-admin.badge tone="brand">{{ $membershipType->userMemberships->count() }} اشتراك</x-admin.badge>
            </div>
        </x-slot:header>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-tremor-border">
                <thead class="bg-tremor-background-muted">
                    <tr>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-tremor-content-subtle">المستخدم</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-tremor-content-subtle">تاريخ البداية</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-tremor-content-subtle">تاريخ الانتهاء</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-tremor-content-subtle">الحالة</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-tremor-content-subtle">حالة الدفع</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-tremor-content-subtle">المبلغ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-tremor-border bg-white">
                    @foreach($membershipType->userMemberships->take(10) as $membership)
                        <tr class="hover:bg-tremor-background-muted/70">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <img class="h-10 w-10 rounded-full" src="{{ $membership->user->profile_photo_url }}" alt="{{ $membership->user->name }}">
                                    <div>
                                        <div class="text-sm font-medium text-tremor-content-strong">{{ $membership->user->name }}</div>
                                        <div class="text-sm text-tremor-content">{{ $membership->user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-sm text-tremor-content-emphasis">
                                {{ $membership->starts_at ? $membership->starts_at->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-5 py-4 text-sm text-tremor-content-emphasis">
                                {{ $membership->expires_at ? $membership->expires_at->format('d/m/Y') : '-' }}
                                @if($membership->expires_at && $membership->expires_at > now())
                                    <div class="text-xs text-tremor-content-subtle">({{ $membership->days_remaining }} يوم متبقي)</div>
                                @endif
                            </td>
                            <td class="px-5 py-4">{!! $membership->status_badge !!}</td>
                            <td class="px-5 py-4">
                                <x-admin.badge :tone="$membership->payment_status === 'paid' ? 'success' : ($membership->payment_status === 'pending' ? 'warning' : 'danger')">
                                    {{ ucfirst($membership->payment_status) }}
                                </x-admin.badge>
                            </td>
                            <td class="px-5 py-4 text-sm text-tremor-content-emphasis">
                                {{ number_format($membership->payment_amount, 2) }} ريال
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($membershipType->userMemberships->count() > 10)
            <div class="border-t border-tremor-border px-5 py-4 text-center text-sm text-tremor-content">
                عرض 10 من أصل {{ $membershipType->userMemberships->count() }} اشتراك
            </div>
        @endif
    </x-admin.card>
@else
    <x-admin.card>
        <x-admin.empty-state
            title="لا توجد اشتراكات"
            description="لم يشترك أي مستخدم في هذا النوع من العضوية بعد."
        />

        @if($membershipType->is_active)
            <x-admin.alert type="info" title="نصائح لزيادة الاشتراكات" class="mt-4 mb-0">
                <ul class="list-inside list-disc space-y-1">
                    <li>تأكد من وضوح المميزات المقدمة</li>
                    <li>راجع التسعير مقارنة بالمنافسين</li>
                    <li>أضف محتوى حصري لهذا النوع من العضوية</li>
                    <li>قم بالترويج للعضوية في الصفحة الرئيسية</li>
                </ul>
            </x-admin.alert>
        @else
            <x-admin.alert type="warning" title="العضوية غير نشطة" class="mt-4 mb-0">
                هذا النوع من العضوية غير نشط حالياً، لذلك لا يمكن للمستخدمين الاشتراك فيه.
            </x-admin.alert>
        @endif
    </x-admin.card>
@endif
@endsection
