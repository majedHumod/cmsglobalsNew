@extends('layouts.admin')

@section('title', 'إضافة خطة اشتراك')

@section('header', 'إضافة خطة اشتراك')

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
        <span class="text-sm font-medium text-gray-500">إضافة</span>
    </div>
</li>
@endsection

@section('header_actions')
    <x-admin.button :href="route('subscription-plans.index')" variant="secondary">العودة للقائمة</x-admin.button>
@endsection

@section('content')
<x-admin.validation-errors title="تعذر إنشاء الخطة:" />

<x-admin.card>
    <x-admin.section-heading
        title="بيانات العرض التجاري"
        description="حدد المسار والسعر والمدة والمزايا التي تظهر للعميل."
    />

    <form action="{{ route('subscription-plans.store') }}" method="POST" class="space-y-6">
        @csrf
        @include('subscription-plans.form-fields')
        <x-admin.form-actions
            :cancel-href="route('subscription-plans.index')"
            submit-label="حفظ الخطة"
        />
    </form>
</x-admin.card>
@endsection
