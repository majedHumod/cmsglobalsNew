<?php

namespace App\Http\Middleware;

use App\Support\LegacyAdminFilamentMap;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Send admin/coach GET traffic from legacy Blade CMS routes to Filament /admin-cms.
 */
class RedirectLegacyAdminToFilament
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return $next($request);
        }

        if ($request->ajax() || $request->expectsJson()) {
            return $next($request);
        }

        // Already on Filament / client app / livewire — never bounce.
        if ($request->is(
            'admin-cms',
            'admin-cms/*',
            'client',
            'client/*',
            'livewire/*',
            'filament/*'
        )) {
            return $next($request);
        }

        $user = $request->user();
        if (! $user || ! $user->hasAnyRole(['admin', 'coach'])) {
            return $next($request);
        }

        $target = LegacyAdminFilamentMap::urlForRequest($request);
        if (! $target) {
            return $next($request);
        }

        // Avoid redirect loops if somehow mapped onto the same path.
        if ($request->getRequestUri() === $target || $request->path() === ltrim($target, '/')) {
            return $next($request);
        }

        return redirect()->to($target);
    }
}
