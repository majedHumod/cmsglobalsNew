<?php

namespace Database\Seeders\system;

use App\Models\Billing\Plan;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    /**
     * Platform catalog for the marketing site and Paylink checkout.
     *
     * Official list prices: 149 / 249 / 499 SAR monthly, 20% off annual.
     * Launch prices (until 2026-12-17): ~30% off, billed via the `price` column.
     * Existing codes subdomain_basic / subdomain_yearly stay so current
     * subscriptions and checkout links keep working. Tenant databases are untouched.
     */
    public static function catalog(): array
    {
        return [
            [
                'code' => 'subdomain_basic',
                'name' => 'أساسي — شهري',
                'price' => 99.00,
                'interval' => 'monthly',
                'currency' => 'SAR',
                'active' => true,
                'features' => [
                    'سعر الإطلاق 99 ر.س بدل 149 ر.س حتى 17 ديسمبر 2026',
                    'حتى 15 عميلاً نشطاً',
                    'مدرب واحد',
                    'موقع احترافي باسم ناديك',
                    'تمارين وجداول أسبوعية',
                    'بوابة يومية للعميل',
                    'رسائل وإشعارات',
                    'خطط وجبات أساسية',
                ],
            ],
            [
                'code' => 'subdomain_yearly',
                'name' => 'أساسي — سنوي',
                'price' => 999.00,
                'interval' => 'yearly',
                'currency' => 'SAR',
                'active' => true,
                'features' => [
                    'سعر الإطلاق 999 ر.س بدل 1,428 ر.س حتى 17 ديسمبر 2026',
                    'حتى 15 عميلاً نشطاً',
                    'مدرب واحد',
                    'موقع احترافي باسم ناديك',
                    'تمارين وجداول أسبوعية',
                    'بوابة يومية للعميل',
                    'رسائل وإشعارات',
                    'خطط وجبات أساسية',
                    'فوترة واحدة للسنة كاملة',
                ],
            ],
            [
                'code' => 'pro_monthly',
                'name' => 'احتراف — شهري',
                'price' => 174.00,
                'interval' => 'monthly',
                'currency' => 'SAR',
                'active' => true,
                'features' => [
                    'سعر الإطلاق 174 ر.س بدل 249 ر.س حتى 17 ديسمبر 2026',
                    'حتى 50 عميلاً نشطاً',
                    'حتى 3 مدربين',
                    'كل مزايا الأساسي',
                    'خطط غذائية ومكملات كاملة',
                    'حجوزات ودفع إلكتروني للعملاء',
                    'عضويات واشتراكات العملاء',
                    'عادات وتحديات',
                ],
            ],
            [
                'code' => 'pro_yearly',
                'name' => 'احتراف — سنوي',
                'price' => 1670.00,
                'interval' => 'yearly',
                'currency' => 'SAR',
                'active' => true,
                'features' => [
                    'سعر الإطلاق 1,670 ر.س بدل 2,388 ر.س حتى 17 ديسمبر 2026',
                    'حتى 50 عميلاً نشطاً',
                    'حتى 3 مدربين',
                    'كل مزايا الأساسي',
                    'خطط غذائية ومكملات كاملة',
                    'حجوزات ودفع إلكتروني للعملاء',
                    'عضويات واشتراكات العملاء',
                    'عادات وتحديات',
                    'فوترة واحدة للسنة كاملة',
                ],
            ],
            [
                'code' => 'clubs_monthly',
                'name' => 'أندية — شهري',
                'price' => 349.00,
                'interval' => 'monthly',
                'currency' => 'SAR',
                'active' => true,
                'features' => [
                    'سعر الإطلاق 349 ر.س بدل 499 ر.س حتى 17 ديسمبر 2026',
                    'حتى 150 عميلاً نشطاً',
                    'حتى 8 مدربين وصلاحيات',
                    'كل مزايا الاحتراف',
                    'تقارير ومتابعة أوسع',
                    'تهيئة هوية النادي',
                    'دعم أولوية',
                ],
            ],
            [
                'code' => 'clubs_yearly',
                'name' => 'أندية — سنوي',
                'price' => 3350.00,
                'interval' => 'yearly',
                'currency' => 'SAR',
                'active' => true,
                'features' => [
                    'سعر الإطلاق 3,350 ر.س بدل 4,788 ر.س حتى 17 ديسمبر 2026',
                    'حتى 150 عميلاً نشطاً',
                    'حتى 8 مدربين وصلاحيات',
                    'كل مزايا الاحتراف',
                    'تقارير ومتابعة أوسع',
                    'تهيئة هوية النادي',
                    'دعم أولوية',
                    'فوترة واحدة للسنة كاملة',
                ],
            ],
        ];
    }

    public function run(): void
    {
        foreach (self::catalog() as $plan) {
            Plan::updateOrCreate(
                ['code' => $plan['code']],
                $plan
            );
        }
    }
}
