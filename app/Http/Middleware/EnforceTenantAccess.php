<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
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
        $status = (string) ($tenant->access_status ?: TenantAccessService::ACTIVE);

        if ($status === TenantAccessService::ACTIVE) {
            return $next($request);
        }

        if ($this->isExempt($request)) {
            return $next($request);
        }

        if ($status === TenantAccessService::GRACE && $this->isCoachWorkspaceRoute($request) && $this->isCoachUser($request)) {
            $request->attributes->set('tenant_access_banner', $this->ownerGraceMessage($tenant));

            return $next($request);
        }

        $audience = $this->isCoachUser($request) ? 'owner' : 'public';

        return $this->blockedResponse($request, $tenant, $status, $audience);
    }

    private function blockedResponse(Request $request, Tenant $tenant, string $status, string $audience): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => $audience === 'owner'
                    ? $this->access->message($tenant)
                    : $this->publicUnavailableMessage($tenant),
                'error' => 'subscription_inactive',
                'access_status' => $status,
                'audience' => $audience,
            ], 402);
        }

        $view = $audience === 'owner'
            ? 'platform.tenant.owner-renewal-required'
            : 'platform.tenant.public-unavailable';

        return response()->view($view, array_merge([
            'tenant' => $tenant,
            'accessStatus' => $status,
            'subscribeUrl' => rtrim((string) config('platform.app_url'), '/').'/subscribe',
            'marketingUrl' => rtrim((string) config('platform.marketing_url'), '/') ?: 'https://etoscoach.com',
            'ownerMessage' => $this->access->message($tenant),
            'publicMessage' => $this->publicUnavailableMessage($tenant),
        ], $this->access->expiredPageContext($tenant)), 402);
    }

    private function ownerGraceMessage(Tenant $tenant): string
    {
        return 'تنبيه للنادي/المدرب: '.$this->access->message($tenant);
    }

    private function publicUnavailableMessage(Tenant $tenant): string
    {
        return 'موقع '.$tenant->name.' غير متاح مؤقتاً. يرجى التواصل مع النادي أو المدرب لاحقاً.';
    }

    private function isCoachUser(Request $request): bool
    {
        $user = $request->user();

        return $user && $user->hasAnyRole(['admin', 'coach']);
    }

    private function isCoachWorkspaceRoute(Request $request): bool
    {
        return $request->is([
            'dashboard',
            'dashboard/*',
            'admin',
            'admin/*',
            'admin-cms',
            'admin-cms/*',
            'coach',
            'coach/*',
            'livewire/*',
        ]);
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
            'platform/handoff',
            'platform/enter',
            'account/*',
            'livewire/*',
            'sanctum/*',
            'up',
        ]);
    }
}
