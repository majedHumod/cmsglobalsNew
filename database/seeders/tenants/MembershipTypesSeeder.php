<?php

namespace Database\Seeders\Tenants;

use App\Models\MembershipType;
use Illuminate\Database\Seeder;

class MembershipTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // عضوية الأدمن المحمية
        MembershipType::firstOrCreate([
            'slug' => 'admin',
        ],[
            'name' => 'مدير النظام',
            'description' => 'عضوية مدير النظام مع صلاحيات كاملة',
            'price' => 0,
            'duration_days' => 36500, // 100 سنة
            'features' => [
                'صلاحيات كاملة للنظام',
                'إدارة جميع المحتويات',
                'إدارة المستخدمين',
                'إدارة أنواع العضويات',
                'الوصول لجميع التقارير',
                'إعدادات النظام'
            ],
            'is_active' => true,
            'is_protected' => true, // محمي من التعديل والحذف
            'sort_order' => 0
        ]);

        // عضوية مجانية أساسية
        MembershipType::firstOrCreate([
            'slug' => 'free',
        ],[
            'name' => 'عضوية مجانية',
            'description' => 'عضوية مجانية مع إمكانيات أساسية',
            'price' => 0,
            'duration_days' => 365,
            'features' => [
                'الوصول للمحتوى المجاني',
                'إنشاء الملاحظات الشخصية',
                'تصفح الوجبات العامة',
                'عرض الصفحات العامة'
            ],
            'is_active' => true,
            'is_protected' => false,
            'sort_order' => 1
        ]);

        // عضوية شهرية مدفوعة
        MembershipType::firstOrCreate([
            'slug' => 'monthly',
        ],[
            'name' => 'عضوية شهرية',
            'description' => 'عضوية شهرية مع مميزات إضافية',
            'price' => 29.99,
            'duration_days' => 30,
            'features' => [
                'جميع مميزات العضوية المجانية',
                'الوصول للمحتوى المدفوع',
                'إنشاء وجبات غذائية مخصصة',
                'تحميل الصور',
                'دعم فني أولوي'
            ],
            'is_active' => true,
            'is_protected' => false,
            'sort_order' => 2
        ]);

        // عضوية سنوية مدفوعة
        MembershipType::firstOrCreate([
            'slug' => 'yearly',
        ],[
            'name' => 'عضوية سنوية',
            'description' => 'عضوية سنوية مع خصم كبير',
            'price' => 299.99,
            'duration_days' => 365,
            'features' => [
                'جميع مميزات العضوية الشهرية',
                'خصم 17% على السعر الشهري',
                'استشارات غذائية مجانية',
                'تقارير تفصيلية',
                'أولوية في الدعم الفني',
                'محتوى حصري للأعضاء السنويين'
            ],
            'is_active' => true,
            'is_protected' => false,
            'sort_order' => 3
        ]);

        // عضوية VIP
        MembershipType::firstOrCreate([
            'slug' => 'vip',
        ],[
            'name' => 'عضوية VIP',
            'description' => 'عضوية VIP مع جميع المميزات',
            'price' => 99.99,
            'duration_days' => 30,
            'features' => [
                'جميع مميزات العضويات السابقة',
                'محتوى حصري VIP',
                'استشارات شخصية مباشرة',
                'تخصيص كامل للواجهة',
                'تقارير متقدمة',
                'دعم فني 24/7',
                'وصول مبكر للمميزات الجديدة'
            ],
            'is_active' => true,
            'is_protected' => false,
            'sort_order' => 4
        ]);
    }
}