<?php

namespace App\Services\Mail;

use App\Models\SiteSetting;
use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Support\Facades\Request;

/**
 * Mail branding rules:
 *
 * - platform → messages to tenants/subscribers (EtosCoach brand)
 * - club     → messages to trainees/clients on a tenant subdomain (club/coach brand)
 */
class TenantMailBrandingService
{
    public const AUDIENCE_PLATFORM = 'platform';

    public const AUDIENCE_CLUB = 'club';

    public function audience(): string
    {
        $forced = config('mail.branding_audience');
        if (is_string($forced) && in_array($forced, [self::AUDIENCE_PLATFORM, self::AUDIENCE_CLUB], true)) {
            return $forced;
        }

        // Platform hosts (app / marketing) always send as EtosCoach,
        // even when a tenant DB is temporarily selected (e.g. forgot password).
        if ($this->isPlatformHostRequest()) {
            return self::AUDIENCE_PLATFORM;
        }

        if (TenantService::getTenant() instanceof Tenant || $this->isTenantHostRequest()) {
            return self::AUDIENCE_CLUB;
        }

        return self::AUDIENCE_PLATFORM;
    }

    public function isClubAudience(): bool
    {
        return $this->audience() === self::AUDIENCE_CLUB;
    }

    public function isPlatformAudience(): bool
    {
        return $this->audience() === self::AUDIENCE_PLATFORM;
    }

    /** @deprecated Use isClubAudience() */
    public function isTenantMailContext(): bool
    {
        return $this->isClubAudience();
    }

    public function isPlatformHostRequest(): bool
    {
        $host = strtolower((string) Request::getHost());
        if ($host === '') {
            return false;
        }

        $platformHosts = array_map('strtolower', config('platform.hosts', []));

        return in_array($host, $platformHosts, true);
    }

    public function isTenantHostRequest(): bool
    {
        $host = strtolower((string) Request::getHost());
        if ($host === '' || $this->isPlatformHostRequest()) {
            return false;
        }

        return true;
    }

    public function displayName(?Tenant $tenant = null): string
    {
        if ($this->isPlatformAudience()) {
            return $this->platformName();
        }

        $tenant ??= TenantService::getTenant();

        $siteName = SiteSetting::get('site_name');
        if (is_string($siteName) && trim($siteName) !== '') {
            return trim($siteName);
        }

        if ($tenant instanceof Tenant && filled($tenant->name)) {
            return (string) $tenant->name;
        }

        return $this->platformName();
    }

    public function fromName(?Tenant $tenant = null): string
    {
        return $this->displayName($tenant);
    }

    public function subject(string $action, ?Tenant $tenant = null): string
    {
        return $this->displayName($tenant).' — '.$action;
    }

    public function platformName(): string
    {
        $fromName = config('mail.from.name');
        if (is_string($fromName) && trim($fromName) !== '' && ! str_contains(strtolower($fromName), 'example')) {
            return trim($fromName);
        }

        return 'EtosCoach';
    }

    /** @deprecated Use platformName() */
    public function platformFromName(): string
    {
        return $this->platformName();
    }

    public function forceAudience(string $audience): void
    {
        config(['mail.branding_audience' => $audience]);
    }
}
