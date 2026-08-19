<?php

namespace Database\Seeders\Tenants;

use App\Models\MembershipType;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SubscriptionPlansSeeder extends Seeder
{
    /**
     * Sellable offers live here: price, duration, marketing features, gender scope.
     */
    public function run(): void
    {
        $catalog = [
            'admin' => [
                [
                    'slug_suffix' => 'default',
                    'name' => 'خطة الإدارة',
                    'duration_days' => 36500,
                    'price' => 0,
                    'gender_scope' => 'all',
                    'features' => ['وصول إداري كامل'],
                ],
            ],
            'free' => [
                [
                    'slug_suffix' => 'default',
                    'name' => 'الخطة المجانية',
                    'duration_days' => 365,
                    'price' => 0,
                    'gender_scope' => 'all',
                    'features' => [
                        'الوصول للمحتوى المجاني',
                        'إنشاء الملاحظات الشخصية',
                        'تصفح الوجبات العامة',
                    ],
                ],
            ],
            'monthly' => [
                [
                    'slug_suffix' => 'default',
                    'name' => 'شهري أساسي',
                    'duration_days' => 30,
                    'price' => 29.99,
                    'gender_scope' => 'all',
                    'features' => [
                        'الوصول للمحتوى المدفوع',
                        'وجبات غذائية مخصصة',
                        'دعم فني أولوي',
                    ],
                ],
            ],
            'yearly' => [
                [
                    'slug_suffix' => 'default',
                    'name' => 'سنوي أساسي',
                    'duration_days' => 365,
                    'price' => 299.99,
                    'gender_scope' => 'all',
                    'features' => [
                        'جميع مزايا المسار السنوي',
                        'استشارات غذائية',
                        'محتوى حصري للأعضاء السنويين',
                    ],
                ],
            ],
            'vip' => [
                [
                    'slug_suffix' => 'default',
                    'name' => 'VIP شهري',
                    'duration_days' => 30,
                    'price' => 99.99,
                    'gender_scope' => 'all',
                    'features' => [
                        'محتوى حصري VIP',
                        'استشارات شخصية',
                        'دعم فني 24/7',
                    ],
                ],
            ],
        ];

        MembershipType::query()
            ->orderBy('sort_order')
            ->get()
            ->each(function (MembershipType $membershipType) use ($catalog) {
                $offers = $catalog[$membershipType->slug] ?? [[
                    'slug_suffix' => 'default',
                    'name' => $membershipType->name.' - عرض افتراضي',
                    'duration_days' => max(1, (int) ($membershipType->duration_days ?: 30)),
                    'price' => (float) ($membershipType->price ?: 0),
                    'gender_scope' => 'all',
                    'features' => is_array($membershipType->features) ? $membershipType->features : [],
                ]];

                foreach ($offers as $index => $offer) {
                    $slug = Str::slug($membershipType->slug.'-'.$offer['slug_suffix']);

                    SubscriptionPlan::firstOrCreate(
                        ['slug' => $slug],
                        [
                            'membership_type_id' => $membershipType->id,
                            'name' => $offer['name'],
                            'description' => $membershipType->description,
                            'duration_days' => $offer['duration_days'],
                            'price' => $offer['price'],
                            'gender_scope' => $offer['gender_scope'],
                            'features' => $offer['features'],
                            'is_active' => (bool) $membershipType->is_active,
                            'sort_order' => ((int) $membershipType->sort_order * 10) + $index,
                        ]
                    );
                }
            });
    }
}
