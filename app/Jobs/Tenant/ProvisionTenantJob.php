<?php

namespace App\Jobs\Tenant;

use App\Services\Platform\OpsAlert;
use App\Services\Tenant\TenantProvisioner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProvisionTenantJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** A paid customer is waiting: retry transient failures (DB busy, pool refilled, mail down) before giving up. */
    public int $tries = 5;

    public function __construct(
        private string $slug,
        private string $planCode,
        private string $contactEmail,
        private ?string $contactName = null,
        private ?string $provider = null,
        private ?string $providerInvoiceId = null,
        private ?string $providerOrderNumber = null,
        private ?string $contactPhone = null
    ) {}

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [60, 300, 900, 1800];
    }

    public function handle(TenantProvisioner $provisioner): void
    {
        $provisioner->provisionSubdomainTenant(
            slug: $this->slug,
            planCode: $this->planCode,
            contactEmail: $this->contactEmail,
            contactName: $this->contactName,
            provider: $this->provider,
            providerInvoiceId: $this->providerInvoiceId,
            providerOrderNumber: $this->providerOrderNumber,
            // Jobs serialized before this property existed must keep working.
            contactPhone: $this->contactPhone ?? null
        );
    }

    public function failed(Throwable $exception): void
    {
        $order = $this->providerOrderNumber ?? '-';

        OpsAlert::critical(
            'فشل تجهيز نادٍ مدفوع',
            "فشل تجهيز النادي «{$this->slug}» بعد {$this->tries} محاولات.\n"
            ."الطلب: {$order} | Paylink: ".($this->providerInvoiceId ?? '-')." | البريد: {$this->contactEmail}\n"
            .'السبب: '.$exception->getMessage()."\n\n"
            ."بعد معالجة السبب (مثلاً تسجيل قاعدة جديدة في الـ pool) أعد المحاولة:\n"
            ."php artisan billing:retry-provision --order={$order}",
            'provision-failed:'.$order,
            60 * 12,
            ['slug' => $this->slug, 'order' => $order]
        );
    }
}
