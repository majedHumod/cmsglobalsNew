<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="vapid-public-key" content="{{ env('VAPID_PUBLIC_KEY', '') }}">
        <link rel="manifest" href="/manifest.json">
        <meta name="theme-color" content="#4f46e5">

        <title>{{ \App\Models\SiteSetting::get('site_name', config('app.name', 'Laravel')) }} - @yield('title', '')</title>

        <!-- Favicon -->
        @php
            $siteFavicon = \App\Models\SiteSetting::get('site_favicon');
        @endphp
        @if($siteFavicon)
            <link rel="icon" href="{{ Storage::url($siteFavicon) }}" type="image/x-icon">
        @endif

        <!-- Fixed UI font for authenticated app shell (not public brand font) -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=tajawal:400,500,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles

        @php
            $primaryColor = \App\Models\SiteSetting::get('primary_color', '#6366f1');
            $secondaryColor = \App\Models\SiteSetting::get('secondary_color', '#10b981');
        @endphp
        <style>
            :root {
                --primary-color: {{ $primaryColor }};
                --secondary-color: {{ $secondaryColor }};
            }

            html, body, body.font-sans, .font-sans {
                font-family: 'Tajawal', Tahoma, sans-serif !important;
            }

            .bg-primary { background-color: var(--primary-color); }
            .text-primary { color: var(--primary-color); }
            .border-primary { border-color: var(--primary-color); }
            .bg-secondary { background-color: var(--secondary-color); }
            .text-secondary { color: var(--secondary-color); }
            .border-secondary { border-color: var(--secondary-color); }

            div.ms-3.relative {
                z-index: 50 !important;
            }
        </style>
    </head>
    <body class="font-sans antialiased" data-push-prompt="{{ auth()->check() ? '1' : '0' }}">
        <x-banner />

        <div class="min-h-screen bg-gray-100">
            <!-- Site Header with Contact Info and Social Media -->
            <x-site-header />

            <!-- Navigation Menu -->
            @livewire('navigation-menu')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                @yield('content')
                @isset($slot)
                    {{ $slot }}
                @endisset
            </main>

            <!-- Site Footer with Contact Info and Social Media -->
            @include('layouts.footer')
        </div>

        @stack('modals')

        @livewireScripts
    </body>
</html>
