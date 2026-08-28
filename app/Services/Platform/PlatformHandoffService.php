<?php

namespace App\Services\Platform;

use App\Models\Tenant;

class PlatformHandoffService
{
    public function create(Tenant $tenant, string $email): string
    {
        return encrypt([
            'tenant_id' => $tenant->id,
            'email' => strtolower(trim($email)),
            'exp' => now()->addMinutes(3)->timestamp,
        ]);
    }

    /**
     * @return array{tenant_id:int,email:string,is_owner:bool}|null
     */
    public function resolve(string $token, Tenant $tenant): ?array
    {
        try {
            $data = decrypt($token);
        } catch (\Throwable) {
            return null;
        }

        if (! is_array($data) || (int) ($data['tenant_id'] ?? 0) !== (int) $tenant->id) {
            return null;
        }

        if (empty($data['exp']) || (int) $data['exp'] < time()) {
            return null;
        }

        $email = strtolower(trim((string) ($data['email'] ?? '')));
        if ($email === '') {
            return null;
        }

        return [
            'tenant_id' => (int) $tenant->id,
            'email' => $email,
            'is_owner' => false,
        ];
    }

    public function enterUrl(Tenant $tenant, string $token): string
    {
        $base = app(TenantAccessService::class)->workspaceEnterUrl($tenant);

        return $base.'?handoff='.urlencode($token);
    }

    public function handoffUrl(): string
    {
        return rtrim((string) config('platform.app_url'), '/').'/platform/handoff';
    }
}
