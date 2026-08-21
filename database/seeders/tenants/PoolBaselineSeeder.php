<?php

namespace Database\Seeders\Tenants;

use Illuminate\Database\Seeder;

/**
 * Clean baseline for pooled tenant databases.
 *
 * Seeds only platform essentials (settings, membership catalog, permissions).
 * Does NOT create coaches, clients, placeholder admins, or marketing content.
 * Subscriber user + public starter content are created at provision time.
 */
class PoolBaselineSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SiteSettingsSeeder::class,
            MembershipTypesSeeder::class,
            SubscriptionPlansSeeder::class,
            FaqsSeeder::class,
            PermissionsSeeder::class,
        ]);
    }
}
