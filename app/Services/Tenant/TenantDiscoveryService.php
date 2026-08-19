<?php

namespace App\Services\Tenant;

use App\Models\Tenant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TenantDiscoveryService
{
    public function normalizeJoinCode(?string $code): string
    {
        return Str::upper(preg_replace('/\s+/', '', trim((string) $code)) ?: '');
    }

    public function findActiveByJoinCode(string $code): ?Tenant
    {
        $code = $this->normalizeJoinCode($code);
        if ($code === '') {
            return null;
        }

        return Tenant::query()
            ->where('status', 'active')
            ->whereRaw('UPPER(join_code) = ?', [$code])
            ->first();
    }

    /**
     * @return Collection<int, Tenant>
     */
    public function searchActive(string $query, int $limit = 10): Collection
    {
        $query = trim($query);
        if ($query === '') {
            return collect();
        }

        $like = '%'.$query.'%';

        return Tenant::query()
            ->where('status', 'active')
            ->where(function ($builder) use ($like, $query) {
                $builder->where('name', 'like', $like)
                    ->orWhere('slug', 'like', $like)
                    ->orWhere('domain', 'like', $like)
                    ->orWhere('subdomain', 'like', $like)
                    ->orWhereRaw('UPPER(join_code) = ?', [Str::upper($query)]);
            })
            ->orderBy('name')
            ->limit(max(1, min(25, $limit)))
            ->get();
    }

    /**
     * Public payload for mobile — never expose db_name.
     *
     * @return array<string, mixed>
     */
    public function toPublicPayload(Tenant $tenant): array
    {
        $logo = $tenant->logo;
        $logoUrl = null;
        if (is_string($logo) && $logo !== '') {
            $logoUrl = Str::startsWith($logo, ['http://', 'https://'])
                ? $logo
                : url(Storage::url($logo));
        }

        return [
            'join_code' => $tenant->join_code,
            'tenant_domain' => $tenant->domain,
            'name' => $tenant->name,
            'slug' => $tenant->slug,
            'subdomain' => $tenant->subdomain,
            'logo' => $logo,
            'logo_url' => $logoUrl,
            'status' => $tenant->status,
            'headers' => [
                'X-Tenant-Domain' => $tenant->domain,
            ],
            'deep_link' => 'etoscoach://join/'.urlencode((string) $tenant->join_code),
        ];
    }
}
