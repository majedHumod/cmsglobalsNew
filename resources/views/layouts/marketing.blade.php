@php
    $marketingUrl = rtrim((string) ($marketingUrl ?? config('platform.marketing_url', 'https://etoscoach.com')), '/');
    $appUrl = rtrim((string) ($appUrl ?? config('platform.app_url', 'https://app.etoscoach.com')), '/');
    $assetBase = rtrim($appUrl, '/').'/marketing/assets';
    $assetVer = '7';
    $pageTitle = $pageTitle ?? 'EtosCoach';
@endphp
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $pageTitle }} | EtosCoach</title>
    <meta name="description" content="اشتراك وتجديد منصّة EtosCoach للمدربين والأندية." />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="icon" type="image/png" href="{{ $assetBase }}/img/logo.png?v={{ $assetVer }}" />
    <link rel="stylesheet" href="{{ $assetBase }}/css/styles.css?v={{ $assetVer }}" />
    <link rel="stylesheet" href="{{ $assetBase }}/css/billing.css?v={{ $assetVer }}" />
    <script>document.documentElement.classList.add('platform-session-pending');</script>
    @stack('head')
</head>
<body>
    <header class="site-header">
        <div class="container">
            <nav class="nav">
                <div class="brand">
                    <a href="{{ $marketingUrl }}">
                        <img class="logo" src="{{ $assetBase }}/img/logo.png?v={{ $assetVer }}" alt="EtosCoach logo" />
                    </a>
                </div>
                <button class="menu-toggle" aria-label="فتح القائمة" aria-expanded="false">
                    <svg class="icon-menu" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <path d="M4 7h16M4 12h16M4 17h16" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <svg class="icon-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <path d="M6 6l12 12M18 6l-12 12" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
                <div class="nav-links">
                    <a href="{{ $marketingUrl }}/#about">عن المنصة</a>
                    <a href="{{ $marketingUrl }}/#features">المزايا</a>
                    <a href="{{ $marketingUrl }}/#pricing">الأسعار</a>
                    <a href="{{ $marketingUrl }}/#faq">الأسئلة الشائعة</a>
                    <div id="platform-account" class="platform-account" aria-live="polite"></div>
                </div>
            </nav>
        </div>
    </header>

    <main class="billing-page">
        <div class="container">
            @yield('content')
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="inline-links" style="justify-content: space-between;">
                <div>&copy; {{ date('Y') }} EtosCoach</div>
                <div class="inline-links">
                    <a href="{{ $marketingUrl }}/privacy.html">سياسة الخصوصية</a>
                    <span>&bull;</span>
                    <a href="{{ $marketingUrl }}/refund.html">الاسترجاع والاسترداد</a>
                    <span>&bull;</span>
                    <a href="{{ $marketingUrl }}/#pricing">الأسعار</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="{{ $assetBase }}/js/main.js?v={{ $assetVer }}"></script>
    <script src="{{ $assetBase }}/js/platform-account.js?v={{ $assetVer }}"></script>
    @stack('scripts')
</body>
</html>
