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

    // Who gets operational alerts (failed provisioning, pool running low, stuck payments...).
    // Falls back to PLATFORM_OWNER_EMAILS when empty.
    'alert_emails' => array_values(array_filter(array_map(
        static fn (string $email) => strtolower(trim($email)),
        explode(',', (string) env('PLATFORM_ALERT_EMAILS', ''))
    ))),

    'signup' => [
        // Require the buyer to prove ownership of the email (6-digit code) before a new signup invoice is created.
        'verify_email' => filter_var(env('PLATFORM_SIGNUP_VERIFY_EMAIL', true), FILTER_VALIDATE_BOOLEAN),
        // true = one mobile number per club (hard block). false = mobile duplicates are allowed.
        'unique_mobile' => filter_var(env('PLATFORM_SIGNUP_UNIQUE_MOBILE', false), FILTER_VALIDATE_BOOLEAN),
        // Calling code used to normalise local numbers such as 05xxxxxxxx to E.164.
        'default_country_code' => (string) env('PLATFORM_SIGNUP_COUNTRY_CODE', '966'),
        // How long a pending checkout holds the subdomain + email before it is released.
        'reservation_minutes' => (int) env('PLATFORM_SIGNUP_RESERVATION_MINUTES', 120),
        // Refuse new signups when there is no prepared tenant database left (avoids "paid but nothing to provision").
        'require_free_database' => filter_var(
            env('PLATFORM_SIGNUP_REQUIRE_FREE_DATABASE', env('APP_ENV', 'production') !== 'local'),
            FILTER_VALIDATE_BOOLEAN
        ),
        // Alert when free prepared databases (minus those already reserved) drop to this number or fewer.
        'min_free_databases' => (int) env('PLATFORM_POOL_MIN_FREE', 3),
        // A paid signup invoice without a tenant after this many minutes is considered stuck.
        'stuck_provision_minutes' => (int) env('PLATFORM_STUCK_PROVISION_MINUTES', 15),
    ],
];
