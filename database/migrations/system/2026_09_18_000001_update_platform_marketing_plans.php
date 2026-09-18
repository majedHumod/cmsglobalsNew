<?php

use Database\Seeders\system\PlansSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        (new PlansSeeder)->run();
    }

    public function down(): void
    {
        DB::connection('system')->table('plans')
            ->whereIn('code', ['pro_monthly', 'pro_yearly', 'clubs_monthly', 'clubs_yearly'])
            ->update(['active' => false]);

        foreach ([
            [
                'code' => 'subdomain_basic',
                'name' => 'خطة شهرية - سب دومين',
                'price' => 99.00,
                'interval' => 'monthly',
                'features' => json_encode([
                    'سب-دومين مجاني',
                    'صفحة هبوط افتراضية جاهزة',
                    'إدارة تمارين وجداول ووجبات',
                ], JSON_UNESCAPED_UNICODE),
            ],
            [
                'code' => 'subdomain_yearly',
                'name' => 'خطة سنوية - سب دومين',
                'price' => 999.00,
                'interval' => 'yearly',
                'features' => json_encode([
                    'سب-دومين مجاني',
                    'صفحة هبوط افتراضية جاهزة',
                    'خصم على السعر الشهري',
                ], JSON_UNESCAPED_UNICODE),
            ],
        ] as $plan) {
            DB::connection('system')->table('plans')
                ->where('code', $plan['code'])
                ->update([
                    'name' => $plan['name'],
                    'price' => $plan['price'],
                    'interval' => $plan['interval'],
                    'features' => $plan['features'],
                    'active' => true,
                    'updated_at' => now(),
                ]);
        }
    }
};
