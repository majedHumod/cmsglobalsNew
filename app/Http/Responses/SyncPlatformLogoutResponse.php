<?php

namespace App\Http\Responses;

use App\Services\Platform\PlatformAccountCookie;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

class SyncPlatformLogoutResponse implements LogoutResponseContract
{
    public function __construct(private readonly PlatformAccountCookie $cookie)
    {
    }

    public function toResponse($request)
    {
        $redirect = redirect()->to($this->safeRedirect($request->query('redirect')));

        if ($request->attributes->get('tenant')) {
            $redirect->withCookie($this->cookie->forget());
        }

        return $redirect;
    }

    private function safeRedirect(?string $url): string
    {
        $fallback = '/';
        if (! $url) {
            return $fallback;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $allowed = array_map('strtolower', config('platform.hosts', []));
        $marketing = strtolower((string) parse_url((string) config('platform.marketing_url', 'https://etoscoach.com'), PHP_URL_HOST));

        if ($host === $marketing || in_array($host, $allowed, true) || Str::endsWith($host, '.'.config('app.domain'))) {
            return $url;
        }

        return $fallback;
    }
}
