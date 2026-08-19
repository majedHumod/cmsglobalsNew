<?php

namespace App\Services\Tenant;

use App\Models\LandingPage;
use Illuminate\Support\Facades\Artisan;

class TenantDefaultContentService
{
    /**
     * Seed default public tenant content and report whether an active landing page exists afterwards.
     *
     * @return array{output: string, has_active_landing_page: bool}
     */
    public function seedDefaultPublicContent(): array
    {
        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Tenants\\DefaultTenantContentSeeder',
            '--database' => 'tenant',
            '--force' => true,
        ]);

        return [
            'output' => Artisan::output(),
            'has_active_landing_page' => LandingPage::query()->where('is_active', true)->exists(),
        ];
    }
}
