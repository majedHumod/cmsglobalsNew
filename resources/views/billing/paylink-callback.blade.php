@extends('layouts.marketing', [
    'pageTitle' => 'حالة الدفع',
    'marketingUrl' => $marketingUrl,
])

@section('content')
    <div class="billing-hero">
        <div class="eyebrow">Paylink</div>
        <h1>حالة الدفع</h1>
    </div>

    <div class="billing-panel">
        @php
            $statusClasses = [
                'paid' => 'success',
                'canceled' => 'danger',
                'pending' => 'warning',
            ];
            $bannerClass = $statusClasses[$status] ?? 'info';
            $statusLabel = $status === 'paid'
                ? ($isRenewal ? 'تم التجديد' : 'تم الدفع')
                : ($status === 'canceled' ? 'تم الإلغاء' : 'قيد التحقق');
        @endphp

        <div class="status-banner {{ $bannerClass }}">
            <span class="status-pill {{ $status === 'paid' ? 'active' : ($status === 'canceled' ? 'suspended' : 'grace') }}">
                {{ $statusLabel }}
            </span>
            <p style="margin:10px 0 0">{{ $message }}</p>
        </div>

        <p class="note">إذا تأخر التفعيل، فسيتم التحقق آلياً من الفاتورة خلال دقيقة تقريباً حتى لو لم يصل webhook من Paylink.</p>

        @if($invoice)
            <div class="invoice-box" style="margin-top:16px">
                <div><strong>رقم الطلب:</strong> {{ $invoice->number ?? '-' }}</div>
                <div><strong>المبلغ:</strong> {{ number_format((float) $invoice->amount_due, 2) }} {{ $invoice->currency }}</div>
                <div><strong>الحالة الحالية:</strong> {{ $invoice->status }}</div>
            </div>
        @endif

        <div class="form-actions" style="margin-top:20px">
            @if($status === 'paid' && ($isRenewal || in_array($activationStatus, ['already_provisioned', 'renewed'], true)))
                <a href="{{ $handoffUrl }}" class="btn primary">الذهاب إلى لوحة التحكم</a>
                <a href="{{ $marketingUrl }}" class="btn">الموقع الرئيسي</a>
            @elseif($status === 'paid')
                <a href="{{ route('subscribe') }}" class="btn primary">عرض حالة الاشتراك</a>
                <a href="{{ $marketingUrl }}" class="btn">الموقع الرئيسي</a>
            @elseif($status === 'canceled')
                <a href="{{ route('subscribe') }}" class="btn primary">{{ $isRenewal ? 'إعادة محاولة التجديد' : 'العودة إلى الاشتراك' }}</a>
                <a href="{{ $marketingUrl }}" class="btn">الموقع الرئيسي</a>
            @else
                <a href="{{ route('subscribe') }}" class="btn">العودة إلى الاشتراك</a>
                <a href="{{ $marketingUrl }}" class="btn primary">الموقع الرئيسي</a>
            @endif
        </div>
    </div>
@endsection
