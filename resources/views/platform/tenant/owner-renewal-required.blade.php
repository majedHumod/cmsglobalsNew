@extends('layouts.marketing', [
    'pageTitle' => 'تجديد اشتراك النادي',
    'marketingUrl' => $marketingUrl,
])

@section('content')
    <div class="billing-hero">
        <div class="eyebrow">للمدرب / إدارة النادي</div>
        <h1>يتطلب تجديد اشتراك المنصّة</h1>
        <p class="billing-lead">
            هذه الرسالة موجّهة لمالك النادي أو المدرب المسؤول عن الاشتراك — وليست للمتدربين.
            المحتوى والبيانات محفوظة ولن تُحذف.
        </p>
    </div>

    <div class="billing-panel">
        <div class="status-banner {{ ($accessStatus ?? '') === 'grace' ? 'warning' : 'danger' }}">
            <span class="status-pill {{ ($accessStatus ?? '') === 'grace' ? 'grace' : 'suspended' }}">
                {{ ($accessStatus ?? '') === 'grace' ? 'فترة سماح' : 'اشتراك منتهٍ' }}
            </span>
            <p style="margin:10px 0 0">{{ $ownerMessage }}</p>
        </div>

        <div class="account-summary">
            @if($tenant)
                <div><strong>النادي:</strong> {{ $tenant->name }}</div>
            @endif
            @if(!empty($subscriptionEndsAt))
                <div><strong>تاريخ انتهاء الاشتراك:</strong>
                    {{ $subscriptionEndsAt->timezone(config('app.timezone'))->locale(app()->getLocale())->translatedFormat('l j F Y') }}
                </div>
            @endif
            @if(!empty($graceEndsAt) && ($accessStatus ?? '') === 'grace')
                <div><strong>فترة السماح حتى:</strong>
                    {{ $graceEndsAt->timezone(config('app.timezone'))->locale(app()->getLocale())->translatedFormat('l j F Y') }}
                </div>
            @endif
        </div>

        <div class="form-actions">
            <a href="{{ $subscribeUrl }}" class="btn primary">تجديد اشتراك النادي</a>
            <a href="{{ $marketingUrl }}" class="btn">الموقع الرئيسي</a>
        </div>
    </div>
@endsection
