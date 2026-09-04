<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>مرحباً بك في {{ $platformName }}</title>
</head>
<body style="margin:0;padding:0;background:#fafafa;direction:rtl;text-align:right;font-family:Tahoma,Arial,sans-serif;color:#52525b;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" dir="rtl" style="background:#fafafa;direction:rtl;">
        <tr>
            <td align="center" style="padding:24px 12px;">
                <p style="margin:0 0 16px;font-size:19px;font-weight:bold;color:#18181b;text-align:center;direction:rtl;font-family:Tahoma,Arial,sans-serif;">
                    {{ $platformName }}
                </p>

                <table width="570" cellpadding="0" cellspacing="0" role="presentation" dir="rtl" style="max-width:570px;width:100%;background:#ffffff;border:1px solid #e4e4e7;border-radius:4px;box-shadow:0 1px 3px rgba(0,0,0,.1);direction:rtl;">
                    <tr>
                        <td dir="rtl" align="right" style="padding:32px;direction:rtl;text-align:right;font-family:Tahoma,Arial,sans-serif;">
                            <h1 style="margin:0 0 16px;font-size:18px;font-weight:bold;color:#18181b;direction:rtl;text-align:right;">
                                مرحباً {{ $contactName ?? $tenantName }}
                            </h1>

                            <p style="margin:0 0 12px;font-size:16px;line-height:1.6;direction:rtl;text-align:right;">
                                شكراً لاشتراكك في <strong>{{ $platformName }}</strong>.
                                تم إنشاء منصة ناديك <strong>{{ $tenantName }}</strong> بنجاح.
                            </p>

                            <p style="margin:0 0 12px;font-size:16px;line-height:1.6;direction:rtl;text-align:right;">
                                يمكنك الآن إدارة ناديك أو برنامجك التدريبي من لوحة التحكم.
                            </p>

                            <p style="margin:0 0 12px;font-size:16px;line-height:1.6;direction:rtl;text-align:right;">
                                <strong>رابط منصتك:</strong>
                                <a href="{{ $tenantDomainUrl }}" style="color:#18181b;word-break:break-all;">{{ $tenantDomainUrl }}</a>
                            </p>

                            @if($plan)
                                <p style="margin:0 0 12px;font-size:16px;line-height:1.6;direction:rtl;text-align:right;">
                                    <strong>الخطة:</strong>
                                    {{ $plan['name'] ?? $plan['code'] ?? '—' }}
                                    @if(!empty($plan['interval']))
                                        ({{ $plan['interval'] === 'yearly' ? 'سنوي' : 'شهري' }})
                                    @endif
                                </p>
                            @endif

                            @if($passwordResetUrl)
                                <p style="margin:0 0 20px;font-size:16px;line-height:1.6;direction:rtl;text-align:right;">
                                    لإنشاء كلمة مرورك الأولى، اضغط الزر أدناه (صالح لفترة محدودة):
                                </p>

                                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:0 0 20px;">
                                    <tr>
                                        <td align="center">
                                            <a href="{{ $passwordResetUrl }}" target="_blank" rel="noopener"
                                               style="display:inline-block;background:#18181b;color:#ffffff;text-decoration:none;padding:10px 18px;border-radius:4px;font-size:15px;font-family:Tahoma,Arial,sans-serif;">
                                                تعيين كلمة المرور
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            <p style="margin:0 0 16px;font-size:16px;line-height:1.6;direction:rtl;text-align:right;">
                                <strong>البريد المسجل:</strong> {{ $contactEmail }}
                            </p>

                            <p style="margin:0;font-size:16px;line-height:1.6;direction:rtl;text-align:right;">
                                مع التحية،<br>
                                فريق {{ $platformName }}
                            </p>
                        </td>
                    </tr>
                </table>

                <p style="margin:24px 0 0;font-size:12px;color:#a1a1aa;text-align:center;direction:rtl;">
                    © {{ date('Y') }} {{ $platformName }}. جميع الحقوق محفوظة.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
