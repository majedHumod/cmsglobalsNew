<?php

return [
    'app_url' => rtrim((string) env('PLATFORM_APP_URL', env('APP_URL', 'https://app.etoscoach.com')), '/'),
    'marketing_url' => rtrim((string) env('PLATFORM_MARKETING_URL', 'https://etoscoach.com'), '/'),
    'cookie' => env('PLATFORM_COOKIE', 'etos_platform'),
    'cookie_domain' => env('PLATFORM_COOKIE_DOMAIN', '.etoscoach.com'),

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
