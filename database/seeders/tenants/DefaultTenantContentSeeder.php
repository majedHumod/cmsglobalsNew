<?php

namespace Database\Seeders\Tenants;

use App\Models\Faq;
use App\Models\LandingPage;
use App\Models\MealPlan;
use App\Models\NutritionDiscount;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Database\Seeder;

class DefaultTenantContentSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::query()->value('id');

        if (!$adminId) {
            return;
        }

        $this->seedLandingPage($adminId);
        $this->seedMealPlans($adminId);
        $this->seedTestimonials($adminId);
        $this->seedTrainingSessions($adminId);
        $this->seedNutritionDiscounts();
        $this->seedHomepageSettings();

        LandingPage::clearCache();
        Faq::clearCache();
        Testimonial::clearCache();
        TrainingSession::clearCache();
        SiteSetting::clearAllCache();
    }

    private function seedLandingPage(int $adminId): void
    {
        $landingPage = LandingPage::query()
            ->where('is_active', true)
            ->latest()
            ->first();

        if ($landingPage) {
            return;
        }

        LandingPage::create([
            'title' => 'ابدأ رحلتك الرقمية مع EtosCoach',
            'subtitle' => 'منصة مرنة لإدارة التدريب والتغذية والحجوزات',
            'header_image' => 'landing-pages/default.jpg',
            'header_text_color' => '#ffffff',
            'show_join_button' => true,
            'join_button_text' => 'احجز استشارتك الآن',
            'join_button_url' => '/register',
            'join_button_color' => '#1A8E9A',
            'content' => '<p>وفّر على عملائك تجربة احترافية تشمل الخطط الغذائية، الجلسات التدريبية، والعروض الخاصة مع لوحة تحكم سهلة وسريعة.</p>',
            'meta_title' => 'EtosCoach | منصة للياقة والتغذية',
            'meta_description' => 'منصة متكاملة لإدارة المحتوى واللياقة والتغذية للمدربين والمراكز الرياضية.',
            'is_active' => true,
            'user_id' => $adminId,
        ]);
    }

    private function seedMealPlans(int $adminId): void
    {
        $plans = [
            [
                'name' => 'فطور عالي البروتين',
                'description' => 'وجبة مناسبة لبداية اليوم مع توازن ممتاز بين البروتين والكربوهيدرات.',
                'meal_type' => 'breakfast',
                'calories' => 420,
                'protein' => 30,
                'carbs' => 38,
                'fats' => 14,
                'ingredients' => "بيض عدد 3\nشوفان نصف كوب\nزبادي يوناني\nتوت مشكل",
                'instructions' => 'اخلط الشوفان مع الزبادي، وقدّم البيض مسلوقا أو أومليت بجانب التوت.',
                'prep_time' => 10,
                'cook_time' => 8,
                'servings' => 1,
                'difficulty' => 'easy',
                'is_active' => true,
                'user_id' => $adminId,
            ],
            [
                'name' => 'غداء متوازن للدعم العضلي',
                'description' => 'وجبة غداء تساعد على الاستشفاء وتدعم الحفاظ على الطاقة خلال اليوم.',
                'meal_type' => 'lunch',
                'calories' => 610,
                'protein' => 42,
                'carbs' => 52,
                'fats' => 18,
                'ingredients' => "صدر دجاج مشوي\nأرز بسمتي\nخضار مطهوة\nسلطة خضراء",
                'instructions' => 'اشوِ الدجاج وقدّمه مع الأرز والخضار والسلطة.',
                'prep_time' => 15,
                'cook_time' => 25,
                'servings' => 1,
                'difficulty' => 'easy',
                'is_active' => true,
                'user_id' => $adminId,
            ],
            [
                'name' => 'وجبة خفيفة قبل التمرين',
                'description' => 'خيار سريع قبل التدريب يمنح طاقة خفيفة دون شعور بالثقل.',
                'meal_type' => 'snack',
                'calories' => 260,
                'protein' => 12,
                'carbs' => 34,
                'fats' => 8,
                'ingredients' => "موزة\nزبدة فول سوداني\nخبز حبوب كاملة",
                'instructions' => 'افرد زبدة الفول السوداني على الخبز وقدّمها مع الموز.',
                'prep_time' => 5,
                'cook_time' => 0,
                'servings' => 1,
                'difficulty' => 'easy',
                'is_active' => true,
                'user_id' => $adminId,
            ],
        ];

        foreach ($plans as $plan) {
            MealPlan::firstOrCreate(
                ['name' => $plan['name'], 'user_id' => $adminId],
                $plan
            );
        }
    }

    private function seedTestimonials(int $adminId): void
    {
        $testimonials = [
            [
                'name' => 'سارة محمد',
                'story_content' => 'بعد إطلاق موقعي عبر المنصة أصبحت إدارة الجلسات والحجوزات أسهل بكثير، وبدأ العملاء يتابعون المحتوى بانتظام.',
                'is_visible' => true,
                'sort_order' => 1,
                'user_id' => $adminId,
            ],
            [
                'name' => 'علي خالد',
                'story_content' => 'استطعت عرض الخطط الغذائية والعروض بشكل احترافي، وهذا زاد من استفسارات العملاء والتحويلات بشكل ملحوظ.',
                'is_visible' => true,
                'sort_order' => 2,
                'user_id' => $adminId,
            ],
            [
                'name' => 'ريم عبدالله',
                'story_content' => 'المنصة اختصرت علي الكثير من الوقت في المتابعة اليدوية، وأصبح لدي حضور رقمي أوضح وأكثر تنظيما.',
                'is_visible' => true,
                'sort_order' => 3,
                'user_id' => $adminId,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::firstOrCreate(
                ['name' => $testimonial['name'], 'user_id' => $adminId],
                $testimonial
            );
        }
    }

    private function seedTrainingSessions(int $adminId): void
    {
        $sessions = [
            [
                'title' => 'جلسة تقييم لياقي أولية',
                'description' => 'جلسة تشخيصية لتحديد مستوى اللياقة ووضع توصيات تدريبية وغذائية أولية.',
                'price' => 149.00,
                'duration_hours' => 1,
                'is_visible' => true,
                'sort_order' => 1,
                'user_id' => $adminId,
            ],
            [
                'title' => 'جلسة متابعة وتحديث خطة',
                'description' => 'جلسة لمراجعة النتائج وتحديث برنامج التدريب أو التغذية حسب التقدم الحالي.',
                'price' => 199.00,
                'duration_hours' => 1,
                'is_visible' => true,
                'sort_order' => 2,
                'user_id' => $adminId,
            ],
        ];

        foreach ($sessions as $session) {
            TrainingSession::firstOrCreate(
                ['title' => $session['title'], 'user_id' => $adminId],
                $session
            );
        }
    }

    private function seedNutritionDiscounts(): void
    {
        $discounts = [
            [
                'name' => 'عرض البداية للعملاء الجدد',
                'discount_percentage' => 15,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonths(2)->toDateString(),
                'is_active' => true,
            ],
            [
                'name' => 'خصم الاشتراك الشهري',
                'discount_percentage' => 10,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonths(1)->toDateString(),
                'is_active' => true,
            ],
        ];

        foreach ($discounts as $discount) {
            NutritionDiscount::firstOrCreate(
                ['name' => $discount['name']],
                $discount
            );
        }
    }

    private function seedHomepageSettings(): void
    {
        SiteSetting::set('training_sessions_count', 4, 'homepage', 'integer', 'عدد جلسات التدريب المعروضة في الصفحة الرئيسية');
        SiteSetting::set('homepage_show_testimonials', true, 'homepage', 'boolean', 'إظهار قصص النجاح في الصفحة الرئيسية');
        SiteSetting::set('homepage_show_faqs', true, 'homepage', 'boolean', 'إظهار الأسئلة الشائعة في الصفحة الرئيسية');
    }
}
