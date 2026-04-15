<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Billing\Subscription;
use App\Models\Billing\Invoice;
use App\Models\Billing\Payment;
use App\Models\Billing\Plan;
use App\Services\TenantService;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function index(): View
    {
        $tenant = TenantService::getTenant();

        $subscription = null;
        $plan = null;
        $invoices = collect();
        $payments = collect();

        if ($tenant) {
            $subscription = Subscription::where('tenant_id', $tenant->id)
                ->latest('id')
                ->first();
            if ($subscription) {
                $plan = Plan::find($subscription->plan_id);
            }
            $invoices = Invoice::where('tenant_id', $tenant->id)
                ->latest('id')
                ->limit(10)
                ->get();
            $payments = Payment::where('tenant_id', $tenant->id)
                ->latest('id')
                ->limit(10)
                ->get();
        }

        return view('admin.billing.index', compact('tenant', 'subscription', 'plan', 'invoices', 'payments'));
    }
}

