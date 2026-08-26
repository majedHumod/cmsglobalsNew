<?php

namespace App\Http\Middleware;

use App\Services\Platform\TenantAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceTenantAccess
{
    public function __construct(private readonly TenantAccessService $access)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $request->attributes->get('tenant');
        if (! $tenant) {
            return $next($request);
        }

        $this->access->sync($tenant);
        $status = $tenant->access_status ?: TenantAccessService::ACTIVE;

        if (in_array($status, [TenantAccessService::ACTIVE, TenantAccessService::GRACE], true)) {
            if ($status === TenantAccessService::GRACE) {
                $request->attributes->set('tenant_access_banner', $this->access->message($tenant));
            }

            return $next($request);
        }

        if ($this->isExempt($request)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => $this->access->message($tenant),
                'error' => 'subscription_inactive',
                'access_status' => $status,
            ], 402);
        }

        return response()->view('platform.account.expired', array_merge([
            'tenant' => $tenant,
            'message' => $this->access->message($tenant),
            'subscribeUrl' => rtrim((string) config('platform.app_url'), '/').'/subscribe',
            'marketingUrl' => rtrim((string) config('platform.marketing_url'), '/') ?: 'https://etoscoach.com',
            'accountName' => null,
            'accountEmail' => null,
        ], $this->access->expiredPageContext($tenant)), 402);
    }

    private function isExempt(Request $request): bool
    {
        return $request->is([
            'login',
            'logout',
            'register',
            'forgot-password',
            'reset-password',
            'reset-password/*',
            'two-factor-challenge',
            'user/confirm-password',
            'admin/billing',
            'billing/*',
            'subscribe',
            'checkout/*',
            'platform/enter',
            'account/*',
            'livewire/*',
            'sanctum/*',
            'up',
        ]);
    }
}
