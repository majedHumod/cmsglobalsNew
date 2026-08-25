<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'plans', 'account/session'],
    'allowed_methods' => ['*'],
    'allowed_origins' => array_values(array_filter(array_map(
        static fn ($url) => $url !== '' ? rtrim((string) $url, '/') : null,
        [
            env('PLATFORM_MARKETING_URL'),
            'https://etoscoach.com',
            'https://www.etoscoach.com',
            'http://localhost:5173',
            'http://127.0.0.1:5500',
        ]
    ))),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
