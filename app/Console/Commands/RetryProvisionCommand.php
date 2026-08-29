<?php

namespace App\Console\Commands;

use App\Models\Billing\Event;
use App\Models\Billing\Invoice;
use App\Models\Tenant;
use App\Services\Billing\PaylinkActivationService;
use Illuminate\Console\Command;

class RetryProvisionCommand extends Command
{
    protected $signature = 'billing:retry-provision
                            {--order= : Merchant order number e.g. signup-...}
                            {--transaction= : Paylink transaction number}
                            {--all-paid : Retry all paid signup invoices without a tenant}';

    protected $description = 'Re-queue tenant provisioning for paid signup invoices that failed to provision';

    public function handle(PaylinkActivationService $activation): int
    {
        $order = $this->option('order');
        $transaction = $this->option('transaction');
        $allPaid = (bool) $this->option('all-paid');

        if (! $order && ! $transaction && ! $allPaid) {
            $this->error('Provide --order=, --transaction=, or --all-paid');

            return self::FAILURE;
        }

        $invoices = Invoice::query()
            ->when($order, fn ($q) => $q->where('number', $order))
            ->when($transaction, fn ($q) => $q->where('provider_invoice_id', $transaction))
            ->when($allPaid, fn ($q) => $q->where('status', 'paid')->whereNull('tenant_id'))
            ->whereNotNull('provider_invoice_id')
            ->orderBy('id')
            ->get();

        if ($invoices->isEmpty()) {
            $this->warn('No matching invoices found.');

            return self::SUCCESS;
        }

        foreach ($invoices as $invoice) {
            $this->retryInvoice($invoice, $activation);
        }

        return self::SUCCESS;
    }

    private function retryInvoice(Invoice $invoice, PaylinkActivationService $activation): void
    {
        $checkout = Event::query()
            ->where('provider_event_id', 'checkout:'.$invoice->number)
            ->first();

        if (! $checkout) {
            $this->warn("Missing checkout event for {$invoice->number}");

            return;
        }

        $signup = $checkout->payload ?? [];
        $slug = (string) ($signup['slug'] ?? '');
        if ($slug === '') {
            $this->warn("Missing slug on checkout event for {$invoice->number}");

            return;
        }

        if (! empty($signup['renew'])) {
            $this->line("Skip renewal invoice {$invoice->number}");

            return;
        }

        $domain = $slug.'.'.config('app.domain', 'etoscoach.com');
        $tenant = Tenant::on('system')
            ->where('subdomain', $slug)
            ->orWhere('domain', $domain)
            ->first();

        if ($tenant) {
            $invoice->tenant_id = $tenant->id;
            $invoice->save();
            $this->info("Already provisioned: {$invoice->number} → tenant #{$tenant->id}");

            return;
        }

        $result = $activation->activatePaidInvoice(
            (string) $invoice->provider_invoice_id,
            $invoice->number,
            'retry-provision'
        );

        $status = $result['status'] ?? 'unknown';
        $this->info("{$invoice->number}: {$status}");
    }
}
