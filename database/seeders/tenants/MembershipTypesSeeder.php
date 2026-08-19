<?php

namespace Database\Seeders\Tenants;

use App\Models\MembershipType;
use Illuminate\Database\Seeder;

class MembershipTypesSeeder extends Seeder
{
    /**
     * Access paths only — commercial price/duration/features belong on SubscriptionPlan.
     */
    public function run(): void
    {
        MembershipType::firstOrCreate([
            'slug' => 'admin',
        ], [
            'name' => 'مدير النظام',
            'description' => 'مسار إداري بصلاحيات كاملة لإدارة النظام',
            'price' => 0,
            'duration_days' => 36500,
            'features' => null,
            'is_active' => true,
            'is_protected' => true,
            'sort_order' => 0,
        ]);

        MembershipType::firstOrCreate([
            'slug' => 'free',
        ], [
            'name' => 'عضوية مجانية',
            'description' => 'مسار أساسي للوصول للمحتوى المجاني',
            'price' => 0,
            'duration_days' => 30,
            'features' => null,
            'is_active' => true,
            'is_protected' => false,
            'sort_order' => 1,
        ]);

        MembershipType::firstOrCreate([
            'slug' => 'monthly',
        ], [
            'name' => 'عضوية شهرية',
            'description' => 'مسار مدفوع للمحتوى الشهري والمزايا الإضافية',
            'price' => 0,
            'duration_days' => 30,
            'features' => null,
            'is_active' => true,
            'is_protected' => false,
            'sort_order' => 2,
        ]);

        MembershipType::firstOrCreate([
            'slug' => 'yearly',
        ], [
            'name' => 'عضوية سنوية',
            'description' => 'مسار سنوي بمحتوى ومزايا أوسع',
            'price' => 0,
            'duration_days' => 30,
            'features' => null,
            'is_active' => true,
            'is_protected' => false,
            'sort_order' => 3,
        ]);

        MembershipType::firstOrCreate([
            'slug' => 'vip',
        ], [
            'name' => 'عضوية VIP',
            'description' => 'مسار VIP للمحتوى الحصري والمتابعة المتقدمة',
            'price' => 0,
            'duration_days' => 30,
            'features' => null,
            'is_active' => true,
            'is_protected' => false,
            'sort_order' => 4,
        ]);
    }
}
