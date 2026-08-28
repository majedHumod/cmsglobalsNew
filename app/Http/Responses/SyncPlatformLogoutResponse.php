<?php

namespace App\Http\Responses;

use App\Services\Platform\PlatformAccountCookie;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Symfony\Component\HttpFoundation\Response;

class SyncPlatformLogoutResponse implements LogoutResponseContract
{
    public function __construct(private readonly PlatformAccountCookie $cookie)
    {
    }

    public function toResponse(Request $request): Response
    {
        $redirect = redirect('/');

        if ($request->attributes->get('tenant')) {
            $redirect->withCookie($this->cookie->forget());
        }

        return $redirect;
    }
}
