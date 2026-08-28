<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Billing\BillingContact;
use App\Models\Billing\Plan;
use App\Models\Billing\Subscription;
use App\Models\Tenant;
use App\Services\Platform\PlatformAccountCookie;
use App\Services\Platform\PlatformHandoffService;
use App\Services\Platform\TenantAccessService;
use Illuminate\Http\Request;

class SubscribePageController extends Controller
{
    public function __construct(
        private readonly PlatformAccountCookie $cookie,
        private readonly TenantAccessService $access,
        private readonly PlatformHandoffService $handoff,
    ) {
    }

    public function index(Request $request)
    {
        $marketingUrl = rtrim((string) config('platform.marketing_url'), '/') ?: 'https://etoscoach.com';
        $appUrl = rtrim((string) config('platform.app_url'), '/') ?: 'https://app.etoscoach.com';

        $plans = Plan::where('active', true)
            ->orderByRaw("CASE WHEN `interval`='monthly' THEN 1 WHEN `interval`='yearly' THEN 2 ELSE 3 END")
            ->orderBy('price')
            ->get(['code', 'name', 'price', 'interval', 'currency', 'features']);

        $session = $this->cookie->read($request);
        $tenant = null;
        $contact = null;
        $subscription = null;
        $currentPlan = null;
        $accessStatus = null;
        $authenticated = (bool) $session;
        $isOwner = ! empty($session['is_owner']);

        if ($isOwner) {
            return redirect()->away($appUrl.'/platform/customers');
        }

        if ($session && ! empty($session['tenant_id'])) {
            $tenant = Tenant::on('system')->find($session['tenant_id']);
            if ($tenant) {
                $this->access->sync($tenant);
                $accessStatus = (string) ($tenant->access_status ?: TenantAccessService::ACTIVE);
                $contact = BillingContact::query()->where('tenant_id', $tenant->id)->first();
                $subscription = Subscription::query()
                    ->where('tenant_id', $tenant->id)
                    ->latest('id')
                    ->first();

                if ($subscription?->plan_id) {
                    $currentPlan = Plan::find($subscription->plan_id);
                }
            }
        }

        $subdomain = $tenant
            ? ($tenant->subdomain ?: explode('.', (string) $tenant->domain)[0])
            : '';

        $needsRenewal = $tenant && in_array($accessStatus, [
            TenantAccessService::GRACE,
            TenantAccessService::SUSPENDED,
            TenantAccessService::ARCHIVED,
        ], true);

        $pageMode = match (true) {
            ! $authenticated => 'guest',
            $needsRenewal => 'renewal',
            $tenant !== null => 'active',
            default => 'guest_signup',
        };

        $selectedPlan = $request->query('plan');
        if ($selectedPlan && ! $plans->contains('code', $selectedPlan)) {
            $selectedPlan = null;
        }

        return view('subscribe.index', [
            'plans' => $plans,
            'tenant' => $tenant,
            'subscription' => $subscription,
            'currentPlan' => $currentPlan,
            'accessStatus' => $accessStatus,
            'authenticated' => $authenticated,
            'pageMode' => $pageMode,
            'showCheckoutForm' => in_array($pageMode, ['guest', 'guest_signup', 'renewal'], true),
            'prefill' => [
                'subdomain' => $subdomain,
                'email' => $contact->email ?? $tenant?->email ?? $session['email'] ?? '',
                'name' => $contact->name ?? $tenant?->name ?? $session['name'] ?? '',
                'mobile' => $tenant?->phone ?? '',
            ],
            'selectedPlan' => $selectedPlan,
            'marketingUrl' => $marketingUrl,
            'appUrl' => $appUrl,
            'handoffUrl' => $this->handoff->handoffUrl(),
            'loginUrl' => $appUrl.'/account/login?redirect='.urlencode('/subscribe'),
            'graceEndsAt' => $tenant?->grace_ends_at,
            'subscriptionEndsAt' => $tenant?->subscription_ends_at,
            'accessMessage' => $tenant ? $this->access->message($tenant) : '',
            'domain' => config('app.domain', 'etoscoach.com'),
        ]);
    }
}
