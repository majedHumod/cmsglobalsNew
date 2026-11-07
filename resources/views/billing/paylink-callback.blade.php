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

        @if($status === 'paid' && ! $isRenewal && $activationStatus !== 'signup_conflict')
            <div class="welcome-next" style="margin-top:18px;padding:18px 20px;border:1px solid #d0d5dd;border-radius:12px;background:#f9fafb">
                <h2 style="margin:0 0 6px;font-size:20px">أهلاً بك في EtosCoach 🎉</h2>
                <p style="margin:0 0 14px">شكراً لانضمامك! استلمنا دفعتك، ونجهّز الآن مساحة ناديك. إليك ما سيحدث بعد ذلك:</p>
                <ol style="margin:0;padding-inline-start:22px;line-height:2">
                    <li>
                        <strong>التجهيز التلقائي (من دقيقة إلى بضع دقائق):</strong>
                        سننشئ لك مساحة النادي
                        @if($signupSlug)
                            على الرابط <strong dir="ltr">{{ $signupSlug }}.{{ $domain }}</strong>
                        @endif
                        دون أي إجراء منك.
                    </li>
                    <li>
                        <strong>سيصلك بريد إلكتروني</strong>
                        @if($signupEmail)
                            على <strong dir="ltr">{{ $signupEmail }}</strong>
                        @endif
                        يحتوي على رسالة ترحيب، ورابط ناديك، ورابط <strong>تعيين كلمة المرور</strong>، وتفاصيل خطتك.
                    </li>
                    <li><strong>افتح الرسالة</strong> واضغط «تعيين كلمة المرور»، ثم سجّل الدخول وابدأ بإعداد ناديك.</li>
                </ol>
                <p class="note" style="margin:14px 0 0">
                    لم تصلك الرسالة خلال 10 دقائق؟ تفقّد مجلد الرسائل غير المرغوبة (Spam/Junk)، وإن لم تجدها فتواصل مع الدعم
                    @if($invoice) وزوّدنا برقم الطلب: <strong dir="ltr">{{ $invoice->number }}</strong>@endif.
                </p>
            </div>
        @endif

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
