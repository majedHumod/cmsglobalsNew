<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'communication_webhook' => [
        'secret' => env('COMMUNICATION_WEBHOOK_SECRET', ''),
    ],

    'paylink' => [
        'base_url' => env('PAYLINK_BASE_URL', 'https://restapi.paylink.sa'),
        'api_id' => env('PAYLINK_API_ID'),
        'secret_key' => env('PAYLINK_SECRET_KEY'),
        'persist_token' => (bool) env('PAYLINK_PERSIST_TOKEN', true),
        'webhook_secret' => env('PAYLINK_WEBHOOK_SECRET'),
        'callback_url' => env('PAYLINK_CALLBACK_URL'),
        'cancel_url' => env('PAYLINK_CANCEL_URL'),
        'webhook_url' => env('PAYLINK_WEBHOOK_URL'),
        'currency' => env('PAYLINK_CURRENCY', 'SAR'),
        'supported_card_brands' => array_values(array_filter(array_map('trim', explode(',', (string) env('PAYLINK_SUPPORTED_CARD_BRANDS', 'mada,visaMastercard,stcpay'))))),
    ],

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_AUTH_TOKEN'),
        'sms_from' => env('TWILIO_PHONE_NUMBER'),
    ],

    'whatsapp' => [
        // log = write OTP to laravel.log (local). twilio = send via WhatsApp API.
        'driver' => env('WHATSAPP_DRIVER', 'log'),
        'from' => env('TWILIO_WHATSAPP_FROM', 'whatsapp:+14155238886'),
        'otp_ttl' => (int) env('WHATSAPP_OTP_TTL', 300),
        'otp_resend_seconds' => (int) env('WHATSAPP_OTP_RESEND_SECONDS', 60),
        // When true, API returns debug_code in response (local testing only).
        'otp_debug' => (bool) env('WHATSAPP_OTP_DEBUG', false),
    ],

];
