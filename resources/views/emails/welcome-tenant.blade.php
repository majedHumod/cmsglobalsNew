<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>مرحباً بك في EtosCoach</title>
</head>
<body style="font-family: Tahoma, Arial, sans-serif; background:#f8fafc; padding:24px; color:#111827;">
    <div style="max-width:640px; margin:0 auto; background:#ffffff; border:1px solid #e5e7eb; border-radius:8px; padding:24px;">
        <h1 style="margin-top:0; font-size:20px;">مرحباً {{ $tenantName }} 👋</h1>
        <p>تم إنشاء منصتك بنجاح على EtosCoach.</p>

        <p>
            رابط الدخول إلى منصتك:
            <a href="{{ $tenantDomainUrl }}" target="_blank" style="color:#0ea5e9; text-decoration:none;">
                {{ $tenantDomainUrl }}
            </a>
        </p>

        @if($plan)
            <p>تفاصيل خطتك: {{ $plan['name'] ?? $plan['code'] ?? '—' }} ({{ $plan['interval'] ?? '' }})</p>
        @endif

        @if($passwordResetUrl)
            <p>لإنشاء كلمة مرورك الأولى، استخدم الرابط التالي (صالح لفترة محدودة):</p>
            <p>
                <a href="{{ $passwordResetUrl }}" target="_blank" style="display:inline-block; background:#0ea5e9; color:#ffffff; padding:10px 16px; border-radius:6px; text-decoration:none;">
                    تعيين كلمة المرور
                </a>
            </p>
        @endif

        <p>البريد المسجل: {{ $contactEmail }}</p>

        <hr style="border:none; border-top:1px solid #e5e7eb; margin:16px 0;">
        <p style="font-size:12px; color:#6b7280;">
            إذا لم تطلب هذه العملية، يرجى تجاهل هذا البريد.
        </p>
    </div>
</body>
</html>

