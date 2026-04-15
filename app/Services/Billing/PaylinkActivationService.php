<?php

namespace App\Services\Billing;

use App\Jobs\Tenant\ProvisionTenantJob;
use App\Models\Billing\Event;
use App\Models\Billing\Invoice;
use App\Models\Billing\Payment;
use App\Models\Tenant;

class PaylinkActivationService
{
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

        if (!$slug || empty($signup['plan_code']) || empty($signup['email'])) {
            return ['status' => 'invalid_signup_payload', 'invoice' => $invoice];
        }

        $tenant = Tenant::on('system')
            ->where('subdomain', $slug)
            ->orWhere('domain', $domain)
            ->first();

        $eventId = $source . ':paylink:' . $transactionNo . ':paid';
        if (Event::query()->where('provider_event_id', $eventId)->exists()) {
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

        if ($tenant) {
            return ['status' => 'already_provisioned', 'invoice' => $invoice, 'tenant' => $tenant];
        }

        ProvisionTenantJob::dispatch(
            $signup['slug'],
            $signup['plan_code'],
            $signup['email'],
            $signup['name'] ?? null,
            'paylink',
            $transactionNo,
            $invoice->number
        );

        return ['status' => 'queued', 'invoice' => $invoice];
    }
}
