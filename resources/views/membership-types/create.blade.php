@extends('layouts.admin')

@section('title', 'إضافة مسار عضوية')

@section('header', 'إضافة مسار عضوية جديد')

@section('header_actions')
    <x-admin.button :href="route('membership-types.index')" variant="secondary">العودة للقائمة</x-admin.button>
@endsection

@section('content')
<x-admin.alert type="info">
    مسار العضوية يحدد <strong>صلاحية الوصول للمحتوى</strong>. السعر والمدة والمميزات التسويقية تُضاف لاحقاً من
    <a href="{{ route('subscription-plans.index') }}" class="font-semibold underline">خطط الاشتراك</a>.
</x-admin.alert>

<x-admin.validation-errors title="تعذر إنشاء المسار:" />

<x-admin.card>
    <x-admin.section-heading
        title="إنشاء مسار عضوية"
        description="مثال: أساسي، مميز، VIP — بدون سعر أو مدة بيع."
    />

    <form action="{{ route('membership-types.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <x-admin.label for="name" value="اسم المسار *" />
                <x-admin.input type="text" name="name" id="name" :value="old('name')" required placeholder="مثال: عضوية مميزة" />
                <x-admin.field-error name="name" />
            </div>

            <div>
                <x-admin.label for="sort_order" value="ترتيب العرض" />
                <x-admin.input type="number" name="sort_order" id="sort_order" min="0" :value="old('sort_order', 0)" />
                <x-admin.field-error name="sort_order" />
            </div>
        </div>

        <div>
            <x-admin.label for="description" value="وصف المسار" />
            <x-admin.textarea name="description" id="description" rows="3" placeholder="وصف مختصر لمستوى الوصول المرتبط بهذا المسار">{{ old('description') }}</x-admin.textarea>
            <x-admin.field-error name="description" />
        </div>

        <label class="flex items-start gap-3">
            <input
                type="checkbox"
                name="is_active"
                id="is_active"
                value="1"
                class="mt-1 rounded border-tremor-border text-tremor-brand shadow-tremor-input focus:border-tremor-brand focus:ring-tremor-brand"
                @checked(old('is_active', true))
            >
            <span class="text-sm text-tremor-content-emphasis">
                <span class="font-medium">تفعيل المسار</span>
                <span class="mt-0.5 block text-tremor-content">يظهر في خيارات استهداف المحتوى وربط خطط الاشتراك.</span>
            </span>
        </label>

        <x-admin.form-actions
            :cancel-href="route('membership-types.index')"
            submit-label="حفظ المسار ثم إضافة خطة"
        />
    </form>
</x-admin.card>
@endsection
