<?php

namespace Database\Seeders\Tenants;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutSchedule;
use App\Models\MealPlan;
use App\Models\Page;
use App\Models\Faq;
use App\Models\Testimonial;
use App\Models\NutritionDiscount;

class DemoTenantSeeder extends Seeder
{
    public function run(): void
    {
        // Basic users (trainer + viewer) if users table exists
        if ($this->tableExists('users')) {
            $trainer = User::firstOrCreate(
                ['email' => 'trainer@demo.local'],
                [
                    'name' => 'Demo Trainer',
                    'password' => bcrypt('password'),
                ]
            );
            $viewer = User::firstOrCreate(
                ['email' => 'viewer@demo.local'],
                [
                    'name' => 'Demo Viewer',
                    'password' => bcrypt('password'),
                ]
            );
        } else {
            $trainer = null;
        }

        // Workouts
        if ($this->tableExists('workouts')) {
            Workout::firstOrCreate(
                ['name' => 'تمارين قوة للمبتدئين'],
                [
                    'description' => 'برنامج تمارين قوة لمدة 30 دقيقة.',
                    'duration' => 30,
                    'difficulty' => 'easy',
                    'status' => true,
                    'user_id' => optional($trainer)->id,
                ]
            );
            Workout::firstOrCreate(
                ['name' => 'تمارين HIIT سريعة'],
                [
                    'description' => 'جلسة HIIT تنشيطية لمدة 20 دقيقة.',
                    'duration' => 20,
                    'difficulty' => 'medium',
                    'status' => true,
                    'user_id' => optional($trainer)->id,
                ]
            );
        }

        // Meal plans
        if ($this->tableExists('meal_plans')) {
            MealPlan::firstOrCreate(
                ['name' => 'خطة وجبات أسبوعية متوازنة'],
                [
                    'description' => 'خطة أسبوعية بمتوسط 2000 سعرة يوميًا.',
                    'meal_type' => 'lunch',
                    'calories' => 650,
                    'protein' => 35,
                    'carbs' => 70,
                    'fats' => 20,
                    'ingredients' => 'دجاج، أرز، خضار، زيت زيتون',
                    'instructions' => 'اطبخ الدجاج والأرز وأضف الخضار.',
                    'prep_time' => 15,
                    'cook_time' => 30,
                    'servings' => 2,
                    'difficulty' => 'easy',
                    'is_active' => true,
                    'user_id' => optional($trainer)->id,
                ]
            );
        }

        // Pages
        if ($this->tableExists('pages')) {
            Page::firstOrCreate(
                ['slug' => 'about'],
                ['title' => 'عن المنصّة', 'content' => 'EtosCoach—حل متكامل لإدارة التمارين والوجبات والحجوزات.', 'user_id' => optional($trainer)->id]
            );
        }

        // FAQs
        if ($this->tableExists('faqs')) {
            Faq::firstOrCreate(
                ['question' => 'كيف أبدأ؟'],
                ['answer' => 'أنشئ حسابًا، أضف برامجك، وابدأ بمشاركة الروابط مع عملائك.', 'user_id' => optional($trainer)->id]
            );
            Faq::firstOrCreate(
                ['question' => 'هل يمكن تخصيص الهوية؟'],
                ['answer' => 'نعم، يمكنك ضبط الألوان والشعار من الإعدادات.', 'user_id' => optional($trainer)->id]
            );
        }

        // Testimonials
        if ($this->tableExists('testimonials')) {
            Testimonial::firstOrCreate(
                ['name' => 'كابتن سارة'],
                ['story_content' => 'وفّر عليّ ساعات أسبوعيًا، أداة موثوقة للمدربين.','user_id' => optional($trainer)->id]
            );
        }

        // Nutrition discounts
        if ($this->tableExists('nutrition_discounts')) {
            NutritionDiscount::firstOrCreate(
                ['name' => 'خصم تجريبي 10%'],
                ['discount_percentage' => 10, 'start_date' => now()->toDateString(), 'end_date' => now()->addDays(30)->toDateString(), 'image' => null, 'is_active' => true]
            );
        }
    }

    private function tableExists(string $table): bool
    {
        try {
            return \Schema::hasTable($table);
        } catch (\Throwable $e) {
            return false;
        }
    }
}

