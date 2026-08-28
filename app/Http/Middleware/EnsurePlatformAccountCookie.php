<?php

namespace App\Http\Middleware;

use App\Services\Platform\PlatformAccountCookie;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlatformAccountCookie
{
    public function __construct(private readonly PlatformAccountCookie $cookie)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $tenant = $request->attributes->get('tenant');
        $user = $request->user();

        if (! $tenant || ! $user || ! $user->hasAnyRole(['admin', 'coach'])) {
            return $response;
        }

        $existing = $this->cookie->read($request);
        $email = strtolower((string) $user->email);

        if (
            $existing
            && (int) ($existing['tenant_id'] ?? 0) === (int) $tenant->id
            && strtolower((string) ($existing['email'] ?? '')) === $email
        ) {
            return $response;
        }

        return $response->withCookie($this->cookie->put(
            $tenant,
            $email,
            (string) ($user->name ?: $tenant->name)
        ));
    }
}
