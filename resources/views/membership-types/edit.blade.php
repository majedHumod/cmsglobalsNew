@extends('layouts.admin')

@section('title', 'تعديل مسار العضوية')

@section('header', 'تعديل مسار العضوية: ' . $membershipType->name)

@section('header_actions')
    <div class="flex flex-wrap gap-2">
        <x-admin.button :href="route('membership-types.show', $membershipType)" variant="secondary">عرض التفاصيل</x-admin.button>
        <x-admin.button :href="route('membership-types.index')" variant="secondary">العودة للقائمة</x-admin.button>
    </div>
@endsection

@section('content')
<x-admin.alert type="info">
    السعر والمدة والمميزات تُدار من
    <a href="{{ route('subscription-plans.index') }}" class="font-semibold underline">خطط الاشتراك</a>
    المرتبطة بهذا المسار.
</x-admin.alert>

<x-admin.validation-errors title="تعذر حفظ التعديلات:" />

<x-admin.card class="mb-6">
    <x-admin.section-heading
        title="تعديل مسار العضوية"
        description="هذا المسار يُستخدم لفلترة المحتوى المعروض للمشتركين."
    />

    <form action="{{ route('membership-types.update', $membershipType) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <x-admin.label for="name" value="اسم المسار *" />
                <x-admin.input type="text" name="name" id="name" :value="old('name', $membershipType->name)" required />
                <x-admin.field-error name="name" />
            </div>

            <div>
                <x-admin.label for="sort_order" value="ترتيب العرض" />
                <x-admin.input type="number" name="sort_order" id="sort_order" min="0" :value="old('sort_order', $membershipType->sort_order)" />
                <x-admin.field-error name="sort_order" />
            </div>
        </div>

        <div>
            <x-admin.label for="description" value="وصف المسار" />
            <x-admin.textarea name="description" id="description" rows="3">{{ old('description', $membershipType->description) }}</x-admin.textarea>
            <x-admin.field-error name="description" />
        </div>

        <label class="flex items-start gap-3">
            <input
                type="checkbox"
                name="is_active"
                id="is_active"
                value="1"
                class="mt-1 rounded border-tremor-border text-tremor-brand shadow-tremor-input focus:border-tremor-brand focus:ring-tremor-brand"
                @checked(old('is_active', $membershipType->is_active))
            >
            <span class="text-sm font-medium text-tremor-content-emphasis">تفعيل المسار</span>
        </label>

        <x-admin.form-actions
            :cancel-href="route('membership-types.index')"
            submit-label="حفظ التعديلات"
        />
    </form>
</x-admin.card>

<x-admin.card :padding="false">
    <x-slot:header>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <x-admin.section-heading
                class="mb-0"
                title="خطط الاشتراك المرتبطة"
                description="العروض التجارية (السعر / المدة / المميزات) لهذا المسار"
            />
            <x-admin.button
                :href="route('subscription-plans.create', ['membership_type_id' => $membershipType->id])"
                variant="primary"
                size="sm"
            >
                إضافة خطة
            </x-admin.button>
        </div>
    </x-slot:header>

    <div class="px-5 py-2">
        @forelse($membershipType->subscriptionPlans as $plan)
            <div class="flex items-center justify-between gap-3 py-3 {{ ! $loop->last ? 'border-b border-tremor-border' : '' }}">
                <div>
                                    <div class="font-medium text-tremor-content-strong">{{ $plan->name }}</div>
                                    <div class="text-sm text-tremor-content">{{ $plan->formatted_price }} · {{ $plan->duration_text }} · {{ $plan->gender_scope_label }}</div>
                                </div>
                                <x-admin.action :href="route('subscription-plans.edit', $plan)">تعديل</x-admin.action>
            </div>
        @empty
            <div class="py-4">
                <x-admin.empty-state
                    title="لا توجد خطط اشتراك بعد"
                    description="أضف خطة لعرض السعر والمدة للعملاء."
                >
                    <x-slot:actions>
                        <x-admin.button
                            :href="route('subscription-plans.create', ['membership_type_id' => $membershipType->id])"
                            variant="primary"
                        >
                            إضافة خطة
                        </x-admin.button>
                    </x-slot:actions>
                </x-admin.empty-state>
            </div>
        @endforelse
    </div>
</x-admin.card>
@endsection
