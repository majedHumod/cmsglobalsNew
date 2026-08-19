@extends('layouts.public-page')

@section('title', 'تم تفعيل الاشتراك')

@section('content')
<section class="py-16">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-4">تم تفعيل اشتراكك</h1>
            <div class="space-y-3 text-gray-700">
                <p>الخطة: <strong>{{ $membership->subscriptionPlan->name }}</strong></p>
                <p>المسار التدريبي: <strong>{{ $membership->subscriptionPlan->membershipType?->name }}</strong></p>
                <p>تاريخ البداية: <strong>{{ optional($membership->starts_at)->format('Y-m-d') }}</strong></p>
                <p>تاريخ الانتهاء: <strong>{{ optional($membership->expires_at)->format('Y-m-d') }}</strong></p>
            </div>

            <div class="mt-6 flex gap-3">
                <a href="{{ route('dashboard') }}" class="rounded-md bg-indigo-600 px-4 py-3 text-white hover:bg-indigo-700">الانتقال للوحة التحكم</a>
                <a href="{{ route('subscription-plans.public') }}" class="rounded-md border border-gray-300 px-4 py-3 text-gray-700">عرض الخطط</a>
            </div>
        </div>
    </div>
</section>
@endsection
