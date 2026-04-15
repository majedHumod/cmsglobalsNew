<?php

namespace Database\Seeders\Tenants;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class BaseTenantSeeder extends Seeder
{
    public function run(): void
    {
        // Seed core data needed for any fresh tenant
        $this->call([
            DatabaseSeeder::class, // create a default tenant admin user
            SiteSettingsSeeder::class,
            MembershipTypesSeeder::class,
            FaqsSeeder::class,
            PermissionsSeeder::class,
            DefaultTenantContentSeeder::class,
        ]);

        // Ensure first user has admin role after roles are created
        try {
            if (\Schema::hasTable('users') && \Schema::hasTable('roles') && \Schema::hasTable('model_has_roles')) {
                $firstUser = \App\Models\User::first();
                if ($firstUser && method_exists($firstUser, 'assignRole')) {
                    // Assign admin if not already
                    if (!$firstUser->hasRole('admin')) {
                        $firstUser->assignRole('admin');
                    }
                }
            }
        } catch (\Throwable $e) {
            // ignore optional failure
        }

    }
}

