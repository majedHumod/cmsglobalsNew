<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Billing\Event;
use App\Models\Billing\Invoice;
use App\Models\Billing\Payment;
use App\Services\Billing\PaylinkActivationService;
use App\Services\Billing\PaylinkService;
use App\Services\Billing\SignupReservationService;
use App\Services\Platform\OpsAlert;
use Illuminate\Http\Request;

class PaylinkWebhookController extends Controller
{
    public function __construct(
        private readonly PaylinkService $paylink,
        private readonly PaylinkActivationService $activation,
    ) {
    }

    public function __invoke(Request $request)
    {
        if (!$this->isAuthorized($request)) {
            return response()->json(['message' => 'Unauthorized webhook request.'], 401);
        }

        $payload = $request->all();
        $transactionNo = (string) ($payload['transactionNo'] ?? '');
        $orderNumber = (string) ($payload['merchantOrderNumber'] ?? '');
        $orderStatus = $this->paylink->normalizeInvoiceStatus($payload['orderStatus'] ?? null);
        $eventKey = 'webhook:paylink:' . $transactionNo . ':' . $orderStatus;

        if ($transactionNo === '' || $orderNumber === '') {
            return response()->json(['message' => 'Invalid webhook payload.'], 422);
        }

        if (Event::query()->where('provider_event_id', $eventKey)->exists()) {
            return response()->json(['status' => 'duplicate']);
        }

        $invoice = Invoice::query()
            ->where('provider_invoice_id', $transactionNo)
            ->orWhere('number', $orderNumber)
            ->latest('id')
            ->first();

        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found.'], 404);
        }

        $checkoutEvent = Event::query()
            ->where('provider_event_id', 'checkout:' . $invoice->number)
            ->first();

        if (!$checkoutEvent) {
            return response()->json(['message' => 'Pending checkout event not found.'], 404);
        }

        try {
            $verifiedInvoice = $this->paylink->getInvoice($transactionNo);
        } catch (\Throwable $e) {
            Event::create([
                'tenant_id' => null,
                'provider_event_id' => $eventKey . ':error',
                'type' => 'paylink.webhook.error',
                'payload' => [
                    'incoming' => $payload,
                    'error' => $e->getMessage(),
                ],
                'processed_at' => now(),
            ]);

            return response()->json(['message' => 'Verification failed.'], 422);
        }

        $verifiedStatus = $this->paylink->normalizeInvoiceStatus($verifiedInvoice['orderStatus'] ?? null);
        $paidAmount = (float) ($verifiedInvoice['amount'] ?? 0);
        $expectedAmount = (float) $invoice->amount_due;

        Event::create([
            'tenant_id' => null,
            'provider_event_id' => $eventKey,
            'type' => 'paylink.webhook.received',
            'payload' => [
                'incoming' => $payload,
                'verified_invoice' => $verifiedInvoice,
            ],
            'processed_at' => now(),
        ]);

        if (($verifiedInvoice['transactionNo'] ?? null) !== $transactionNo || $verifiedStatus !== 'PAID' || abs($paidAmount - $expectedAmount) > 0.01) {
            $invoice->status = $verifiedStatus === 'CANCELED' ? 'void' : 'open';
            $invoice->save();

            if ($verifiedStatus === 'CANCELED') {
                // Payment was abandoned/expired: free the subdomain + email held for this checkout.
                app(SignupReservationService::class)->release($invoice->number);
            }

            Payment::query()
                ->where('provider_payment_intent_id', $transactionNo)
                ->update([
                    'status' => $verifiedStatus === 'CANCELED' ? 'failed' : 'pending',
                ]);

            return response()->json(['status' => 'ignored']);
        }

        Payment::query()
            ->where('provider_payment_intent_id', $transactionNo)
            ->update([
                'status' => 'succeeded',
                'paid_at' => now(),
                'receipt_url' => $verifiedInvoice['paymentReceipt']['receiptUrl'] ?? null,
                'method_details' => [
                    'provider' => 'paylink',
                    'payment_type' => $payload['paymentType'] ?? null,
                    'payment_receipt' => $verifiedInvoice['paymentReceipt'] ?? null,
                    'order_number' => $orderNumber,
                ],
            ]);

        $result = $this->activation->activatePaidInvoice($transactionNo, $orderNumber, 'webhook');

        return response()->json(['status' => $result['status'] ?? 'accepted']);
    }

    private function isAuthorized(Request $request): bool
    {
        $secret = (string) config('services.paylink.webhook_secret');

        if ($secret === '') {
            // Every webhook is re-verified against the Paylink API (status + amount) before anything is activated,
            // so a forged request cannot mark an invoice as paid. The secret only keeps strangers from
            // making us call the API. Refusing here would silently break payments if Paylink does not send it,
            // so in production we accept but complain loudly until PAYLINK_WEBHOOK_SECRET is set.
            if (app()->isProduction()) {
                OpsAlert::critical(
                    'PAYLINK_WEBHOOK_SECRET غير مضبوط في الإنتاج',
                    'مسار /webhooks/paylink يقبل أي طلب لأن PAYLINK_WEBHOOK_SECRET فارغ. اضبطه في .env وفي لوحة Paylink.',
                    'paylink-webhook-secret-missing',
                    60 * 24
                );
            }

            return true;
        }

        $candidates = array_filter([
            (string) $request->header('Authorization'),
            (string) $request->header('PAYLINK_WEBHOOK_SECRET'),
            (string) $request->header('X-PAYLINK-WEBHOOK-SECRET'),
        ]);

        foreach ($candidates as $header) {
            $normalized = trim($header);
            $normalized = preg_replace('/^Bearer\s+/i', '', $normalized) ?? $normalized;
            $normalized = trim($normalized, "\"' \t\n\r\0\x0B");

            if (hash_equals($secret, $normalized)) {
                return true;
            }
        }

        return false;
    }
}
