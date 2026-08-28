<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\Platform\PlatformAccountCookie;
use App\Services\Platform\PlatformHandoffService;
use App\Services\Platform\TenantAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SessionApiController extends Controller
{
    public function __construct(
        private readonly PlatformAccountCookie $cookie,
        private readonly TenantAccessService $access,
        private readonly PlatformHandoffService $handoff,
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
                'needs_renewal' => false,
            ]);
        }

        $tenant = Tenant::on('system')->find($payload['tenant_id']);
        if (! $tenant) {
            return $this->sessionJson($request, ['authenticated' => false]);
        }

        $this->access->sync($tenant);
        $status = (string) ($tenant->access_status ?: TenantAccessService::ACTIVE);
        $app = rtrim((string) config('platform.app_url'), '/') ?: 'https://app.etoscoach.com';
        $subscribeUrl = $app.'/subscribe';
        $handoffUrl = $this->handoff->handoffUrl();

        $data = [
            'authenticated' => true,
            'is_owner' => false,
            'name' => $payload['name'] ?: $tenant->name,
            'email' => $payload['email'],
            'club' => $tenant->name,
            'access_status' => $status,
            'needs_renewal' => $status !== TenantAccessService::ACTIVE,
            'message' => $this->access->message($tenant),
            'login_url' => $this->access->loginUrl($tenant),
            'subscribe_url' => $subscribeUrl,
        ];

        if ($status === TenantAccessService::ACTIVE) {
            $data['dashboard_url'] = $handoffUrl;
            $data['dashboard_label'] = 'لوحة التحكم';
        } elseif ($status === TenantAccessService::GRACE) {
            $data['dashboard_url'] = $subscribeUrl;
            $data['dashboard_label'] = 'تجديد الاشتراك';
            $data['secondary_url'] = $handoffUrl;
            $data['secondary_label'] = 'لوحة التحكم';
            $data['status_hint'] = 'انتهى الاشتراك — فترة سماح';
        } else {
            $data['dashboard_url'] = $subscribeUrl;
            $data['dashboard_label'] = 'تجديد الاشتراك';
            $data['status_hint'] = 'اشتراك منتهٍ';
        }

        return $this->sessionJson($request, $data);
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
