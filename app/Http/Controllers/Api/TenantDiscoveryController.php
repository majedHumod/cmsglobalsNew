<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Tenant\TenantDiscoveryService;
use Illuminate\Http\Request;

class TenantDiscoveryController extends Controller
{
    public function __construct(
        private readonly TenantDiscoveryService $discovery
    ) {}

    /**
     * Resolve organization by join code (Classera-style entry).
     * No tenant middleware — reads system.tenants only.
     */
    public function discover(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|min:2|max:32',
        ]);

        $tenant = $this->discovery->findActiveByJoinCode($validated['code']);

        if (! $tenant || empty($tenant->domain)) {
            return response()->json([
                'message' => 'لم يتم العثور على منظمة بهذا الرمز، أو أنها غير مفعّلة.',
                'error' => 'organization_not_found',
            ], 404);
        }

        return response()->json([
            'status' => 'ok',
            'organization' => $this->discovery->toPublicPayload($tenant),
            'next' => [
                'store_locally' => ['tenant_domain', 'join_code', 'name', 'logo_url'],
                'login' => url('/api/v1/auth/login'),
                'required_header' => 'X-Tenant-Domain',
            ],
        ]);
    }

    /**
     * Optional name/code search for "find my club" UX.
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'q' => 'required|string|min:2|max:80',
            'limit' => 'nullable|integer|min:1|max:25',
        ]);

        $tenants = $this->discovery->searchActive(
            $validated['q'],
            (int) ($validated['limit'] ?? 10)
        );

        return response()->json([
            'status' => 'ok',
            'query' => $validated['q'],
            'organizations' => $tenants
                ->filter(fn ($tenant) => ! empty($tenant->domain))
                ->map(fn ($tenant) => $this->discovery->toPublicPayload($tenant))
                ->values(),
        ]);
    }
}
