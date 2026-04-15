<?php

namespace Database\Seeders\system;

use Illuminate\Database\Seeder;
use App\Models\Billing\Plan;

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        // Basic monthly plan
        Plan::updateOrCreate(
            ['code' => 'subdomain_basic'],
            [
                'name' => 'خطة شهرية - سب دومين',
                'price' => 99.00,
                'interval' => 'monthly',
                'currency' => 'SAR',
                'features' => [
                    'سب-دومين مجاني',
                    'صفحة هبوط افتراضية جاهزة',
                    'إدارة تمارين وجداول ووجبات',
                ],
                'active' => true,
            ]
        );

        // Yearly plan
        Plan::updateOrCreate(
            ['code' => 'subdomain_yearly'],
            [
                'name' => 'خطة سنوية - سب دومين',
                'price' => 999.00,
                'interval' => 'yearly',
                'currency' => 'SAR',
                'features' => [
                    'سب-دومين مجاني',
                    'صفحة هبوط افتراضية جاهزة',
                    'خصم على السعر الشهري',
                ],
                'active' => true,
            ]
        );
    }
}

