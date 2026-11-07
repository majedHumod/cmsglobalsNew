<?php

namespace App\Services\Billing;

use App\Jobs\Tenant\ProvisionTenantJob;
use App\Models\Billing\Event;
use App\Models\Billing\Invoice;
use App\Models\Billing\Payment;
use App\Models\Billing\Plan;
use App\Models\Billing\SignupReservation;
use App\Models\Tenant;
use App\Services\Platform\OpsAlert;
use App\Services\Platform\TenantAccessService;

class PaylinkActivationService
{
    public function __construct(
        private readonly TenantAccessService $access,
        private readonly SignupReservationService $reservations,
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

        // New signups hold their subdomain + email in a reservation while the buyer pays.
        $reservation = null;
        if (! $isRenewal) {
            // A reservation that ran out of time counts as released even if nobody has touched it since.
            try {
                $this->reservations->releaseExpired();
            } catch (\Throwable $e) {
                report($e);
            }
            $reservation = $this->reservations->findByOrder($invoice->number);
        }

        if ($reservation && $reservation->status === SignupReservation::CONFLICT) {
            // Already flagged for manual handling (refund / reassign). Never provision automatically.
            return ['status' => 'signup_conflict', 'invoice' => $invoice];
        }

        $eventId = $source.':paylink:'.$transactionNo.':paid';
        if (Event::query()->where('provider_event_id', $eventId)->exists()) {
            if (! $isRenewal && ! $tenant) {
                return $this->queueProvision($signup, $transactionNo, $invoice, 'requeued');
            }

            return ['status' => 'duplicate', 'invoice' => $invoice, 'tenant' => $tenant];
        }

        // The reservation was released (expired / superseded) before the customer paid.
        // Re-claim it if nobody took the name or email meanwhile; otherwise this payment needs manual handling.
        $conflict = false;
        if ($reservation && $reservation->status === SignupReservation::RELEASED) {
            if ($tenant || ! $this->reservations->reclaim($reservation)) {
                $conflict = true;
                $tenant = null;
            }
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

        if ($conflict && $reservation) {
            $this->reservations->markConflict($reservation);

            OpsAlert::critical(
                'دفعة اشتراك تحتاج معالجة يدوية (تعارض في الاسم/البريد)',
                "تم استلام دفعة لطلب {$invoice->number} (Paylink {$transactionNo}) لكن السب-دومين «{$reservation->slug}» أو البريد {$reservation->email} "
                ."لم يعد متاحاً (انتهى الحجز وأخذه عميل آخر). لم يتم تجهيز أي نادٍ تلقائياً.\n"
                .'الإجراء: استرجع المبلغ للعميل أو اربط الطلب باسم جديد يدوياً.',
                'signup-conflict:'.$invoice->number,
                60 * 24
            );

            return ['status' => 'signup_conflict', 'invoice' => $invoice];
        }

        if ($reservation) {
            $this->reservations->markPaid($reservation);
        }

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
            $invoice->number,
            $signup['mobile_e164'] ?? ($signup['mobile'] ?? null)
        );

        return ['status' => $status, 'invoice' => $invoice];
    }
}
