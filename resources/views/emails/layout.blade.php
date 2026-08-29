<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'EtosCoach')</title>
</head>
<body style="font-family: Tahoma, Arial, sans-serif; background:#f8fafc; padding:24px; color:#111827; direction:rtl; text-align:right;">
    <div style="max-width:640px; margin:0 auto; background:#ffffff; border:1px solid #e5e7eb; border-radius:8px; padding:24px;">
        @if(!empty($siteName))
            <p style="margin:0 0 16px; font-size:13px; color:#64748b;">{{ $siteName }}</p>
        @endif

        @yield('content')

        <hr style="border:none; border-top:1px solid #e5e7eb; margin:16px 0;">
        <p style="font-size:12px; color:#6b7280; margin:0;">
            @yield('footer', 'إذا لم تطلب هذه العملية، يرجى تجاهل هذا البريد.')
        </p>
    </div>
</body>
</html>
