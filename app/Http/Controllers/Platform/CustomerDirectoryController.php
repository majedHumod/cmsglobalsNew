<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Billing\Plan;
use App\Models\Billing\Subscription;
use App\Models\Tenant;
use App\Services\Platform\PlatformAccountCookie;
use App\Services\Platform\TenantAccessService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerDirectoryController extends Controller
{
    public function __construct(
        private readonly PlatformAccountCookie $cookie,
        private readonly TenantAccessService $access,
    ) {
    }

    public function index(Request $request): View
    {
        $session = $this->cookie->read($request);
        abort_unless($session && $session['is_owner'], 403);

        $tenants = Tenant::on('system')->orderByDesc('id')->get();
        $rows = $tenants->map(function (Tenant $tenant) {
            $this->access->sync($tenant);
            $sub = Subscription::query()->where('tenant_id', $tenant->id)->latest('id')->first();
            $plan = $sub ? Plan::find($sub->plan_id) : null;

            return [
                'tenant' => $tenant,
                'plan' => $plan?->name,
                'period_end' => $sub?->current_period_end,
                'sub_status' => $sub?->status,
                'access' => $tenant->access_status,
            ];
        });

        return view('platform.customers.index', ['rows' => $rows]);
    }
}
