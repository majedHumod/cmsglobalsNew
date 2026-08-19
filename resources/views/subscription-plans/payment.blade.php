@extends('layouts.public-page')

@section('title', 'دفع الاشتراك')

@section('content')
<section class="py-16">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-4">إتمام الدفع</h1>
            <p class="text-gray-600 mb-2">
                الخطة: <strong>{{ $membership->subscriptionPlan->name }}</strong>
            </p>
            <div class="mb-6">
                <x-subscription-plan-price :plan="$membership->subscriptionPlan" size="sm" />
                <p class="mt-1 text-xs text-gray-500">المبلغ المستحق للدفع هو سعر البيع فقط.</p>
            </div>

            <div class="rounded-lg bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800 mb-6">
                تم تجهيز عملية الدفع وربطها بسجل الاشتراك. بعد إتمام الدفع يمكنك تأكيد النجاح من الزر التالي.
            </div>

            <div class="flex gap-3">
                <a href="{{ route('subscription-plans.success', $membership) }}" class="rounded-md bg-indigo-600 px-4 py-3 text-white hover:bg-indigo-700">
                    تأكيد نجاح الدفع
                </a>
                <a href="{{ route('subscription-plans.public') }}" class="rounded-md border border-gray-300 px-4 py-3 text-gray-700">
                    العودة للخطط
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
