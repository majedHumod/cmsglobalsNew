<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Services\Platform\PlatformAccountCookie;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PlatformSignOutController extends Controller
{
    public function __construct(private readonly PlatformAccountCookie $cookie)
    {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $response = redirect()->to($this->safeRedirect($request->query('redirect')));

        return $response->withCookie($this->cookie->forget());
    }

    private function safeRedirect(?string $url): string
    {
        $fallback = rtrim((string) config('platform.marketing_url', 'https://etoscoach.com'), '/') ?: 'https://etoscoach.com';
        if (! $url) {
            return $fallback;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $allowed = array_map('strtolower', config('platform.hosts', []));
        $marketing = strtolower((string) parse_url($fallback, PHP_URL_HOST));

        if ($host === $marketing || in_array($host, $allowed, true) || Str::endsWith($host, '.'.config('app.domain'))) {
            return $url;
        }

        return $fallback;
    }
}
