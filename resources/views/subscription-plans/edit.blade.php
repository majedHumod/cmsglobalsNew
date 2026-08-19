@extends('layouts.admin')

@section('title', 'تعديل خطة الاشتراك')

@section('header', 'تعديل خطة الاشتراك')

@section('breadcrumbs')
<li>
    <div class="flex items-center">
        <svg class="mx-1 h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
        <a href="{{ route('subscription-plans.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-800">خطط الاشتراك</a>
    </div>
</li>
<li>
    <div class="flex items-center">
        <svg class="mx-1 h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
        <span class="text-sm font-medium text-gray-500">تعديل</span>
    </div>
</li>
@endsection

@section('header_actions')
    <x-admin.button :href="route('subscription-plans.index')" variant="secondary">العودة للقائمة</x-admin.button>
@endsection

@section('content')
<x-admin.validation-errors title="تعذر حفظ التعديلات:" />

<x-admin.card>
    <x-admin.section-heading
        title="تعديل العرض التجاري"
        description="التغييرات تظهر للعملاء في الصفحة الرئيسية وصفحة الاشتراكات."
    />

    <form action="{{ route('subscription-plans.update', $subscriptionPlan) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        @include('subscription-plans.form-fields')
        <x-admin.form-actions
            :cancel-href="route('subscription-plans.index')"
            submit-label="حفظ التعديلات"
        />
    </form>
</x-admin.card>
@endsection
