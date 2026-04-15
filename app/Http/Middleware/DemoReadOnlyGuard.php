<?php

namespace App\Http\Middleware;

use App\Services\TenantService;
use Illuminate\Support\Facades\Cache;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DemoReadOnlyGuard
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = TenantService::getTenant();
        if ($tenant) {
            $demoDomain = env('DEMO_TENANT_DOMAIN', 'demo.cmsglobals.test');
            $isDemo = ($tenant->domain === $demoDomain) || ($tenant->slug === 'demo');
            if ($isDemo) {
                // Simple rate limiting for demo: 300 requests / 5 minutes per IP
                $ip = $request->ip() ?: 'unknown';
                $key = "demo_rate:{$ip}";
                $count = (int) Cache::get($key, 0) + 1;
                Cache::put($key, $count, now()->addMinutes(5));
                if ($count > 300) {
                    if ($request->expectsJson()) {
                        return response()->json(['message' => 'Too Many Requests (demo)'], 429);
                    }
                    abort(429, 'Too Many Requests (demo)');
                }
                // Hide admin endpoints on demo
                if (str_starts_with($request->path(), 'admin')) {
                    abort(404);
                }
                // Block non-GET methods except a small whitelist
                $method = strtoupper($request->getMethod());
                $path = $request->path();
                $whitelist = [
                    // add read-only safe endpoints here if needed
                ];
                if ($method !== 'GET' && !$this->isWhitelisted($path, $whitelist)) {
                    if ($request->expectsJson()) {
                        return response()->json(['message' => 'Demo mode: write operations are disabled'], 403);
                    }
                    return redirect()->back()->with('error', 'Demo mode: write operations are disabled');
                }
                // Continue pipeline and add robots header to discourage indexing
                $response = $next($request);
                $response->headers->set('X-Robots-Tag', 'noindex, nofollow', false);
                return $response;
            }
        }
        return $next($request);
    }

    private function isWhitelisted(string $path, array $whitelist): bool
    {
        foreach ($whitelist as $allowed) {
            if (str_starts_with($path, $allowed)) {
                return true;
            }
        }
        return false;
    }
}

