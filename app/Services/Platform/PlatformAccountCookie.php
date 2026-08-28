<?php

namespace App\Services\Platform;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;

class PlatformAccountCookie
{
    public function name(): string
    {
        return (string) config('platform.cookie', 'etos_platform');
    }

    public function put(Tenant $tenant, string $email, string $name, bool $isOwner = false): SymfonyCookie
    {
        $payload = encrypt([
            'tenant_id' => $tenant->id,
            'email' => $email,
            'name' => $name,
            'is_owner' => $isOwner,
            'exp' => now()->addDays(7)->timestamp,
        ]);

        return Cookie::make(
            $this->name(),
            $payload,
            60 * 24 * 7,
            '/',
            $this->cookieDomain(),
            ! app()->environment('local'),
            true,
            false,
            $this->cookieSameSite()
        );
    }

    public function forget(): SymfonyCookie
    {
        return Cookie::forget($this->name(), '/', $this->cookieDomain());
    }

    /**
     * @return array{tenant_id:int,email:string,name:string,is_owner:bool}|null
     */
    public function read(Request $request): ?array
    {
        $raw = $request->cookie($this->name());
        if (! is_string($raw) || $raw === '') {
            return null;
        }

        try {
            $data = decrypt($raw);
        } catch (\Throwable) {
            return null;
        }

        if (! is_array($data) || empty($data['exp']) || (int) $data['exp'] < time()) {
            return null;
        }

        return [
            'tenant_id' => (int) ($data['tenant_id'] ?? 0),
            'email' => (string) ($data['email'] ?? ''),
            'name' => (string) ($data['name'] ?? ''),
            'is_owner' => (bool) ($data['is_owner'] ?? false),
        ];
    }

    private function cookieDomain(): ?string
    {
        $domain = (string) config('platform.cookie_domain');
        if (app()->environment('local') || $domain === '') {
            return null;
        }

        return $domain;
    }

    private function cookieSameSite(): string
    {
        return app()->environment('local') ? 'lax' : 'none';
    }
}
