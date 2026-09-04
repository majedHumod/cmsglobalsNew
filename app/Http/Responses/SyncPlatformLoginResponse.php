<?php

namespace App\Http\Responses;

use App\Services\Platform\PlatformAccountCookie;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class SyncPlatformLoginResponse implements LoginResponseContract
{
    public function __construct(private readonly PlatformAccountCookie $cookie)
    {
    }

    public function toResponse($request)
    {
        $user = $request->user();
        $home = config('fortify.home', '/dashboard');

        if ($user && $user->hasAnyRole(['admin', 'coach'])) {
            $home = \App\Support\LegacyAdminFilamentMap::PANEL;
        }

        $redirect = redirect()->intended($home);

        $tenant = $request->attributes->get('tenant');

        if ($tenant && $user && $user->hasAnyRole(['admin', 'coach'])) {
            $redirect->withCookie($this->cookie->put(
                $tenant,
                (string) $user->email,
                (string) ($user->name ?: $tenant->name)
            ));
        }

        return $redirect;
    }
}
