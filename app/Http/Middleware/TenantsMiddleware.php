<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\TenantService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Mobile apps may hit an IP/API host; allow explicit tenant domain override.
        $domain = $request->header('X-Tenant-Domain')
            ?: $request->header('X-Tenant')
            ?: $request->query('tenant_domain')
            ?: $request->getHost();

        $domain = $this->normalizeDomain((string) $domain);
        $domain = $this->applyDomainAlias($domain);

        if ($this->isPlatformHost($domain)) {
            return $next($request);
        }

        $subdomain = explode('.', $domain)[0] ?? $domain;

        $tenant = null;
        if ($domain !== '') {
            $tenant = Tenant::on('system')
                ->where(function ($query) use ($domain, $subdomain) {
                    $query->where('domain', $domain)
                        ->orWhere('subdomain', $subdomain);
                })
                ->first();
        }

        if ($tenant) {
            TenantService::switchToTenant($tenant);
            $request->attributes->set('tenant', $tenant);

            return $next($request);
        }

        // API must never fall through to the system DB for tenant user tables.
        if ($this->requiresTenant($request)) {
            return response()->json([
                'message' => 'لم يتم التعرف على المستأجر. أرسل ترويسة X-Tenant-Domain بقيمة دومين المستأجر (مثال: app3.cmsglobals.test).',
                'error' => 'tenant_not_resolved',
                'domain' => $domain !== '' ? $domain : null,
            ], 400);
        }

        return $next($request);
    }

    private function normalizeDomain(string $domain): string
    {
        $domain = strtolower(trim($domain));
        $domain = preg_replace('#^https?://#', '', $domain) ?: $domain;
        $domain = explode('/', $domain)[0];
        $domain = explode(':', $domain)[0];

        return trim($domain);
    }

    private function isPlatformHost(string $domain): bool
    {
        $hosts = array_map('strtolower', config('platform.hosts', []));

        return $domain !== '' && in_array($domain, $hosts, true);
    }

    /**
     * Optional aliases: TENANT_DOMAIN_ALIASES=app1.cmsglobals.test:app3.cmsglobals.test,old:new
     */
    private function applyDomainAlias(string $domain): string
    {
        $raw = (string) env('TENANT_DOMAIN_ALIASES', 'app1.cmsglobals.test:app3.cmsglobals.test');
        if ($raw === '' || $domain === '') {
            return $domain;
        }

        foreach (explode(',', $raw) as $pair) {
            $parts = array_map('trim', explode(':', $pair, 2));
            if (count($parts) === 2 && strtolower($parts[0]) === $domain) {
                return strtolower($parts[1]);
            }
        }

        return $domain;
    }

    private function requiresTenant(Request $request): bool
    {
        if (! $request->is('api/*')) {
            return false;
        }

        // Platform webhooks stay on the system connection.
        if ($request->is('api/webhooks/*') || $request->is('api/*/webhooks/*') || $request->is('api/platform/*')) {
            return false;
        }

        // Organization discovery is system-scoped (single mobile build entry).
        if (
            $request->is('api/v1/organizations/discover')
            || $request->is('api/v1/organizations/search')
            || $request->is('api/organizations/discover')
            || $request->is('api/organizations/search')
        ) {
            return false;
        }

        return true;
    }
}
