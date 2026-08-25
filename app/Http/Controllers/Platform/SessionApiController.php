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
            return response()->json(['authenticated' => false]);
        }

        if ($payload['is_owner']) {
            return response()->json([
                'authenticated' => true,
                'is_owner' => true,
                'name' => $payload['name'] ?: 'مالك المنصة',
                'email' => $payload['email'],
                'dashboard_url' => config('platform.app_url').'/platform/customers',
                'dashboard_label' => 'لوحة الإدارة',
                'access_status' => 'active',
            ]);
        }

        $tenant = Tenant::on('system')->find($payload['tenant_id']);
        if (! $tenant) {
            return response()->json(['authenticated' => false]);
        }

        $this->access->sync($tenant);

        return response()->json([
            'authenticated' => true,
            'is_owner' => false,
            'name' => $payload['name'] ?: $tenant->name,
            'email' => $payload['email'],
            'club' => $tenant->name,
            'access_status' => $tenant->access_status,
            'message' => $this->access->message($tenant),
            'dashboard_url' => $this->access->canUseWorkspace($tenant)
                ? $this->access->dashboardUrl($tenant)
                : url('/account/expired'),
            'dashboard_label' => $this->access->canUseWorkspace($tenant) ? 'لوحة التحكم' : 'تجديد الاشتراك',
            'login_url' => $this->access->loginUrl($tenant),
        ]);
    }
}
