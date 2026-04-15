<?php

namespace Database\Seeders\Tenants;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // seed data here
        \App\Models\User::firstOrCreate(
            ['email' => 'admin@tenant.com'],
            [
                'name' => 'Tenant Admin',
                'password' => bcrypt('password'),
            ]
        );
    }
}


