<?php

namespace App\Jobs\Tenant;

use App\Services\Tenant\TenantProvisioner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProvisionTenantJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $slug,
        private string $planCode,
        private string $contactEmail,
        private ?string $contactName = null,
        private ?string $provider = null,
        private ?string $providerInvoiceId = null,
        private ?string $providerOrderNumber = null
    ) {}

    public function handle(TenantProvisioner $provisioner): void
    {
        $provisioner->provisionSubdomainTenant(
            slug: $this->slug,
            planCode: $this->planCode,
            contactEmail: $this->contactEmail,
            contactName: $this->contactName,
            provider: $this->provider,
            providerInvoiceId: $this->providerInvoiceId,
            providerOrderNumber: $this->providerOrderNumber
        );
    }
}

