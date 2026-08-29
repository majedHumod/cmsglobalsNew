@extends('emails.layout')

@section('title', 'مرحباً بك في '.$tenantName)

@section('content')
    <h1 style="margin-top:0; font-size:20px;">مرحباً {{ $contactName ?? $tenantName }} 👋</h1>

    <p style="margin:0 0 12px;">
        تم إنشاء منصة <strong>{{ $tenantName }}</strong> بنجاح. يمكنك الآن إدارة ناديك أو برنامجك التدريبي من لوحة التحكم.
    </p>

    <p style="margin:0 0 12px;">
        رابط منصتك:
        <a href="{{ $tenantDomainUrl }}" target="_blank" style="color:#0ea5e9; text-decoration:none;">
            {{ $tenantDomainUrl }}
        </a>
    </p>

    @if($plan)
        <p style="margin:0 0 12px;">
            الخطة: {{ $plan['name'] ?? $plan['code'] ?? '—' }}
            @if(!empty($plan['interval']))
                ({{ $plan['interval'] === 'yearly' ? 'سنوي' : 'شهري' }})
            @endif
        </p>
    @endif

    @if($passwordResetUrl)
        <p style="margin:0 0 12px;">لإنشاء كلمة مرورك الأولى، استخدم الزر التالي (صالح لفترة محدودة):</p>
        <p style="margin:0 0 16px;">
            <a href="{{ $passwordResetUrl }}" target="_blank" style="display:inline-block; background:#0ea5e9; color:#ffffff; padding:10px 16px; border-radius:6px; text-decoration:none;">
                تعيين كلمة المرور
            </a>
        </p>
    @endif

    <p style="margin:0;">البريد المسجل: {{ $contactEmail }}</p>
@endsection

@section('footer')
    هذه الرسالة أُرسلت لأنك اشتركت في EtosCoach. إذا لم تطلب إنشاء منصة، يرجى تجاهل هذا البريد.
@endsection
