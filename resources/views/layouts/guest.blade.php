<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="{{ \App\Support\Branding::primaryColor() }}">

        <title>{{ \App\Models\SiteSetting::get('site_name', config('app.name', 'Laravel')) }}</title>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @include('partials.brand-tokens')

        <!-- RTL Support -->
        <style>
            [dir="rtl"] .text-start {
                text-align: right !important;
            }
            [dir="rtl"] .text-end {
                text-align: left !important;
            }
            [dir="rtl"] .ms-2 {
                margin-left: 0;
                margin-right: 0.5rem;
            }
            [dir="rtl"] .ms-4 {
                margin-left: 0;
                margin-right: 1rem;
            }
            [dir="rtl"] .me-4 {
                margin-right: 0;
                margin-left: 1rem;
            }
        </style>

        <!-- Styles -->
        @livewireStyles
    </head>
    <body>
        <div class="font-sans text-gray-900 antialiased">
            {{ $slot }}
        </div>

        @livewireScripts
    </body>
</html>
