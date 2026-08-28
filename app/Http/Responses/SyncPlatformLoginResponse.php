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
        $redirect = redirect()->intended(config('fortify.home', '/dashboard'));

        $tenant = $request->attributes->get('tenant');
        $user = $request->user();

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
