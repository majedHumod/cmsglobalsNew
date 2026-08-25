<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'plans'],
    'allowed_methods' => ['*'],
    'allowed_origins' => array_values(array_filter([
        env('PLATFORM_MARKETING_URL', 'https://etoscoach.com'),
        'https://etoscoach.com',
        'https://www.etoscoach.com',
        'http://localhost:5173',
        'http://127.0.0.1:5500',
    ])),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
