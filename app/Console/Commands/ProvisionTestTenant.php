<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Tenant\TenantProvisioner;

class ProvisionTestTenant extends Command
{
    protected $signature = 'tenants:provision-test {slug} {plan} {email} {name?}';

    protected $description = 'Provision a test subdomain tenant using the new provisioning flow';

    public function handle(TenantProvisioner $provisioner): int
    {
        $slug = strtolower($this->argument('slug'));
        $plan = $this->argument('plan');
        $email = $this->argument('email');
        $name = $this->argument('name') ?? null;

        $this->info("Provisioning tenant:");
        $this->line(" - Slug: {$slug}");
        $this->line(" - Plan: {$plan}");
        $this->line(" - Email: {$email}");
        if ($name) $this->line(" - Name: {$name}");

        try {
            $provisioner->provisionSubdomainTenant($slug, $plan, $email, $name);
            $this->info("✅ Provisioning completed for {$slug}");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("❌ Failed: ".$e->getMessage());
            return self::FAILURE;
        }
    }
}

