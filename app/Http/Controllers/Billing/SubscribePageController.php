<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Billing\BillingContact;
use App\Models\Billing\Plan;
use App\Models\Tenant;
use App\Services\Platform\PlatformAccountCookie;
use App\Services\Platform\TenantAccessService;
use Illuminate\Http\Request;

class SubscribePageController extends Controller
{
    public function __construct(
        private readonly PlatformAccountCookie $cookie,
        private readonly TenantAccessService $access,
    ) {
    }

    public function index(Request $request)
    {
        $plans = Plan::where('active', true)
            ->orderByRaw("CASE WHEN `interval`='monthly' THEN 1 WHEN `interval`='yearly' THEN 2 ELSE 3 END")
            ->orderBy('price')
            ->get(['code', 'name', 'price', 'interval', 'currency', 'features']);

        $session = $this->cookie->read($request);
        $tenant = null;
        $contact = null;
        $renewal = false;

        if ($session && empty($session['is_owner']) && ! empty($session['tenant_id'])) {
            $tenant = Tenant::on('system')->find($session['tenant_id']);
            if ($tenant) {
                $this->access->sync($tenant);
                $renewal = true;
                $contact = BillingContact::query()->where('tenant_id', $tenant->id)->first();
            }
        }

        $subdomain = $tenant
            ? ($tenant->subdomain ?: explode('.', (string) $tenant->domain)[0])
            : '';

        return view('subscribe.index', [
            'plans' => $plans,
            'renewal' => $renewal,
            'tenant' => $tenant,
            'prefill' => [
                'subdomain' => $subdomain,
                'email' => $contact->email ?? $tenant?->email ?? $session['email'] ?? '',
                'name' => $contact->name ?? $tenant?->name ?? $session['name'] ?? '',
                'mobile' => $tenant?->phone ?? '',
            ],
            'marketingUrl' => rtrim((string) config('platform.marketing_url'), '/') ?: 'https://etoscoach.com',
        ]);
    }
}
