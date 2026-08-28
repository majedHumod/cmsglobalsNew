<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $tenant->name ?? 'النادي' }} | غير متاح مؤقتاً</title>
    <style>
        :root { --brand:#1A8E9A; --ink:#4A5D68; --muted:#6f838c; --bg:#f7faf9; }
        * { box-sizing:border-box; }
        body { margin:0; font-family:system-ui,sans-serif; background:var(--bg); color:var(--ink); direction:rtl; }
        .wrap { min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px; }
        .card { max-width:520px; width:100%; background:#fff; border:1px solid #e6ece9; border-radius:16px; padding:28px; text-align:center; box-shadow:0 10px 30px rgba(16,24,40,.08); }
        h1 { margin:0 0 10px; font-size:24px; }
        p { color:var(--muted); line-height:1.8; margin:0 0 12px; }
        .club { font-weight:800; color:var(--brand); }
        .note { font-size:13px; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <h1>الموقع غير متاح مؤقتاً</h1>
            <p>
                <span class="club">{{ $tenant->name ?? 'هذا النادي' }}</span>
                {{ $publicMessage }}
            </p>
            <p class="note">إذا كنت مدرباً أو مسؤولاً عن النادي، سجّل الدخول من رابط إدارة النادي لتجديد الاشتراك.</p>
        </div>
    </div>
</body>
</html>
