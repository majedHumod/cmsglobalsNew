<?php

namespace App\Console\Commands;

use App\Models\Billing\Invoice;
use App\Models\Billing\Payment;
use App\Services\Billing\PaylinkActivationService;
use App\Services\Billing\PaylinkService;
use Illuminate\Console\Command;

class ReconcilePaylinkInvoices extends Command
{
    protected $signature = 'billing:reconcile-paylink
                            {--transaction= : Reconcile a single Paylink transaction number}
                            {--order= : Reconcile a single merchant order number}
                            {--limit=20 : Max number of pending invoices to inspect}';

    protected $description = 'Verify pending Paylink invoices and provision tenants for paid orders';

    public function handle(PaylinkService $paylink, PaylinkActivationService $activation): int
    {
        $transactionNo = $this->option('transaction');
        $orderNumber = $this->option('order');
        $limit = max(1, (int) $this->option('limit'));

        $invoices = Invoice::query()
            ->when($transactionNo, fn ($query) => $query->where('provider_invoice_id', $transactionNo))
            ->when($orderNumber, fn ($query) => $query->where('number', $orderNumber))
            ->when(!$transactionNo && !$orderNumber, fn ($query) => $query->whereIn('status', ['pending', 'open']))
            ->whereNotNull('provider_invoice_id')
            ->orderBy('id')
            ->limit($limit)
            ->get();

        if ($invoices->isEmpty()) {
            $this->info('No Paylink invoices found for reconciliation.');
            return self::SUCCESS;
        }

        foreach ($invoices as $invoice) {
            $this->reconcileInvoice($invoice, $paylink, $activation);
        }

        return self::SUCCESS;
    }

    private function reconcileInvoice(Invoice $invoice, PaylinkService $paylink, PaylinkActivationService $activation): void
    {
        $transactionNo = (string) $invoice->provider_invoice_id;

        try {
            $verifiedInvoice = $paylink->getInvoice($transactionNo);
        } catch (\Throwable $e) {
            $this->warn("Failed to verify invoice {$invoice->number}: {$e->getMessage()}");
            return;
        }

        $status = $paylink->normalizeInvoiceStatus($verifiedInvoice['orderStatus'] ?? null);
        $paidAmount = (float) ($verifiedInvoice['amount'] ?? 0);
        $expectedAmount = (float) $invoice->amount_due;

        if ($status === 'CANCELED') {
            $invoice->status = 'void';
            $invoice->save();

            Payment::query()
                ->where('provider_payment_intent_id', $transactionNo)
                ->update(['status' => 'failed']);

            $this->line("Canceled: {$invoice->number}");
            return;
        }

        if ($status !== 'PAID' || abs($paidAmount - $expectedAmount) > 0.01) {
            $invoice->status = 'open';
            $invoice->save();
            $this->line("Pending: {$invoice->number}");
            return;
        }

        Payment::query()
            ->where('provider_payment_intent_id', $transactionNo)
            ->update([
                'status' => 'succeeded',
                'paid_at' => now(),
                'receipt_url' => $verifiedInvoice['paymentReceipt']['receiptUrl'] ?? null,
                'method_details' => [
                    'provider' => 'paylink',
                    'payment_receipt' => $verifiedInvoice['paymentReceipt'] ?? null,
                    'order_number' => $invoice->number,
                ],
            ]);

        $result = $activation->activatePaidInvoice($transactionNo, $invoice->number, 'reconcile');
        $status = $result['status'] ?? 'unknown';

        if ($status === 'queued') {
            $this->info("Provision queued: {$invoice->number}");
            return;
        }

        if (in_array($status, ['already_provisioned', 'duplicate'], true)) {
            $this->line("Already handled: {$invoice->number}");
            return;
        }

        $this->warn("Unhandled reconciliation status [{$status}] for {$invoice->number}");
    }
}
