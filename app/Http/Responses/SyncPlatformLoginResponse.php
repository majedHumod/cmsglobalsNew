<?php

namespace App\Http\Responses;

use App\Services\Platform\PlatformAccountCookie;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class SyncPlatformLoginResponse implements LoginResponseContract
{
    public function __construct(private readonly PlatformAccountCookie $cookie)
    {
    }

    public function toResponse(Request $request): Response
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
