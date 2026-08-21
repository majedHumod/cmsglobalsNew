<?php

namespace Database\Seeders\Tenants;

use Illuminate\Database\Seeder;

class BaseTenantSeeder extends Seeder
{
    public function run(): void
    {
        // Clean essentials only — no placeholder admin, no coaches/clients, no marketing content.
        // Public starter content is seeded after the real subscriber user is created.
        $this->call([
            PoolBaselineSeeder::class,
        ]);
    }
}
