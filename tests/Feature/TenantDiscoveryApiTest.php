<?php

namespace Tests\Feature;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantDiscoveryApiTest extends TestCase
{
    public function test_discover_requires_code(): void
    {
        $this->getJson('/api/v1/organizations/discover')
            ->assertStatus(422);
    }

    public function test_discover_returns_organization_without_db_name(): void
    {
        $tenant = Tenant::on('system')->where('status', 'active')->whereNotNull('join_code')->first();
        if (! $tenant) {
            $this->markTestSkipped('No active tenant with join_code');
        }

        $response = $this->getJson('/api/v1/organizations/discover?code='.$tenant->join_code);

        $response->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('organization.join_code', $tenant->join_code)
            ->assertJsonPath('organization.tenant_domain', $tenant->domain)
            ->assertJsonMissingPath('organization.db_name');
    }

    public function test_discover_unknown_code_returns_404(): void
    {
        $this->getJson('/api/v1/organizations/discover?code=ZZZNOPE999')
            ->assertStatus(404)
            ->assertJsonPath('error', 'organization_not_found');
    }
}
