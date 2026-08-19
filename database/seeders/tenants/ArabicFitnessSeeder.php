<?php

namespace Database\Seeders\Tenants;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Page;
use App\Models\MealPlan;
use App\Models\Workout;
use App\Models\WorkoutSchedule;
use App\Models\Article;
use App\Models\Faq;
use App\Models\LandingPage;
use App\Models\SiteSetting;
use App\Models\MembershipType;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class ArabicFitnessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء المستخدمين
        $this->createUsers();
        
        // إنشاء إعدادات الموقع
        $this->createSiteSettings();
        
        // إنشاء أنواع العضويات
        $this->createMembershipTypes();
        
        // إنشاء الصفحة الرئيسية
        $this->createLandingPage();
        
        // إنشاء الصفحات
        $this->createPages();
        
        // إنشاء الوجبات
        $this->createMealPlans();
        
        // إنشاء التمارين
        $this->createWorkouts();
        
        // إنشاء جدولة التمارين
        $this->createWorkoutSchedules();
        
        // إنشاء المقالات
        $this->createArticles();
        
        // إنشاء الأسئلة الشائعة
        $this->createFaqs();
    }

    private function createUsers()
    {
        // إنشاء المدرب الرئيسي
        $coach = User::create([
            'name' => 'أحمد محمد - مدرب اللياقة البدنية',
            'email' => 'coach@fitness.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // إنشاء الأدوار إذا لم تكن موجودة
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $coachRole = Role::firstOrCreate(['name' => 'coach', 'guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // منح الأدوار
        $coach->assignRole(['admin', 'coach']);

        // إنشاء مستخدمين إضافيين
        $user1 = User::create([
            'name' => 'سارة أحمد',
            'email' => 'sara@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $user1->assignRole('user');

        $user2 = User::create([
            'name' => 'محمد علي',
            'email' => 'mohamed@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $user2->assignRole('user');
    }

    private function createSiteSettings()
    {
        $this->command->info('إنشاء إعدادات الموقع...');
        
        // إعدادات عامة
        $generalSettings = [
            ['key' => 'site_name', 'value' => 'أكاديمية اللياقة البدنية', 'group' => 'general', 'type' => 'string', 'description' => 'اسم الموقع'],
            ['key' => 'site_description', 'value' => 'أكاديمية متخصصة في اللياقة البدنية والتغذية الصحية مع برامج تدريبية متقدمة ومتابعة شخصية من خبراء معتمدين', 'group' => 'general', 'type' => 'string', 'description' => 'وصف الموقع'],
            ['key' => 'primary_color', 'value' => '#dc2626', 'group' => 'general', 'type' => 'string', 'description' => 'اللون الرئيسي'],
            ['key' => 'secondary_color', 'value' => '#16a34a', 'group' => 'general', 'type' => 'string', 'description' => 'اللون الثانوي'],
            ['key' => 'footer_text', 'value' => '© ' . date('Y') . ' أكاديمية اللياقة البدنية. جميع الحقوق محفوظة. نحو حياة صحية أفضل.', 'group' => 'general', 'type' => 'string', 'description' => 'نص التذييل'],
        ];

        foreach ($generalSettings as $setting) {
            DB::table('site_settings')->updateOrInsert(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'group' => $setting['group'],
                    'type' => $setting['type'],
                    'description' => $setting['description'],
                    'is_public' => true,
                    'is_tenant_specific' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
        
        // معلومات الاتصال
        $contactSettings = [
            ['key' => 'contact_email', 'value' => 'info@fitness-academy.com', 'description' => 'البريد الإلكتروني'],
            ['key' => 'contact_phone', 'value' => '+966541234567', 'description' => 'رقم الهاتف'],
            ['key' => 'contact_whatsapp', 'value' => '+966541234567', 'description' => 'رقم الواتساب'],
            ['key' => 'contact_address', 'value' => 'الرياض، حي الملقا، شارع الأمير محمد بن عبدالعزيز', 'description' => 'العنوان'],
        ];

        foreach ($contactSettings as $setting) {
            DB::table('site_settings')->updateOrInsert(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'group' => 'contact',
                    'type' => 'string',
                    'description' => $setting['description'],
                    'is_public' => true,
                    'is_tenant_specific' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
        
        // وسائل التواصل الاجتماعي
        $socialSettings = [
            ['key' => 'social_facebook', 'value' => 'https://facebook.com/fitness.academy.sa', 'description' => 'فيسبوك'],
            ['key' => 'social_instagram', 'value' => 'https://instagram.com/fitness.academy.sa', 'description' => 'انستقرام'],
            ['key' => 'social_youtube', 'value' => 'https://youtube.com/c/fitnessacademysa', 'description' => 'يوتيوب'],
            ['key' => 'social_twitter', 'value' => 'https://twitter.com/fitnessacademysa', 'description' => 'تويتر'],
        ];

        foreach ($socialSettings as $setting) {
            DB::table('site_settings')->updateOrInsert(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'group' => 'social',
                    'type' => 'string',
                    'description' => $setting['description'],
                    'is_public' => true,
                    'is_tenant_specific' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function createMembershipTypes()
    {
        $membershipTypes = [
            [
                'name' => 'العضوية الأساسية',
                'slug' => 'basic-membership',
                'description' => 'عضوية مجانية تتيح الوصول للمحتوى الأساسي والتمارين العامة',
                'price' => 0,
                'duration_days' => 365,
                'features' => [
                    'الوصول للتمارين الأساسية',
                    'مكتبة الوجبات الصحية',
                    'نصائح اللياقة البدنية',
                    'دعم المجتمع'
                ],
                'is_active' => true,
                'is_protected' => true,
                'sort_order' => 1
            ],
            [
                'name' => 'العضوية الذهبية',
                'slug' => 'gold-membership',
                'description' => 'عضوية شاملة مع برامج تدريبية متقدمة وجداول غذائية مخصصة',
                'price' => 299.00,
                'duration_days' => 30,
                'features' => [
                    'جميع مميزات العضوية الأساسية',
                    'برامج تدريبية متقدمة',
                    'جداول غذائية مخصصة',
                    'متابعة شخصية أسبوعية',
                    'تمارين حصرية بالفيديو',
                    'استشارات غذائية'
                ],
                'is_active' => true,
                'is_protected' => false,
                'sort_order' => 2
            ],
            [
                'name' => 'العضوية البلاتينية',
                'slug' => 'platinum-membership',
                'description' => 'العضوية الأكثر تميزاً مع تدريب شخصي ومتابعة يومية',
                'price' => 599.00,
                'duration_days' => 30,
                'features' => [
                    'جميع مميزات العضوية الذهبية',
                    'تدريب شخصي مباشر (4 جلسات شهرياً)',
                    'متابعة يومية عبر الواتساب',
                    'برنامج غذائي مخصص بالكامل',
                    'تحليل تركيب الجسم',
                    'خطة تدريبية فردية',
                    'دعم فني أولوي 24/7'
                ],
                'is_active' => true,
                'is_protected' => false,
                'sort_order' => 3
            ]
        ];

        foreach ($membershipTypes as $type) {
            // Access path only — commercial offers are seeded via SubscriptionPlansSeeder / admin plans.
            MembershipType::create([
                'name' => $type['name'],
                'slug' => $type['slug'],
                'description' => $type['description'],
                'price' => 0,
                'duration_days' => 30,
                'features' => null,
                'is_active' => $type['is_active'],
                'is_protected' => $type['is_protected'],
                'sort_order' => $type['sort_order'],
            ]);
        }
    }

    private function createLandingPage()
    {
        $coach = User::first();
        
        LandingPage::create([
            'title' => 'حوّل جسمك، غيّر حياتك',
            'subtitle' => 'انضم لآلاف الأشخاص الذين حققوا أهدافهم في اللياقة البدنية مع برامجنا المتخصصة',
            'header_image' => 'https://images.pexels.com/photos/1552242/pexels-photo-1552242.jpeg',
            'header_text_color' => '#ffffff',
            'show_join_button' => true,
            'join_button_text' => 'ابدأ رحلتك الآن',
            'join_button_url' => '/register',
            'join_button_color' => '#ef4444',
            'content' => '
                <div class="text-center mb-12">
                    <h2 class="text-4xl font-bold text-gray-900 mb-6">لماذا تختار أكاديمية اللياقة البدنية؟</h2>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto">نحن لسنا مجرد صالة رياضية، بل شريكك في رحلة التحول الكاملة نحو حياة صحية ونشطة</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
                    <div class="text-center">
                        <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">برامج تدريبية متخصصة</h3>
                        <p class="text-gray-600">برامج مصممة خصيصاً لأهدافك سواء كانت خسارة الوزن، بناء العضلات، أو تحسين اللياقة العامة</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">تغذية علمية متوازنة</h3>
                        <p class="text-gray-600">خطط غذائية مدروسة علمياً تناسب نمط حياتك وتساعدك في تحقيق أهدافك بأسرع وقت</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">متابعة شخصية</h3>
                        <p class="text-gray-600">متابعة مستمرة من مدربين معتمدين لضمان تحقيق أفضل النتائج وتجنب الإصابات</p>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-2xl p-8 mb-16">
                    <div class="text-center mb-8">
                        <h2 class="text-3xl font-bold text-gray-900 mb-4">قصص نجاح ملهمة</h2>
                        <p class="text-lg text-gray-600">اكتشف كيف غيّر عملاؤنا حياتهم وحققوا أهدافهم</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="bg-white p-6 rounded-xl shadow-sm">
                            <div class="flex items-center mb-4">
                                <img src="https://images.pexels.com/photos/1239291/pexels-photo-1239291.jpeg" alt="سارة" class="w-16 h-16 rounded-full object-cover mr-4">
                                <div>
                                    <h4 class="font-bold text-gray-900">سارة محمد</h4>
                                    <p class="text-gray-600">خسرت 25 كيلو في 6 أشهر</p>
                                </div>
                            </div>
                            <p class="text-gray-700">"كانت رحلة مذهلة! البرنامج الغذائي والتدريبي ساعدني في تحقيق حلمي. الآن أشعر بثقة أكبر وصحة أفضل."</p>
                        </div>
                        
                        <div class="bg-white p-6 rounded-xl shadow-sm">
                            <div class="flex items-center mb-4">
                                <img src="https://images.pexels.com/photos/1681010/pexels-photo-1681010.jpeg" alt="أحمد" class="w-16 h-16 rounded-full object-cover mr-4">
                                <div>
                                    <h4 class="font-bold text-gray-900">أحمد خالد</h4>
                                    <p class="text-gray-600">بنى عضلات قوية في 4 أشهر</p>
                                </div>
                            </div>
                            <p class="text-gray-700">"البرنامج التدريبي كان تحدياً حقيقياً، لكن النتائج تستحق كل المجهود. زاد وزني العضلي 8 كيلو!"</p>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <h2 class="text-3xl font-bold text-gray-900 mb-6">ابدأ رحلة التحول اليوم</h2>
                    <p class="text-xl text-gray-600 mb-8">انضم لآلاف الأشخاص الذين حققوا أهدافهم معنا</p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="/register" class="bg-red-600 hover:bg-red-700 text-white font-bold py-4 px-8 rounded-lg text-lg transition-colors">
                            احجز استشارة مجانية
                        </a>
                        <a href="/meal-plans-public" class="bg-white border-2 border-red-600 text-red-600 hover:bg-red-50 font-bold py-4 px-8 rounded-lg text-lg transition-colors">
                            تصفح البرامج الغذائية
                        </a>
                    </div>
                </div>
            ',
            'meta_title' => 'أكاديمية اللياقة البدنية - حوّل جسمك، غيّر حياتك',
            'meta_description' => 'أكاديمية متخصصة في التدريب الشخصي وبناء الأجسام مع برامج غذائية متكاملة. ابدأ رحلة التحول اليوم!',
            'is_active' => true,
            'user_id' => 1,
        ]);
    }

    private function createPages()
    {
        $coach = User::first();
        
        $pages = [
            [
                'title' => 'من نحن',
                'slug' => 'about-us',
                'content' => '
                    <h1>مرحباً بك في أكاديمية اللياقة البدنية</h1>
                    
                    <p>نحن أكثر من مجرد صالة رياضية - نحن شريكك في رحلة التحول الكاملة نحو حياة صحية ونشطة. تأسست أكاديميتنا على يد نخبة من المدربين المعتمدين دولياً والمتخصصين في علوم الرياضة والتغذية.</p>
                    
                    <h2>رؤيتنا</h2>
                    <p>أن نكون الوجهة الأولى في المنطقة لكل من يسعى لتحقيق أهدافه في اللياقة البدنية والصحة العامة، من خلال تقديم برامج علمية متطورة ومتابعة شخصية متميزة.</p>
                    
                    <h2>مهمتنا</h2>
                    <p>نساعد عملاءنا في تحقيق أهدافهم من خلال:</p>
                    <ul>
                        <li>برامج تدريبية مخصصة ومدروسة علمياً</li>
                        <li>خطط غذائية متوازنة تناسب نمط الحياة</li>
                        <li>متابعة شخصية من مدربين معتمدين</li>
                        <li>بيئة تدريبية محفزة وداعمة</li>
                        <li>أحدث الأجهزة والتقنيات الرياضية</li>
                    </ul>
                    
                    <h2>فريق العمل</h2>
                    <p>يضم فريقنا نخبة من المدربين المعتمدين من الاتحاد الدولي للياقة البدنية (ACSM) وأخصائيي التغذية المرخصين. كل مدرب لديه خبرة تزيد عن 5 سنوات في مجال التدريب الشخصي وتحقيق النتائج.</p>
                    
                    <h2>إنجازاتنا</h2>
                    <ul>
                        <li>أكثر من 2000 عميل حقق أهدافه معنا</li>
                        <li>معدل نجاح 95% في تحقيق الأهداف المحددة</li>
                        <li>شراكات مع أفضل خبراء التغذية في المملكة</li>
                        <li>برامج معتمدة من الجمعية السعودية للطب الرياضي</li>
                    </ul>
                ',
                'excerpt' => 'تعرف على أكاديمية اللياقة البدنية، رؤيتنا، مهمتنا، وفريق العمل المتخصص',
                'meta_title' => 'من نحن - أكاديمية اللياقة البدنية',
                'meta_description' => 'تعرف على أكاديمية اللياقة البدنية، فريق المدربين المعتمدين، وإنجازاتنا في مجال اللياقة البدنية والتغذية',
                'access_level' => 'public',
                'is_published' => true,
                'show_in_menu' => true,
                'menu_order' => 1,
                'published_at' => now(),
                'user_id' => $coach->id,
            ],
            [
                'title' => 'خدماتنا',
                'slug' => 'our-services',
                'content' => '
                    <h1>خدماتنا المتميزة</h1>
                    
                    <p>نقدم مجموعة شاملة من الخدمات المتخصصة في اللياقة البدنية والتغذية لمساعدتك في تحقيق أهدافك بأفضل الطرق العلمية.</p>
                    
                    <h2>🏋️ التدريب الشخصي</h2>
                    <p>جلسات تدريب فردية مع مدربين معتمدين، مصممة خصيصاً لأهدافك ومستوى لياقتك البدنية.</p>
                    <ul>
                        <li>تقييم شامل للياقة البدنية</li>
                        <li>برنامج تدريبي مخصص</li>
                        <li>متابعة التقدم والتطوير</li>
                        <li>تعديل البرنامج حسب النتائج</li>
                    </ul>
                    
                    <h2>🥗 الاستشارات الغذائية</h2>
                    <p>خطط غذائية متوازنة من أخصائيي تغذية مرخصين، تناسب أهدافك ونمط حياتك.</p>
                    <ul>
                        <li>تحليل النظام الغذائي الحالي</li>
                        <li>خطة غذائية مخصصة</li>
                        <li>وصفات صحية ولذيذة</li>
                        <li>متابعة دورية وتعديل الخطة</li>
                    </ul>
                    
                    <h2>📊 تحليل تركيب الجسم</h2>
                    <p>قياسات دقيقة لتركيب جسمك باستخدام أحدث الأجهزة لمتابعة تقدمك بدقة.</p>
                    <ul>
                        <li>قياس نسبة الدهون والعضلات</li>
                        <li>تحليل معدل الأيض الأساسي</li>
                        <li>متابعة التغييرات الشهرية</li>
                        <li>تقارير مفصلة عن التقدم</li>
                    </ul>
                    
                    <h2>🏃 برامج اللياقة الجماعية</h2>
                    <p>حصص جماعية متنوعة ومحفزة تناسب جميع المستويات والأعمار.</p>
                    <ul>
                        <li>كروس فيت</li>
                        <li>يوغا وبيلاتس</li>
                        <li>تمارين القلب والأوعية</li>
                        <li>تمارين القوة الوظيفية</li>
                    </ul>
                    
                    <h2>💪 برامج إعادة التأهيل</h2>
                    <p>برامج متخصصة لإعادة التأهيل بعد الإصابات أو للأشخاص ذوي الاحتياجات الخاصة.</p>
                    <ul>
                        <li>تقييم طبي شامل</li>
                        <li>برامج تأهيل تدريجية</li>
                        <li>تمارين علاجية متخصصة</li>
                        <li>متابعة طبية دورية</li>
                    </ul>
                ',
                'excerpt' => 'اكتشف خدماتنا المتميزة في التدريب الشخصي، الاستشارات الغذائية، وبرامج اللياقة المتخصصة',
                'access_level' => 'public',
                'is_published' => true,
                'show_in_menu' => true,
                'menu_order' => 2,
                'published_at' => now(),
                'user_id' => $coach->id,
            ],
            [
                'title' => 'برامج التدريب المتقدمة',
                'slug' => 'advanced-training-programs',
                'content' => '
                    <h1>برامج التدريب المتقدمة</h1>
                    
                    <p>برامج تدريبية متخصصة للرياضيين المتقدمين والراغبين في الوصول لأعلى مستويات الأداء.</p>
                    
                    <h2>🏆 برنامج بناء الأجسام المتقدم</h2>
                    <p>برنامج شامل لبناء كتلة عضلية قوية وتحسين التعريف العضلي.</p>
                    <ul>
                        <li>تدريب 6 أيام في الأسبوع</li>
                        <li>تقسيم عضلي متقدم</li>
                        <li>تقنيات تدريب متطورة</li>
                        <li>مكملات غذائية موصى بها</li>
                    </ul>
                    
                    <h2>⚡ برنامج القوة الوظيفية</h2>
                    <p>تطوير القوة والقدرة على التحمل للأنشطة اليومية والرياضية.</p>
                    <ul>
                        <li>تمارين متعددة المفاصل</li>
                        <li>تدريب الثبات والتوازن</li>
                        <li>تطوير القوة الانفجارية</li>
                        <li>تحسين المرونة والحركة</li>
                    </ul>
                    
                    <h2>🔥 برنامج حرق الدهون المكثف</h2>
                    <p>برنامج متقدم لحرق الدهون والحصول على جسم مشدود ومتناسق.</p>
                    <ul>
                        <li>تدريب عالي الكثافة (HIIT)</li>
                        <li>تمارين القلب المتنوعة</li>
                        <li>تدريب الدوائر</li>
                        <li>نظام غذائي منخفض الكربوهيدرات</li>
                    </ul>
                ',
                'excerpt' => 'برامج تدريبية متقدمة للرياضيين وعشاق اللياقة البدنية الراغبين في تحقيق أهداف متقدمة',
                'access_level' => 'membership',
                'required_membership_types' => json_encode([2, 3]), // الذهبية والبلاتينية
                'is_published' => true,
                'show_in_menu' => true,
                'menu_order' => 3,
                'published_at' => now(),
                'user_id' => $coach->id,
            ],
            [
                'title' => 'اتصل بنا',
                'slug' => 'contact-us',
                'content' => '
                    <h1>تواصل معنا</h1>
                    
                    <p>نحن هنا لمساعدتك في بداية رحلتك نحو حياة صحية أفضل. تواصل معنا للحصول على استشارة مجانية.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 my-8">
                        <div>
                            <h2>معلومات الاتصال</h2>
                            <div class="space-y-4">
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                    <div>
                                        <p class="font-semibold">الهاتف</p>
                                        <p>+966501234567</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    <div>
                                        <p class="font-semibold">البريد الإلكتروني</p>
                                        <p>info@fitnessacademy.sa</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <div>
                                        <p class="font-semibold">العنوان</p>
                                        <p>الرياض، حي الملقا، شارع الأمير سلطان</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h2>ساعات العمل</h2>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span>السبت - الخميس:</span>
                                    <span>6:00 ص - 11:00 م</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>الجمعة:</span>
                                    <span>2:00 م - 11:00 م</span>
                                </div>
                            </div>
                            
                            <h3 class="mt-6">تابعنا على وسائل التواصل</h3>
                            <div class="flex space-x-4 mt-4">
                                <a href="#" class="text-red-600 hover:text-red-800">إنستغرام</a>
                                <a href="#" class="text-red-600 hover:text-red-800">يوتيوب</a>
                                <a href="#" class="text-red-600 hover:text-red-800">تويتر</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-red-50 p-6 rounded-lg mt-8">
                        <h2>احجز استشارة مجانية</h2>
                        <p>احصل على استشارة مجانية مع أحد مدربينا المعتمدين لوضع خطة مخصصة لأهدافك.</p>
                        <a href="/register" class="inline-block mt-4 bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition-colors">
                            احجز الآن
                        </a>
                    </div>
                ',
                'excerpt' => 'تواصل مع أكاديمية اللياقة البدنية واحجز استشارة مجانية مع مدربينا المعتمدين',
                'access_level' => 'public',
                'is_published' => true,
                'show_in_menu' => true,
                'menu_order' => 4,
                'published_at' => now(),
                'user_id' => $coach->id,
            ]
        ];

        foreach ($pages as $pageData) {
            Page::create($pageData);
        }
    }

    private function createMealPlans()
    {
        $coach = User::first();
        $user1 = User::find(2);
        
        $mealPlans = [
            [
                'name' => 'سلطة الكينوا بالدجاج المشوي',
                'description' => 'وجبة غداء صحية ومتوازنة غنية بالبروتين والألياف، مثالية لبناء العضلات وخسارة الوزن',
                'meal_type' => 'lunch',
                'calories' => 420,
                'protein' => 35,
                'carbs' => 28,
                'fats' => 18,
                'ingredients' => "1 كوب كينوا مطبوخة\n200 جرام صدر دجاج مشوي\n2 كوب خضار ورقية مشكلة\n1/2 كوب طماطم كرزية\n1/4 كوب خيار مقطع\n2 ملعقة كبيرة زيت زيتون\n1 ملعقة كبيرة عصير ليمون\nملح وفلفل أسود حسب الذوق\n1/4 كوب جوز مفروم",
                'instructions' => "1. اطبخي الكينوا حسب التعليمات على العبوة واتركيها تبرد\n2. تبلي صدر الدجاج بالملح والفلفل واشويه حتى ينضج تماماً\n3. اقطعي الدجاج إلى قطع صغيرة\n4. في وعاء كبير، اخلطي الخضار الورقية مع الطماطم والخيار\n5. أضيفي الكينوا والدجاج المقطع\n6. اخلطي زيت الزيتون مع عصير الليمون والملح والفلفل\n7. أضيفي الصلصة للسلطة واخلطي جيداً\n8. زيني بالجوز المفروم وقدمي فوراً",
                'prep_time' => 15,
                'cook_time' => 20,
                'servings' => 2,
                'difficulty' => 'easy',
                'is_active' => true,
                'user_id' => $coach->id,
            ],
            [
                'name' => 'عجة البيض بالخضار والجبن',
                'description' => 'إفطار صحي وسريع التحضير، غني بالبروتين والفيتامينات، مثالي لبداية يوم نشط',
                'meal_type' => 'breakfast',
                'calories' => 320,
                'protein' => 22,
                'carbs' => 8,
                'fats' => 22,
                'ingredients' => "3 بيضات كبيرة\n1/4 كوب حليب قليل الدسم\n1/2 كوب فلفل ألوان مقطع\n1/4 كوب بصل مفروم\n1/4 كوب جبن شيدر مبشور\n1 ملعقة كبيرة زيت زيتون\nحفنة سبانخ طازجة\nملح وفلفل أسود\nرشة أعشاب مجففة",
                'instructions' => "1. اخفقي البيض مع الحليب والملح والفلفل في وعاء\n2. سخني زيت الزيتون في مقلاة غير لاصقة\n3. أضيفي البصل والفلفل وحركي لمدة 3 دقائق\n4. أضيفي السبانخ حتى تذبل\n5. اسكبي خليط البيض في المقلاة\n6. اتركي العجة تنضج من الأسفل لمدة 3-4 دقائق\n7. أضيفي الجبن على نصف العجة\n8. اطوي العجة إلى النصف وقدميها ساخنة",
                'prep_time' => 10,
                'cook_time' => 8,
                'servings' => 1,
                'difficulty' => 'easy',
                'is_active' => true,
                'user_id' => $coach->id,
            ],
            [
                'name' => 'سمك السلمون المشوي مع الأرز البني',
                'description' => 'وجبة عشاء فاخرة وصحية، غنية بأحماض أوميغا 3 والبروتين عالي الجودة',
                'meal_type' => 'dinner',
                'calories' => 480,
                'protein' => 38,
                'carbs' => 35,
                'fats' => 20,
                'ingredients' => "200 جرام فيليه سلمون\n3/4 كوب أرز بني مطبوخ\n1 كوب بروكلي\n1/2 كوب جزر مقطع\n2 ملعقة كبيرة زيت زيتون\n1 ملعقة صغيرة عصير ليمون\n1 فص ثوم مفروم\nملح وفلفل أسود\nأعشاب طازجة (شبت أو بقدونس)",
                'instructions' => "1. سخني الفرن على 200 درجة مئوية\n2. تبلي السلمون بالملح والفلفل وزيت الزيتون\n3. ضعي السلمون في صينية الفرن واتركيه لمدة 15-18 دقيقة\n4. في هذه الأثناء، اسلقي البروكلي والجزر حتى ينضجا\n5. اطبخي الأرز البني حسب التعليمات\n6. اخلطي عصير الليمون مع الثوم المفروم\n7. قدمي السلمون مع الأرز والخضار\n8. اسكبي خليط الليمون والثوم على السلمون\n9. زيني بالأعشاب الطازجة",
                'prep_time' => 10,
                'cook_time' => 25,
                'servings' => 1,
                'difficulty' => 'medium',
                'is_active' => true,
                'user_id' => $coach->id,
            ],
            [
                'name' => 'سموثي البروتين بالتوت',
                'description' => 'مشروب منعش وصحي مثالي بعد التمرين، غني بالبروتين ومضادات الأكسدة',
                'meal_type' => 'snack',
                'calories' => 280,
                'protein' => 25,
                'carbs' => 30,
                'fats' => 8,
                'ingredients' => "1 سكوب بروتين فانيليا\n1 كوب حليب لوز غير محلى\n1/2 كوب توت مجمد\n1/2 موزة\n1 ملعقة كبيرة زبدة لوز\n1 ملعقة صغيرة عسل\nرشة قرفة\n1/2 كوب ثلج",
                'instructions' => "1. ضعي جميع المكونات في الخلاط\n2. اخلطي على سرعة عالية لمدة 60-90 ثانية\n3. تأكدي من أن الخليط أصبح ناعماً ومتجانساً\n4. أضيفي المزيد من الحليب إذا كان الخليط كثيفاً جداً\n5. اسكبي في كوب التقديم\n6. زيني بحبات التوت الطازجة\n7. قدمي فوراً للحصول على أفضل طعم",
                'prep_time' => 5,
                'cook_time' => 0,
                'servings' => 1,
                'difficulty' => 'easy',
                'is_active' => true,
                'user_id' => $coach->id,
            ],
            [
                'name' => 'دجاج تكا مسالا صحي',
                'description' => 'نسخة صحية من الطبق الهندي الشهير، قليلة الدهون وغنية بالنكهات والبروتين',
                'meal_type' => 'dinner',
                'calories' => 380,
                'protein' => 42,
                'carbs' => 15,
                'fats' => 16,
                'ingredients' => "300 جرام صدر دجاج مقطع مكعبات\n1/2 كوب زبادي يوناني\n1 ملعقة كبيرة معجون طماطم\n1 بصلة متوسطة مفرومة\n2 فص ثوم مفروم\n1 ملعقة صغيرة زنجبيل مبشور\n1 ملعقة صغيرة كمون\n1 ملعقة صغيرة كركم\n1/2 ملعقة صغيرة فلفل حار\n1 ملعقة كبيرة زيت جوز الهند\nملح حسب الذوق\nكزبرة طازجة للتزيين",
                'instructions' => "1. تبلي قطع الدجاج بنصف كمية الزبادي والملح واتركيها 30 دقيقة\n2. سخني زيت جوز الهند في مقلاة عميقة\n3. أضيفي البصل وحركي حتى يذبل\n4. أضيفي الثوم والزنجبيل وحركي لدقيقة\n5. أضيفي التوابل وحركي لمدة 30 ثانية\n6. أضيفي معجون الطماطم وحركي لدقيقتين\n7. أضيفي قطع الدجاج وحركي حتى تنضج\n8. أضيفي باقي الزبادي واتركي الخليط ينضج 10 دقائق\n9. زيني بالكزبرة الطازجة وقدمي مع الأرز البني",
                'prep_time' => 40,
                'cook_time' => 25,
                'servings' => 2,
                'difficulty' => 'medium',
                'is_active' => true,
                'user_id' => $user1->id,
            ],
            [
                'name' => 'كرات الطاقة بالتمر والمكسرات',
                'description' => 'وجبة خفيفة طبيعية ومغذية، مثالية قبل أو بعد التمرين، خالية من السكر المضاف',
                'meal_type' => 'snack',
                'calories' => 150,
                'protein' => 4,
                'carbs' => 18,
                'fats' => 8,
                'ingredients' => "1 كوب تمر منزوع النوى\n1/2 كوب لوز نيء\n1/4 كوب جوز\n2 ملعقة كبيرة بذور شيا\n1 ملعقة كبيرة زبدة لوز\n1 ملعقة صغيرة فانيليا\nرشة ملح\nجوز هند مبشور للتغليف",
                'instructions' => "1. انقعي التمر في ماء دافئ لمدة 10 دقائق ثم اتركيه يجف\n2. في محضر الطعام، اطحني اللوز والجوز حتى يصبح مفروماً ناعماً\n3. أضيفي التمر واخلطي حتى يتكون عجين متماسك\n4. أضيفي بذور الشيا وزبدة اللوز والفانيليا والملح\n5. اخلطي حتى تتجانس المكونات\n6. شكلي الخليط إلى كرات صغيرة بحجم الجوز\n7. لفي الكرات في جوز الهند المبشور\n8. ضعيها في الثلاجة لمدة 30 دقيقة قبل التقديم",
                'prep_time' => 20,
                'cook_time' => 0,
                'servings' => 12,
                'difficulty' => 'easy',
                'is_active' => true,
                'user_id' => $coach->id,
            ]
        ];

        foreach ($mealPlans as $mealData) {
            MealPlan::create($mealData);
        }
    }

    private function createWorkouts()
    {
        $coach = User::first();
        
        $workouts = [
            [
                'name' => 'تمرين الجزء العلوي - مبتدئين',
                'description' => 'تمرين شامل للجزء العلوي من الجسم يستهدف عضلات الصدر والظهر والأكتاف والذراعين. مناسب للمبتدئين مع التركيز على الشكل الصحيح للحركات.',
                'duration' => 45,
                'difficulty' => 'easy',
                'video_url' => 'https://youtube.com/watch?v=example1',
                'status' => true,
                'user_id' => $coach->id,
            ],
            [
                'name' => 'تمرين الساقين والمؤخرة - متوسط',
                'description' => 'تمرين مكثف للجزء السفلي يركز على بناء قوة الساقين والمؤخرة. يتضمن تمارين السكوات والرفعة الميتة وتمارين الاندفاع.',
                'duration' => 60,
                'difficulty' => 'medium',
                'video_url' => 'https://youtube.com/watch?v=example2',
                'status' => true,
                'user_id' => $coach->id,
            ],
            [
                'name' => 'تمرين الكارديو عالي الكثافة (HIIT)',
                'description' => 'تمرين قلبي عالي الكثافة لحرق الدهون وتحسين اللياقة القلبية. يتضمن فترات من التمرين المكثف والراحة النشطة.',
                'duration' => 30,
                'difficulty' => 'hard',
                'video_url' => 'https://youtube.com/watch?v=example3',
                'status' => true,
                'user_id' => $coach->id,
            ],
            [
                'name' => 'تمرين الجسم الكامل للمبتدئين',
                'description' => 'تمرين شامل يستهدف جميع عضلات الجسم في جلسة واحدة. مثالي للمبتدئين الذين يريدون البدء بروتين تدريبي متوازن.',
                'duration' => 40,
                'difficulty' => 'easy',
                'video_url' => 'https://youtube.com/watch?v=example4',
                'status' => true,
                'user_id' => $coach->id,
            ],
            [
                'name' => 'تمرين البطن والجذع',
                'description' => 'تمرين متخصص لتقوية عضلات البطن والجذع وتحسين الثبات. يتضمن تمارين متنوعة لجميع عضلات المنطقة الوسطى.',
                'duration' => 25,
                'difficulty' => 'medium',
                'video_url' => 'https://youtube.com/watch?v=example5',
                'status' => true,
                'user_id' => $coach->id,
            ],
            [
                'name' => 'تمرين القوة المتقدم',
                'description' => 'تمرين متقدم للرياضيين ذوي الخبرة، يركز على رفع الأوزان الثقيلة وتطوير القوة القصوى. يتطلب خبرة سابقة في التدريب.',
                'duration' => 75,
                'difficulty' => 'hard',
                'video_url' => 'https://youtube.com/watch?v=example6',
                'status' => true,
                'user_id' => $coach->id,
            ]
        ];

        foreach ($workouts as $workoutData) {
            Workout::create($workoutData);
        }
    }

    private function createWorkoutSchedules()
    {
        $workouts = Workout::all();
        $coach = User::first();
        
        // إنشاء جدولة لأول 4 أسابيع
        for ($week = 1; $week <= 4; $week++) {
            for ($session = 1; $session <= 6; $session++) {
                // تنويع التمارين حسب الأسبوع والجلسة
                $workoutIndex = ($week - 1) * 6 + $session - 1;
                $workout = $workouts[$workoutIndex % $workouts->count()];
                
                $notes = $this->getWorkoutNotes($week, $session, $workout->name);
                
                WorkoutSchedule::create([
                    'workout_id' => $workout->id,
                    'week_number' => $week,
                    'session_number' => $session,
                    'notes' => $notes,
                    'status' => true,
                    'user_id' => $coach->id,
                ]);
            }
        }
    }

    private function getWorkoutNotes($week, $session, $workoutName)
    {
        $notes = [
            "الأسبوع {$week} - الجلسة {$session}: ركز على الشكل الصحيح للحركات",
            "تذكر الإحماء لمدة 10 دقائق قبل البدء",
            "اشرب الماء بانتظام خلال التمرين",
            "لا تنس تمارين التمدد بعد انتهاء التمرين",
            "استمع لجسمك وتوقف إذا شعرت بألم غير طبيعي",
            "زد الأوزان تدريجياً مع تحسن مستواك",
        ];
        
        return $notes[array_rand($notes)];
    }

    private function createArticles()
    {
        $coach = User::first();
        
        $articles = [
            [
                'title' => 'دليل المبتدئين الشامل للياقة البدنية',
                'content' => '
                    <h2>مقدمة</h2>
                    <p>إذا كنت تفكر في بدء رحلتك في عالم اللياقة البدنية، فأنت في المكان الصحيح. هذا الدليل الشامل سيأخذك خطوة بخطوة من البداية حتى تصبح قادراً على وضع برنامج تدريبي فعال.</p>
                    
                    <h2>الخطوة الأولى: تحديد الأهداف</h2>
                    <p>قبل البدء في أي برنامج تدريبي، من المهم جداً تحديد أهدافك بوضوح:</p>
                    <ul>
                        <li><strong>خسارة الوزن:</strong> إذا كان هدفك خسارة الوزن، ستحتاج للتركيز على تمارين الكارديو والنظام الغذائي</li>
                        <li><strong>بناء العضلات:</strong> يتطلب التركيز على تمارين المقاومة والبروتين</li>
                        <li><strong>تحسين اللياقة العامة:</strong> مزيج متوازن من جميع أنواع التمارين</li>
                    </ul>
                    
                    <h2>أساسيات التغذية للمبتدئين</h2>
                    <p>التغذية تشكل 70% من نجاحك في تحقيق أهدافك:</p>
                    <ul>
                        <li>احسب احتياجك اليومي من السعرات الحرارية</li>
                        <li>تناول البروتين في كل وجبة (1.6-2.2 جرام لكل كيلو من وزن الجسم)</li>
                        <li>لا تهمل الكربوهيدرات الصحية والدهون المفيدة</li>
                        <li>اشرب 2-3 لتر من الماء يومياً</li>
                    </ul>
                    
                    <h2>برنامج تدريبي للأسبوع الأول</h2>
                    <p>ابدأ بـ 3 أيام تدريب في الأسبوع:</p>
                    <ul>
                        <li><strong>اليوم الأول:</strong> تمرين الجسم الكامل (30 دقيقة)</li>
                        <li><strong>اليوم الثاني:</strong> راحة أو مشي خفيف</li>
                        <li><strong>اليوم الثالث:</strong> تمرين كارديو (20 دقيقة)</li>
                        <li><strong>اليوم الرابع:</strong> راحة</li>
                        <li><strong>اليوم الخامس:</strong> تمرين الجسم الكامل (30 دقيقة)</li>
                    </ul>
                    
                    <h2>نصائح مهمة للمبتدئين</h2>
                    <ol>
                        <li>ابدأ تدريجياً ولا تتعجل النتائج</li>
                        <li>الثبات أهم من الكثافة في البداية</li>
                        <li>تعلم الشكل الصحيح للتمارين قبل زيادة الأوزان</li>
                        <li>احصل على قسط كافٍ من النوم (7-9 ساعات)</li>
                        <li>لا تتردد في طلب المساعدة من المدربين</li>
                    </ol>
                ',
                'user_id' => $coach->id,
            ],
            [
                'title' => 'أفضل 10 أطعمة لبناء العضلات',
                'content' => '
                    <h2>مقدمة</h2>
                    <p>بناء العضلات لا يتطلب فقط التمرين الصحيح، بل أيضاً التغذية المناسبة. إليك أفضل 10 أطعمة ستساعدك في بناء كتلة عضلية قوية وصحية.</p>
                    
                    <h2>1. صدر الدجاج</h2>
                    <p>يحتوي على 31 جرام بروتين لكل 100 جرام، وهو مصدر ممتاز للبروتين عالي الجودة مع قليل من الدهون.</p>
                    
                    <h2>2. البيض</h2>
                    <p>البيضة الواحدة تحتوي على 6 جرام بروتين كامل مع جميع الأحماض الأمينية الأساسية. تناول 2-3 بيضات يومياً.</p>
                    
                    <h2>3. السلمون</h2>
                    <p>غني بالبروتين وأحماض أوميغا 3 المفيدة للتعافي العضلي وتقليل الالتهابات.</p>
                    
                    <h2>4. الكينوا</h2>
                    <p>مصدر نباتي ممتاز للبروتين الكامل، يحتوي على 8 جرام بروتين لكل كوب مطبوخ.</p>
                    
                    <h2>5. اللبن اليوناني</h2>
                    <p>يحتوي على 20 جرام بروتين لكل كوب، بالإضافة للبروبيوتيك المفيد للهضم.</p>
                    
                    <h2>6. اللوز</h2>
                    <p>مصدر ممتاز للبروتين والدهون الصحية وفيتامين E. تناول حفنة يومياً (23 حبة).</p>
                    
                    <h2>7. الشوفان</h2>
                    <p>كربوهيدرات معقدة توفر طاقة مستدامة للتمارين، مع 6 جرام بروتين لكل كوب.</p>
                    
                    <h2>8. الفاصوليا السوداء</h2>
                    <p>مصدر نباتي غني بالبروتين والألياف، يحتوي على 15 جرام بروتين لكل كوب.</p>
                    
                    <h2>9. التونة</h2>
                    <p>سمك قليل الدهون وغني بالبروتين، سهل التحضير ومتوفر. يحتوي على 25 جرام بروتين لكل 100 جرام.</p>
                    
                    <h2>10. البطاطا الحلوة</h2>
                    <p>مصدر ممتاز للكربوهيدرات المعقدة وفيتامين A، توفر طاقة مستدامة للتمارين الشاقة.</p>
                    
                    <h2>نصائح للاستفادة القصوى</h2>
                    <ul>
                        <li>وزع البروتين على جميع وجباتك اليومية</li>
                        <li>تناول وجبة غنية بالبروتين خلال 30 دقيقة بعد التمرين</li>
                        <li>لا تنس شرب الماء بكثرة</li>
                        <li>نوع في مصادر البروتين لضمان الحصول على جميع الأحماض الأمينية</li>
                    </ul>
                ',
                'user_id' => $coach->id,
            ],
            [
                'title' => 'كيفية تجنب الإصابات الرياضية',
                'content' => '
                    <h2>أهمية الوقاية من الإصابات</h2>
                    <p>الإصابات الرياضية يمكن أن تعيق تقدمك لأسابيع أو حتى أشهر. الوقاية دائماً أفضل من العلاج، وهنا أهم النصائح لتجنب الإصابات.</p>
                    
                    <h2>1. الإحماء الصحيح</h2>
                    <p>لا تبدأ التمرين أبداً بدون إحماء مناسب:</p>
                    <ul>
                        <li>5-10 دقائق من الحركة الخفيفة (مشي أو جري خفيف)</li>
                        <li>تمارين حركية للمفاصل</li>
                        <li>تمارين تحضيرية للعضلات المستهدفة</li>
                    </ul>
                    
                    <h2>2. تعلم الشكل الصحيح</h2>
                    <p>الشكل الصحيح أهم من الوزن المرفوع:</p>
                    <ul>
                        <li>ابدأ بأوزان خفيفة وتعلم الحركة</li>
                        <li>استخدم المرآة لمراقبة شكلك</li>
                        <li>اطلب المساعدة من مدرب مؤهل</li>
                        <li>لا تضحي بالشكل من أجل رفع وزن أثقل</li>
                    </ul>
                    
                    <h2>3. التدرج في الشدة</h2>
                    <p>زيادة شدة التمرين يجب أن تكون تدريجية:</p>
                    <ul>
                        <li>زد الوزن بنسبة 5-10% أسبوعياً فقط</li>
                        <li>استمع لجسمك ولا تتجاهل الألم</li>
                        <li>خذ أيام راحة كافية بين التمارين</li>
                    </ul>
                    
                    <h2>4. أهمية التبريد والتمدد</h2>
                    <p>لا تنس هذه الخطوة المهمة:</p>
                    <ul>
                        <li>5-10 دقائق من التبريد التدريجي</li>
                        <li>تمارين تمدد للعضلات المستخدمة</li>
                        <li>تمارين تنفس عميق للاسترخاء</li>
                    </ul>
                    
                    <h2>5. التغذية والترطيب</h2>
                    <ul>
                        <li>تناول وجبة متوازنة قبل التمرين بـ 2-3 ساعات</li>
                        <li>اشرب الماء قبل وأثناء وبعد التمرين</li>
                        <li>تناول البروتين والكربوهيدرات بعد التمرين</li>
                    </ul>
                    
                    <h2>علامات التحذير</h2>
                    <p>توقف فوراً إذا شعرت بـ:</p>
                    <ul>
                        <li>ألم حاد أو مفاجئ</li>
                        <li>دوخة أو غثيان</li>
                        <li>ضيق في التنفس غير طبيعي</li>
                        <li>ألم في الصدر</li>
                    </ul>
                ',
                'user_id' => $coach->id,
            ],
            [
                'title' => 'أهمية النوم في بناء العضلات',
                'content' => '
                    <h2>لماذا النوم مهم للرياضيين؟</h2>
                    <p>النوم ليس مجرد راحة للجسم، بل هو الوقت الذي يقوم فيه جسمك بإصلاح وبناء العضلات. خلال النوم العميق، يفرز الجسم هرمون النمو الذي يساعد في تعافي العضلات ونموها.</p>
                    
                    <h2>كم ساعة نوم تحتاج؟</h2>
                    <p>الرياضيون يحتاجون لنوم أكثر من الأشخاص العاديين:</p>
                    <ul>
                        <li><strong>البالغون العاديون:</strong> 7-9 ساعات</li>
                        <li><strong>الرياضيون:</strong> 8-10 ساعات</li>
                        <li><strong>الرياضيون المحترفون:</strong> 9-11 ساعة</li>
                    </ul>
                    
                    <h2>مراحل النوم وتأثيرها</h2>
                    <h3>النوم العميق (Deep Sleep)</h3>
                    <p>هذه المرحلة الأهم لبناء العضلات:</p>
                    <ul>
                        <li>إفراز هرمون النمو</li>
                        <li>إصلاح الأنسجة العضلية</li>
                        <li>تقوية جهاز المناعة</li>
                    </ul>
                    
                    <h3>نوم الأحلام (REM Sleep)</h3>
                    <p>مهم للتعافي النفسي والذهني:</p>
                    <ul>
                        <li>معالجة المعلومات والذكريات</li>
                        <li>تحسين التركيز والأداء</li>
                        <li>تنظيم المزاج والدافعية</li>
                    </ul>
                    
                    <h2>نصائح لتحسين جودة النوم</h2>
                    <ol>
                        <li><strong>حافظ على روتين ثابت:</strong> نم واستيقظ في نفس الوقت يومياً</li>
                        <li><strong>اجعل غرفة النوم مظلمة وباردة:</strong> درجة حرارة 18-20 مئوية مثالية</li>
                        <li><strong>تجنب الشاشات قبل النوم:</strong> توقف عن استخدام الهاتف قبل ساعة من النوم</li>
                        <li><strong>لا تتمرن قبل النوم مباشرة:</strong> اترك 3-4 ساعات بين التمرين والنوم</li>
                        <li><strong>تجنب الكافيين بعد الظهر:</strong> آخر كوب قهوة يجب أن يكون قبل 2 ظهراً</li>
                    </ol>
                    
                    <h2>تأثير قلة النوم على الأداء</h2>
                    <p>قلة النوم تؤثر سلباً على:</p>
                    <ul>
                        <li>انخفاض القوة والتحمل</li>
                        <li>بطء التعافي العضلي</li>
                        <li>زيادة خطر الإصابات</li>
                        <li>ضعف التركيز والدافعية</li>
                        <li>اضطراب الهرمونات</li>
                    </ul>
                ',
                'user_id' => $coach->id,
            ]
        ];

        foreach ($articles as $articleData) {
            Article::create($articleData);
        }
    }

    private function createFaqs()
    {
        $coach = User::first();
        
        $faqs = [
            [
                'question' => 'كم مرة يجب أن أتمرن في الأسبوع؟',
                'answer' => 'للمبتدئين، ننصح بـ 3-4 مرات في الأسبوع مع يوم راحة بين كل تمرين. للمتقدمين، يمكن التمرن 5-6 مرات أسبوعياً مع تنويع المجموعات العضلية.',
                'category' => 'التدريب',
                'is_active' => true,
                'sort_order' => 1,
                'user_id' => $coach->id,
            ],
            [
                'question' => 'ما هو أفضل وقت للتمرين؟',
                'answer' => 'أفضل وقت للتمرين هو الوقت الذي يناسب جدولك ويمكنك الالتزام به. صباحاً يساعد في زيادة الطاقة طوال اليوم، ومساءً قد يكون لديك طاقة أكثر. المهم هو الثبات على التوقيت.',
                'category' => 'التدريب',
                'is_active' => true,
                'sort_order' => 2,
                'user_id' => $coach->id,
            ],
            [
                'question' => 'هل يجب تناول البروتين بعد التمرين مباشرة؟',
                'answer' => 'نعم، ننصح بتناول البروتين خلال 30-60 دقيقة بعد التمرين لتحفيز بناء العضلات والتعافي. يمكن أن يكون مشروب بروتين أو وجبة متوازنة تحتوي على 20-30 جرام بروتين.',
                'category' => 'التغذية',
                'is_active' => true,
                'sort_order' => 3,
                'user_id' => $coach->id,
            ],
            [
                'question' => 'كم لتر ماء يجب أن أشرب يومياً؟',
                'answer' => 'الشخص العادي يحتاج 2-3 لتر يومياً، والرياضيون يحتاجون أكثر. اشرب 500 مل إضافية لكل ساعة تمرين. راقب لون البول - يجب أن يكون أصفر فاتح.',
                'category' => 'التغذية',
                'is_active' => true,
                'sort_order' => 4,
                'user_id' => $coach->id,
            ],
            [
                'question' => 'متى سأرى النتائج؟',
                'answer' => 'النتائج تختلف من شخص لآخر، لكن عموماً: تحسن الطاقة والمزاج خلال أسبوع، تحسن القوة خلال 2-4 أسابيع، تغييرات مرئية في الجسم خلال 4-8 أسابيع مع الالتزام بالبرنامج والتغذية.',
                'category' => 'عام',
                'is_active' => true,
                'sort_order' => 5,
                'user_id' => $coach->id,
            ],
            [
                'question' => 'هل يمكنني التمرن أثناء المرض؟',
                'answer' => 'إذا كانت الأعراض فوق الرقبة فقط (رشح خفيف)، يمكن تمرين خفيف. إذا كانت الأعراض تحت الرقبة (حمى، ألم عضلات، سعال)، يجب الراحة التامة حتى الشفاء الكامل.',
                'category' => 'الصحة',
                'is_active' => true,
                'sort_order' => 6,
                'user_id' => $coach->id,
            ],
            [
                'question' => 'ما الفرق بين العضوية الذهبية والبلاتينية؟',
                'answer' => 'العضوية الذهبية تشمل البرامج التدريبية والغذائية مع متابعة أسبوعية. العضوية البلاتينية تضيف التدريب الشخصي المباشر (4 جلسات شهرياً) والمتابعة اليومية عبر الواتساب.',
                'category' => 'العضويات',
                'is_active' => true,
                'sort_order' => 7,
                'user_id' => $coach->id,
            ],
            [
                'question' => 'هل يمكنني إلغاء اشتراكي في أي وقت؟',
                'answer' => 'نعم، يمكنك إلغاء اشتراكك في أي وقت من خلال لوحة التحكم أو بالتواصل معنا. ستستمر في الاستفادة من الخدمات حتى نهاية فترة الاشتراك المدفوعة.',
                'category' => 'العضويات',
                'is_active' => true,
                'sort_order' => 8,
                'user_id' => $coach->id,
            ],
            [
                'question' => 'هل تقدمون برامج للنساء فقط؟',
                'answer' => 'نعم، لدينا برامج مخصصة للنساء مع مدربات معتمدات. نوفر بيئة مريحة وآمنة للنساء مع برامج تناسب احتياجاتهن الخاصة في فترات مختلفة من الحياة.',
                'category' => 'البرامج',
                'is_active' => true,
                'sort_order' => 9,
                'user_id' => $coach->id,
            ],
            [
                'question' => 'هل يمكنني الحصول على برنامج غذائي مخصص؟',
                'answer' => 'نعم، جميع أعضاء العضوية الذهبية والبلاتينية يحصلون على برامج غذائية مخصصة تناسب أهدافهم ونمط حياتهم وتفضيلاتهم الغذائية.',
                'category' => 'التغذية',
                'is_active' => true,
                'sort_order' => 10,
                'user_id' => $coach->id,
            ]
        ];

        foreach ($faqs as $faqData) {
            Faq::create($faqData);
        }
    }
}