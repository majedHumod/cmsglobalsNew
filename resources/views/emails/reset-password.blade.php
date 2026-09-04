<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $siteName }} — إعادة تعيين كلمة المرور</title>
</head>
<body style="margin:0;padding:0;background:#fafafa;direction:rtl;text-align:right;font-family:Tahoma,Arial,sans-serif;color:#52525b;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" dir="rtl" style="background:#fafafa;direction:rtl;">
        <tr>
            <td align="center" style="padding:24px 12px;">
                <p style="margin:0 0 16px;font-size:19px;font-weight:bold;color:#18181b;text-align:center;direction:rtl;font-family:Tahoma,Arial,sans-serif;">
                    {{ $siteName }}
                </p>

                <table width="570" cellpadding="0" cellspacing="0" role="presentation" dir="rtl" style="max-width:570px;width:100%;background:#ffffff;border:1px solid #e4e4e7;border-radius:4px;box-shadow:0 1px 3px rgba(0,0,0,.1);direction:rtl;">
                    <tr>
                        <td dir="rtl" align="right" style="padding:32px;direction:rtl;text-align:right;font-family:Tahoma,Arial,sans-serif;">
                            <h1 style="margin:0 0 16px;font-size:18px;font-weight:bold;color:#18181b;direction:rtl;text-align:right;">
                                مرحباً!
                            </h1>

                            <p style="margin:0 0 12px;font-size:16px;line-height:1.6;direction:rtl;text-align:right;">
                                تلقّينا طلباً لإعادة تعيين كلمة المرور لـ {{ $accountLabel ?? ('حسابك في '.$siteName) }}.
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:24px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $url }}" target="_blank" rel="noopener"
                                           style="display:inline-block;background:#18181b;color:#ffffff;text-decoration:none;padding:10px 18px;border-radius:4px;font-size:15px;font-family:Tahoma,Arial,sans-serif;">
                                            تعيين كلمة المرور
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 12px;font-size:16px;line-height:1.6;direction:rtl;text-align:right;">
                                رابط إعادة التعيين صالح لمدة {{ $expireMinutes }} دقيقة.
                            </p>

                            <p style="margin:0 0 16px;font-size:16px;line-height:1.6;direction:rtl;text-align:right;">
                                إذا لم تطلب إعادة تعيين كلمة المرور، يمكنك تجاهل هذا البريد.
                            </p>

                            <p style="margin:0;font-size:16px;line-height:1.6;direction:rtl;text-align:right;">
                                مع التحية،<br>
                                {{ $siteName }}
                            </p>

                            <hr style="border:none;border-top:1px solid #e4e4e7;margin:24px 0;">

                            <p style="margin:0;font-size:14px;line-height:1.6;color:#71717a;direction:rtl;text-align:right;">
                                إذا واجهت مشكلة في الضغط على الزر، انسخ الرابط التالي والصقه في المتصفح:
                                <br>
                                <a href="{{ $url }}" style="color:#18181b;word-break:break-all;">{{ $url }}</a>
                            </p>
                        </td>
                    </tr>
                </table>

                <p style="margin:24px 0 0;font-size:12px;color:#a1a1aa;text-align:center;direction:rtl;">
                    © {{ date('Y') }} {{ $siteName }}. جميع الحقوق محفوظة.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
