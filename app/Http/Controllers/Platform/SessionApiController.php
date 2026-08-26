<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\Platform\PlatformAccountCookie;
use App\Services\Platform\TenantAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SessionApiController extends Controller
{
    public function __construct(
        private readonly PlatformAccountCookie $cookie,
        private readonly TenantAccessService $access,
    ) {
    }

    public function show(Request $request): JsonResponse
    {
        $payload = $this->cookie->read($request);
        if (! $payload) {
            return $this->sessionJson($request, ['authenticated' => false]);
        }

        if ($payload['is_owner']) {
            return $this->sessionJson($request, [
                'authenticated' => true,
                'is_owner' => true,
                'name' => $payload['name'] ?: 'مالك المنصة',
                'email' => $payload['email'],
                'dashboard_url' => rtrim((string) config('platform.app_url'), '/').'/platform/customers',
                'dashboard_label' => 'لوحة الإدارة',
                'access_status' => 'active',
            ]);
        }

        $tenant = Tenant::on('system')->find($payload['tenant_id']);
        if (! $tenant) {
            return $this->sessionJson($request, ['authenticated' => false]);
        }

        $this->access->sync($tenant);

        $needsRenew = ! $this->access->canUseWorkspace($tenant);
        $app = rtrim((string) config('platform.app_url'), '/') ?: 'https://app.etoscoach.com';

        return $this->sessionJson($request, [
            'authenticated' => true,
            'is_owner' => false,
            'name' => $payload['name'] ?: $tenant->name,
            'email' => $payload['email'],
            'club' => $tenant->name,
            'access_status' => $tenant->access_status,
            'message' => $this->access->message($tenant),
            'dashboard_url' => $needsRenew
                ? $app.'/subscribe'
                : $this->access->workspaceEnterUrl($tenant),
            'dashboard_label' => $needsRenew ? 'تجديد الاشتراك' : 'لوحة التحكم',
            'login_url' => $this->access->loginUrl($tenant),
        ]);
    }

    private function sessionJson(Request $request, array $data): JsonResponse
    {
        $response = response()->json($data);
        $origin = rtrim((string) $request->headers->get('Origin'), '/');
        $allowed = config('cors.allowed_origins', []);
        if ($origin !== '' && in_array($origin, $allowed, true)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Vary', 'Origin');
        }

        return $response;
    }
}
