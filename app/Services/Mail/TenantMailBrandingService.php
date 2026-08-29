<?php

namespace App\Services\Mail;

use App\Models\SiteSetting;
use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Support\Facades\Request;

class TenantMailBrandingService
{
    public function isTenantMailContext(): bool
    {
        if (TenantService::getTenant() instanceof Tenant) {
            return true;
        }

        $host = strtolower((string) Request::getHost());
        if ($host === '') {
            return false;
        }

        $platformHosts = array_map('strtolower', config('platform.hosts', []));

        return ! in_array($host, $platformHosts, true);
    }

    public function displayName(?Tenant $tenant = null): string
    {
        $tenant ??= TenantService::getTenant();

        if ($this->isTenantMailContext()) {
            $siteName = SiteSetting::get('site_name');
            if (is_string($siteName) && trim($siteName) !== '') {
                return trim($siteName);
            }
        }

        if ($tenant instanceof Tenant && filled($tenant->name)) {
            return (string) $tenant->name;
        }

        $appName = config('app.name');

        return is_string($appName) && trim($appName) !== '' ? trim($appName) : 'EtosCoach';
    }

    public function fromName(?Tenant $tenant = null): string
    {
        return $this->displayName($tenant);
    }

    public function subject(string $action, ?Tenant $tenant = null): string
    {
        return $this->displayName($tenant).' — '.$action;
    }

    public function platformFromName(): string
    {
        return (string) config('mail.from.name', 'EtosCoach');
    }
}
