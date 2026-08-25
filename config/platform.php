<?php

$appUrl = trim((string) env('PLATFORM_APP_URL', env('APP_URL', 'https://app.etoscoach.com')));
$marketingUrl = trim((string) env('PLATFORM_MARKETING_URL', 'https://etoscoach.com'));
$isLocal = env('APP_ENV', 'production') === 'local';

if (! $isLocal) {
    if ($appUrl === '' || str_contains($appUrl, 'localhost') || str_contains($appUrl, '127.0.0.1')) {
        $appUrl = 'https://app.etoscoach.com';
    }
    if ($marketingUrl === '' || str_contains($marketingUrl, 'localhost') || str_contains($marketingUrl, '127.0.0.1')) {
        $marketingUrl = 'https://etoscoach.com';
    }
}

$cookieDomain = trim((string) env('PLATFORM_COOKIE_DOMAIN', '.etoscoach.com'));
if (! $isLocal && $cookieDomain === '') {
    $cookieDomain = '.etoscoach.com';
}

return [
    'app_url' => rtrim($appUrl, '/'),
    'marketing_url' => rtrim($marketingUrl, '/'),
    'cookie' => env('PLATFORM_COOKIE', 'etos_platform') ?: 'etos_platform',
    'cookie_domain' => $cookieDomain,

    'hosts' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('PLATFORM_HOSTS', 'app.etoscoach.com,etoscoach.com,www.etoscoach.com'))
    ))),

    // Days after subscription period_end before workspace is suspended (content kept).
    'grace_days' => (int) env('PLATFORM_GRACE_DAYS', 14),
    // Days after period_end before login is limited to renewal only (still no delete).
    'archive_days' => (int) env('PLATFORM_ARCHIVE_DAYS', 90),

    'owner_emails' => array_values(array_filter(array_map(
        static fn (string $email) => strtolower(trim($email)),
        explode(',', (string) env('PLATFORM_OWNER_EMAILS', ''))
    ))),
    'owner_password' => (string) env('PLATFORM_OWNER_PASSWORD', ''),
];
