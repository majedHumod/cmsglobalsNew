<?php

namespace App\Services\Billing;

use App\Jobs\Tenant\ProvisionTenantJob;
use App\Models\Billing\Event;
use App\Models\Billing\Invoice;
use App\Models\Billing\Payment;
use App\Models\Billing\Plan;
use App\Models\Tenant;
use App\Services\Platform\TenantAccessService;

class PaylinkActivationService
{
    public function __construct(
        private readonly TenantAccessService $access,
    ) {
    }

    public function activatePaidInvoice(string $transactionNo, ?string $orderNumber = null, string $source = 'callback'): array
    {
        $invoice = Invoice::query()
            ->where(function ($query) use ($transactionNo, $orderNumber) {
                $query->where('provider_invoice_id', $transactionNo);

                if ($orderNumber) {
                    $query->orWhere('number', $orderNumber);
                }
            })
            ->latest('id')
            ->first();

        if (!$invoice) {
            return ['status' => 'missing_invoice'];
        }

        $checkoutEvent = Event::query()
            ->where('provider_event_id', 'checkout:' . $invoice->number)
            ->first();

        if (!$checkoutEvent) {
            return ['status' => 'missing_checkout_event', 'invoice' => $invoice];
        }

        $signup = $checkoutEvent->payload ?? [];
        $slug = $signup['slug'] ?? null;
        $domain = $slug ? ($slug . '.' . config('app.domain', 'yourdomain.com')) : null;
        $renewTenantId = (int) ($signup['tenant_id'] ?? 0);
        $isRenewal = ! empty($signup['renew']) && $renewTenantId > 0;

        if (!$slug || empty($signup['plan_code']) || empty($signup['email'])) {
            return ['status' => 'invalid_signup_payload', 'invoice' => $invoice];
        }

        $tenant = $isRenewal
            ? Tenant::on('system')->find($renewTenantId)
            : Tenant::on('system')
                ->where('subdomain', $slug)
                ->orWhere('domain', $domain)
                ->first();

        $eventId = $source.':paylink:'.$transactionNo.':paid';
        if (Event::query()->where('provider_event_id', $eventId)->exists()) {
            if (! $isRenewal && ! $tenant) {
                return $this->queueProvision($signup, $transactionNo, $invoice, 'requeued');
            }

            return ['status' => 'duplicate', 'invoice' => $invoice, 'tenant' => $tenant];
        }

        $invoice->status = 'paid';
        $invoice->amount_paid = $invoice->amount_due;
        if ($tenant) {
            $invoice->tenant_id = $tenant->id;
        }
        $invoice->save();

        $payment = Payment::query()
            ->where('provider_payment_intent_id', $transactionNo)
            ->first();

        if ($payment) {
            $payment->status = 'succeeded';
            $payment->paid_at = $payment->paid_at ?: now();
            if ($tenant) {
                $payment->tenant_id = $tenant->id;
            }
            $payment->save();
        }

        Event::create([
            'tenant_id' => $tenant?->id,
            'provider_event_id' => $eventId,
            'type' => 'paylink.' . $source . '.paid',
            'payload' => [
                'invoice_id' => $invoice->id,
                'transaction_no' => $transactionNo,
                'order_number' => $invoice->number,
                'signup' => $signup,
            ],
            'processed_at' => now(),
        ]);

        if ($isRenewal && $tenant) {
            $plan = Plan::where('code', $signup['plan_code'])->first();
            if ($plan) {
                $this->access->applyPaidRenewal($tenant, $plan, 'paylink', $invoice->number);
            }

            return ['status' => 'renewed', 'invoice' => $invoice, 'tenant' => $tenant];
        }

        if ($tenant) {
            return ['status' => 'already_provisioned', 'invoice' => $invoice, 'tenant' => $tenant];
        }

        return $this->queueProvision($signup, $transactionNo, $invoice, 'queued');
    }

    /**
     * @param  array<string, mixed>  $signup
     * @return array{status: string, invoice: Invoice}
     */
    private function queueProvision(array $signup, string $transactionNo, Invoice $invoice, string $status): array
    {
        ProvisionTenantJob::dispatch(
            $signup['slug'],
            $signup['plan_code'],
            $signup['email'],
            $signup['name'] ?? null,
            'paylink',
            $transactionNo,
            $invoice->number
        );

        return ['status' => $status, 'invoice' => $invoice];
    }
}
