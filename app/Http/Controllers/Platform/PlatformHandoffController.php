<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\Platform\PlatformAccountCookie;
use App\Services\Platform\PlatformHandoffService;
use App\Services\Platform\TenantAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PlatformHandoffController extends Controller
{
    public function __construct(
        private readonly PlatformAccountCookie $cookie,
        private readonly PlatformHandoffService $handoff,
        private readonly TenantAccessService $access,
    ) {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $payload = $this->cookie->read($request);
        if (! $payload || ! empty($payload['is_owner'])) {
            return redirect()->route('platform.account.login', [
                'redirect' => config('platform.marketing_url'),
            ]);
        }

        $tenant = Tenant::on('system')->find($payload['tenant_id']);
        if (! $tenant) {
            return redirect()->route('platform.account.login', [
                'redirect' => config('platform.marketing_url'),
            ]);
        }

        $this->access->sync($tenant);

        if (! $this->access->canUseWorkspace($tenant)) {
            return redirect()->to(rtrim((string) config('platform.app_url'), '/').'/subscribe');
        }

        $token = $this->handoff->create($tenant, $payload['email']);

        return redirect()->away($this->handoff->enterUrl($tenant, $token));
    }
}
