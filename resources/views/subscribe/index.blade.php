@extends('layouts.marketing', [
    'pageTitle' => match ($pageMode) {
        'renewal' => 'تجديد الاشتراك',
        'active' => 'حالة الاشتراك',
        default => 'الاشتراك',
    },
    'marketingUrl' => $marketingUrl,
    'appUrl' => $appUrl,
])

@section('content')
    <div class="billing-hero">
        @if($pageMode === 'renewal')
            <div class="eyebrow">تجديد الاشتراك</div>
            <h1>جدّد اشتراك {{ $tenant?->name ?? 'ناديك' }}</h1>
            <p class="billing-lead">اختر الخطة المناسبة وأكمل الدفع عبر Paylink. سيتم تمديد نفس حسابك دون إنشاء مركز جديد.</p>
        @elseif($pageMode === 'active')
            <div class="eyebrow">اشتراكك ساري</div>
            <h1>مرحباً، {{ $prefill['name'] ?: ($tenant?->name ?? 'بك') }}</h1>
            <p class="billing-lead">اشتراكك نشط حالياً. يمكنك الدخول إلى لوحة التحكم مباشرة دون الحاجة للتجديد.</p>
        @else
            <div class="eyebrow">ابدأ مع EtosCoach</div>
            <h1>أنشئ مركز التدريب الإلكتروني الخاص بك</h1>
            <p class="billing-lead">اختر الخطة، ثم أكمل بياناتك. سيتم تحويلك إلى Paylink للدفع، ولن يُفعَّل مركزك إلا بعد تأكيد السداد.</p>
        @endif
    </div>

    @if (session('status'))
        <div class="status-banner success">{{ session('status') }}</div>
    @endif

    @if($pageMode === 'active')
        <div class="billing-panel">
            <div class="status-banner success">
                <span class="status-pill active">اشتراك ساري</span>
                <p style="margin:10px 0 0">مساحة عملك متاحة بالكامل.</p>
            </div>

            <div class="account-summary">
                @if($tenant)
                    <div><strong>النادي:</strong> {{ $tenant->name }}</div>
                    <div><strong>الرابط:</strong> {{ $prefill['subdomain'] }}.{{ $domain }}</div>
                @endif
                @if($currentPlan)
                    <div><strong>الخطة الحالية:</strong> {{ $currentPlan->name }}
                        ({{ $currentPlan->interval === 'monthly' ? 'شهري' : 'سنوي' }})
                    </div>
                @endif
                @if($subscriptionEndsAt)
                    <div><strong>ينتهي في:</strong> {{ $subscriptionEndsAt->timezone(config('app.timezone'))->format('Y-m-d') }}</div>
                @endif
            </div>

            <div class="form-actions">
                <a href="{{ $handoffUrl }}" class="btn primary">الذهاب إلى لوحة التحكم</a>
                <a href="{{ $marketingUrl }}" class="btn">الموقع الرئيسي</a>
            </div>

            <p class="note" style="margin-top:16px">
                لا حاجة للتجديد الآن. عند اقتراب انتهاء الاشتراك ستظهر لك خيارات التجديد هنا تلقائياً.
            </p>
        </div>
    @elseif($pageMode === 'guest')
        <div class="billing-panel">
            <h2>هل لديك حساب بالفعل؟</h2>
            <p class="billing-lead" style="margin-bottom:0">
                إذا كنت تريد <strong>تجديد اشتراك</strong> نادٍ موجود، سجّل الدخول أولاً لعرض بياناتك وخيارات التجديد.
                أما إذا كنت <strong>تنشئ حساباً جديداً</strong>، يمكنك المتابعة بالأسفل دون تسجيل الدخول.
            </p>

            <div class="guest-steps">
                <div class="guest-step">
                    <h3>تجديد اشتراك موجود</h3>
                    <p>سجّل الدخول ببريد مالك النادي لتحميل بيانات التجديد تلقائياً.</p>
                    <a href="{{ $loginUrl }}" class="btn primary">تسجيل الدخول للتجديد</a>
                </div>
                <div class="guest-step">
                    <h3>اشتراك جديد</h3>
                    <p>أنشئ مركزاً جديداً على سب-دومين خاص بك. لا يلزم تسجيل الدخول مسبقاً.</p>
                    <a href="#subscribe-form" class="btn">متابعة كاشتراك جديد</a>
                </div>
            </div>
        </div>

        @include('subscribe.partials.checkout-form')
    @elseif($pageMode === 'renewal')
        @if($accessStatus === \App\Services\Platform\TenantAccessService::GRACE)
            <div class="status-banner warning">
                <span class="status-pill grace">فترة سماح</span>
                <p style="margin:10px 0 0">{{ $accessMessage }}</p>
                @if($graceEndsAt)
                    <p class="note" style="margin:8px 0 0">تنتهي فترة السماح في {{ $graceEndsAt->timezone(config('app.timezone'))->format('Y-m-d') }}.</p>
                @endif
            </div>
        @elseif(in_array($accessStatus, [\App\Services\Platform\TenantAccessService::SUSPENDED, \App\Services\Platform\TenantAccessService::ARCHIVED], true))
            <div class="status-banner danger">
                <span class="status-pill suspended">يتطلب تجديداً</span>
                <p style="margin:10px 0 0">{{ $accessMessage }}</p>
            </div>
        @endif

        <div class="billing-panel">
            <div class="account-summary">
                <div><strong>النادي:</strong> {{ $tenant->name }}</div>
                @if(!empty($prefill['email']))
                    <div><strong>البريد:</strong> {{ $prefill['email'] }}</div>
                @endif
                <div><strong>الرابط:</strong> {{ $prefill['subdomain'] }}.{{ $domain }}</div>
                @if($subscriptionEndsAt)
                    <div><strong>انتهى الاشتراك في:</strong> {{ $subscriptionEndsAt->timezone(config('app.timezone'))->format('Y-m-d') }}</div>
                @endif
            </div>
        </div>

        @include('subscribe.partials.checkout-form', ['renewal' => true])
    @else
        @include('subscribe.partials.checkout-form')
    @endif
@endsection
