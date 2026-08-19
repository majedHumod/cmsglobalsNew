<?php

namespace App\Filament\Pages;

use App\Models\Billing\Invoice;
use App\Models\Billing\Payment;
use App\Models\Billing\Plan;
use App\Models\Billing\Subscription;
use App\Services\TenantService;
use Filament\Pages\Page;

class TenantBilling extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static string $view = 'filament.pages.tenant-billing';

    protected static ?string $navigationGroup = 'العضويات والاشتراكات';

    protected static ?string $navigationLabel = 'فوترة المستأجر';

    protected static ?string $title = 'الفوترة والاشتراك';

    protected static ?int $navigationSort = 4;

    public ?array $tenantInfo = null;

    public ?array $subscriptionInfo = null;

    public ?array $planInfo = null;

    /** @var array<int, array<string, mixed>> */
    public array $invoices = [];

    /** @var array<int, array<string, mixed>> */
    public array $payments = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public function mount(): void
    {
        $tenant = TenantService::getTenant();

        if (! $tenant) {
            $this->tenantInfo = null;

            return;
        }

        $this->tenantInfo = [
            'id' => $tenant->id,
            'name' => $tenant->name ?? $tenant->domain ?? ('#'.$tenant->id),
            'domain' => $tenant->domain ?? null,
        ];

        $subscription = Subscription::query()
            ->where('tenant_id', $tenant->id)
            ->latest('id')
            ->first();

        if ($subscription) {
            $this->subscriptionInfo = [
                'status' => $subscription->status,
                'starts_at' => optional($subscription->current_period_start)->format('d/m/Y'),
                'ends_at' => optional($subscription->current_period_end)->format('d/m/Y'),
            ];

            $plan = Plan::query()->find($subscription->plan_id);
            if ($plan) {
                $this->planInfo = [
                    'name' => $plan->name,
                    'code' => $plan->code,
                    'interval' => $plan->interval,
                    'price' => number_format((float) $plan->price, 2).' '.($plan->currency ?? 'SAR'),
                ];
            }
        }

        $this->invoices = Invoice::query()
            ->where('tenant_id', $tenant->id)
            ->latest('id')
            ->limit(10)
            ->get()
            ->map(fn (Invoice $invoice) => [
                'number' => $invoice->number ?? $invoice->id,
                'amount' => number_format((float) $invoice->amount_due, 2).' '.($invoice->currency ?? ''),
                'status' => $invoice->status,
            ])
            ->all();

        $this->payments = Payment::query()
            ->where('tenant_id', $tenant->id)
            ->latest('id')
            ->limit(10)
            ->get()
            ->map(fn (Payment $payment) => [
                'amount' => number_format((float) $payment->amount, 2).' '.($payment->currency ?? ''),
                'status' => $payment->status,
                'paid_at' => optional($payment->paid_at)->format('d/m/Y') ?? '—',
            ])
            ->all();
    }
}
