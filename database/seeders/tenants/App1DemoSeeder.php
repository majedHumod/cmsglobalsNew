<?php

namespace Database\Seeders\Tenants;

use App\Models\ClientProfile;
use App\Models\CoachAvailability;
use App\Models\MealPlan;
use App\Models\MembershipType;
use App\Models\Page;
use App\Models\ProgressCheckIn;
use App\Models\SessionBooking;
use App\Models\SubscriptionPlan;
use App\Models\SupplementPlan;
use App\Models\Testimonial;
use App\Models\TrainingSession;
use App\Models\User;
use App\Models\UserMembership;
use App\Models\Workout;
use App\Models\WorkoutSchedule;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * بيانات تجريبية شاملة لـ app1.cmsglobals.test
 * يُنشئ مدربين، عملاء، اشتراكات مدفوعة، جلسات، وجبات، وسجلات تقدم
 */
class App1DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('--- App1 Demo Seeder ---');

        $admin   = $this->seedAdmin();
        $coaches = $this->seedCoaches();
        $clients = $this->seedClients($coaches);

        $this->seedCoachAvailabilities($coaches);
        $this->seedSubscriptions($clients);
        $this->seedClientProfiles($clients);
        $this->seedProgressCheckIns($clients, $coaches);

        $sessions  = $this->seedTrainingSessions($coaches);
        $this->seedSessionBookings($sessions, $clients);

        $workouts  = $this->seedWorkouts($coaches);
        $this->seedWorkoutSchedules($workouts, $coaches);
        $this->seedMealPlans($coaches);
        $this->seedTestimonials($clients, $admin);
        $this->seedSupplementPlans($coaches);
        $this->seedClientJourneyPages($coaches[0]);

        $this->command->info('✅ App1 Demo Seeder finished.');
    }

    // ─────────────────────────────────────────
    // Admin
    // ─────────────────────────────────────────

    private function seedAdmin(): User
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@tenant.com'],
            [
                'name'     => 'مدير النظام',
                'password' => Hash::make('password'),
                'gender'   => 'male',
            ]
        );

        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }
        if (!$admin->hasRole('coach')) {
            $admin->assignRole('coach');
        }

        $this->command->info('  Admin ready: admin@tenant.com (admin + coach)');
        return $admin;
    }

    // ─────────────────────────────────────────
    // Coaches
    // ─────────────────────────────────────────

    private function seedCoaches(): array
    {
        $coachData = [
            [
                'name'   => 'أحمد الغامدي',
                'email'  => 'ahmad.coach@app1.local',
                'gender' => 'male',
            ],
            [
                'name'   => 'نورا السالم',
                'email'  => 'noura.coach@app1.local',
                'gender' => 'female',
            ],
        ];

        $coaches = [];
        foreach ($coachData as $data) {
            $coach = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'password' => Hash::make('password'),
                    'gender'   => $data['gender'],
                ]
            );

            if (!$coach->hasRole('coach')) {
                $coach->assignRole('coach');
            }

            $coaches[] = $coach;
            $this->command->info("  Coach ready: {$data['email']}");
        }

        return $coaches;
    }

    // ─────────────────────────────────────────
    // Clients
    // ─────────────────────────────────────────

    private function seedClients(array $coaches): array
    {
        $clientsData = [
            // عملاء المدرب أحمد (ذكور)
            ['name' => 'محمد علي الشهري',     'email' => 'mohammed.ali@app1.local',    'phone' => '+966500000001', 'gender' => 'male',   'coach' => $coaches[0]],
            ['name' => 'عبدالله القحطاني',    'email' => 'abdullah.q@app1.local',      'phone' => '+966500000002', 'gender' => 'male',   'coach' => $coaches[0]],
            ['name' => 'فيصل الحربي',         'email' => 'faisal.harbi@app1.local',    'phone' => '+966500000003', 'gender' => 'male',   'coach' => $coaches[0]],
            ['name' => 'خالد الزهراني',       'email' => 'khalid.z@app1.local',        'phone' => '+966500000004', 'gender' => 'male',   'coach' => $coaches[0]],
            ['name' => 'سعد العتيبي',         'email' => 'saad.otaibi@app1.local',     'phone' => '+966500000005', 'gender' => 'male',   'coach' => $coaches[0]],
            ['name' => 'يوسف الدوسري',        'email' => 'yousuf.dosari@app1.local',   'phone' => '+966500000006', 'gender' => 'male',   'coach' => $coaches[0]],
            // عملاء المدربة نورا (إناث)
            ['name' => 'سارة المطيري',        'email' => 'sara.m@app1.local',          'phone' => '+966500000007', 'gender' => 'female', 'coach' => $coaches[1]],
            ['name' => 'لمياء الدوسري',       'email' => 'lamya.d@app1.local',         'phone' => '+966500000008', 'gender' => 'female', 'coach' => $coaches[1]],
            ['name' => 'هنوف الحمدان',        'email' => 'hanoof.h@app1.local',        'phone' => '+966500000009', 'gender' => 'female', 'coach' => $coaches[1]],
            ['name' => 'ريم الحمادي',         'email' => 'reem.vip@app1.local',        'phone' => '+966500000010', 'gender' => 'female', 'coach' => $coaches[1]],
            ['name' => 'نادية العمري',        'email' => 'nadia.omari@app1.local',     'phone' => '+966500000011', 'gender' => 'female', 'coach' => $coaches[1]],
            ['name' => 'دلال الشمري',         'email' => 'dalal.sh@app1.local',        'phone' => '+966500000012', 'gender' => 'female', 'coach' => $coaches[1]],
        ];

        $clients = [];
        foreach ($clientsData as $data) {
            $client = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'phone'    => $data['phone'],
                    'password' => Hash::make('password'),
                    'gender'   => $data['gender'],
                    'coach_id' => $data['coach']->id,
                ]
            );

            // تحديث coach_id / phone إن كان الحساب موجوداً مسبقاً
            $client->fill([
                'coach_id' => $client->coach_id ?: $data['coach']->id,
                'phone' => $client->phone ?: $data['phone'],
            ]);
            if ($client->isDirty()) {
                $client->save();
            }

            if (!$client->hasRole('user')) {
                $client->assignRole('user');
            }

            $clients[] = $client;
        }

        $this->command->info('  Clients ready: ' . count($clients));
        return $clients;
    }

    // ─────────────────────────────────────────
    // Coach Availabilities
    // ─────────────────────────────────────────

    private function seedCoachAvailabilities(array $coaches): void
    {
        if (!Schema::hasTable('coach_availabilities')) {
            return;
        }

        // أحمد الغامدي: أحد - خميس (0,1,2,3,4)، فترتان
        $ahmadSlots = [
            ['day' => 0, 'start' => '07:00', 'end' => '10:00', 'location' => 'صالة النادي الرئيسية'],
            ['day' => 0, 'start' => '17:00', 'end' => '20:00', 'location' => 'صالة النادي الرئيسية'],
            ['day' => 1, 'start' => '07:00', 'end' => '10:00', 'location' => 'صالة النادي الرئيسية'],
            ['day' => 1, 'start' => '17:00', 'end' => '20:00', 'location' => 'صالة النادي الرئيسية'],
            ['day' => 2, 'start' => '07:00', 'end' => '10:00', 'location' => 'صالة النادي الرئيسية'],
            ['day' => 2, 'start' => '17:00', 'end' => '20:00', 'location' => 'صالة النادي الرئيسية'],
            ['day' => 3, 'start' => '07:00', 'end' => '10:00', 'location' => 'صالة النادي الرئيسية'],
            ['day' => 3, 'start' => '17:00', 'end' => '20:00', 'location' => 'صالة النادي الرئيسية'],
            ['day' => 4, 'start' => '07:00', 'end' => '10:00', 'location' => 'صالة النادي الرئيسية'],
            ['day' => 4, 'start' => '17:00', 'end' => '20:00', 'location' => 'صالة النادي الرئيسية'],
        ];

        // نورا السالم: اثنين - جمعة (1,2,3,4,5)، فترتان
        $nouraSlots = [
            ['day' => 1, 'start' => '09:00', 'end' => '12:00', 'location' => 'صالة السيدات'],
            ['day' => 1, 'start' => '16:00', 'end' => '19:00', 'location' => 'صالة السيدات'],
            ['day' => 2, 'start' => '09:00', 'end' => '12:00', 'location' => 'صالة السيدات'],
            ['day' => 2, 'start' => '16:00', 'end' => '19:00', 'location' => 'صالة السيدات'],
            ['day' => 3, 'start' => '09:00', 'end' => '12:00', 'location' => 'صالة السيدات'],
            ['day' => 3, 'start' => '16:00', 'end' => '19:00', 'location' => 'صالة السيدات'],
            ['day' => 4, 'start' => '09:00', 'end' => '12:00', 'location' => 'صالة السيدات'],
            ['day' => 4, 'start' => '16:00', 'end' => '19:00', 'location' => 'صالة السيدات'],
            ['day' => 5, 'start' => '10:00', 'end' => '13:00', 'location' => 'أونلاين'],
        ];

        $slotsMap = [
            $coaches[0]->id => $ahmadSlots,
            $coaches[1]->id => $nouraSlots,
        ];

        foreach ($slotsMap as $coachId => $slots) {
            foreach ($slots as $slot) {
                CoachAvailability::firstOrCreate(
                    [
                        'user_id'     => $coachId,
                        'day_of_week' => $slot['day'],
                        'start_time'  => $slot['start'],
                    ],
                    [
                        'end_time'              => $slot['end'],
                        'slot_duration_minutes' => 60,
                        'buffer_minutes'        => 10,
                        'location'              => $slot['location'],
                        'capacity'              => 1,
                        'is_active'             => true,
                    ]
                );
            }
        }

        $this->command->info('  Coach availabilities seeded.');
    }

    // ─────────────────────────────────────────
    // Subscriptions
    // ─────────────────────────────────────────

    private function seedSubscriptions(array $clients): void
    {
        if (!Schema::hasTable('user_memberships')) {
            return;
        }

        $monthlyType = MembershipType::where('slug', 'monthly')->first();
        $yearlyType  = MembershipType::where('slug', 'yearly')->first();
        $vipType     = MembershipType::where('slug', 'vip')->first();

        $monthlyPlan = SubscriptionPlan::where('slug', 'like', '%monthly%')->first();
        $yearlyPlan  = SubscriptionPlan::where('slug', 'like', '%yearly%')->first();
        $vipPlan     = SubscriptionPlan::where('slug', 'like', '%vip%')->first();

        // خطة الاشتراكات لكل عميل:
        // 0: محمد علي       → شهري مدفوع (نشط)
        // 1: عبدالله        → شهري مدفوع (نشط)
        // 2: فيصل           → شهري مدفوع (نشط)
        // 3: خالد           → سنوي مدفوع (نشط)
        // 4: سعد            → شهري مدفوع (نشط)
        // 5: يوسف           → شهري منتهي (للتنوع)
        // 6: سارة           → شهري مدفوع (نشط)
        // 7: لمياء          → VIP مدفوع (نشط)
        // 8: هنوف           → شهري مدفوع (نشط)
        // 9: ريم            → VIP مدفوع (نشط) - عميلة VIP
        // 10: نادية         → سنوي مدفوع (نشط)
        // 11: دلال          → VIP مدفوع (نشط)

        $subscriptionMatrix = [
            0  => ['type' => $monthlyType, 'plan' => $monthlyPlan, 'price' => 29.99, 'days' => 30,  'active' => true,  'ref' => 'PAY-2026-M-001'],
            1  => ['type' => $monthlyType, 'plan' => $monthlyPlan, 'price' => 29.99, 'days' => 30,  'active' => true,  'ref' => 'PAY-2026-M-002'],
            2  => ['type' => $monthlyType, 'plan' => $monthlyPlan, 'price' => 29.99, 'days' => 30,  'active' => true,  'ref' => 'PAY-2026-M-003'],
            3  => ['type' => $yearlyType,  'plan' => $yearlyPlan,  'price' => 299.99, 'days' => 365, 'active' => true, 'ref' => 'PAY-2026-Y-001'],
            4  => ['type' => $monthlyType, 'plan' => $monthlyPlan, 'price' => 29.99, 'days' => 30,  'active' => true,  'ref' => 'PAY-2026-M-004'],
            5  => ['type' => $monthlyType, 'plan' => $monthlyPlan, 'price' => 29.99, 'days' => -5,  'active' => false, 'ref' => 'PAY-2026-M-EXP'],
            6  => ['type' => $monthlyType, 'plan' => $monthlyPlan, 'price' => 29.99, 'days' => 30,  'active' => true,  'ref' => 'PAY-2026-M-005'],
            7  => ['type' => $vipType,     'plan' => $vipPlan,     'price' => 99.99, 'days' => 30,  'active' => true,  'ref' => 'PAY-2026-V-001'],
            8  => ['type' => $monthlyType, 'plan' => $monthlyPlan, 'price' => 29.99, 'days' => 30,  'active' => true,  'ref' => 'PAY-2026-M-006'],
            9  => ['type' => $vipType,     'plan' => $vipPlan,     'price' => 99.99, 'days' => 30,  'active' => true,  'ref' => 'PAY-2026-V-002'],
            10 => ['type' => $yearlyType,  'plan' => $yearlyPlan,  'price' => 299.99, 'days' => 365, 'active' => true, 'ref' => 'PAY-2026-Y-002'],
            11 => ['type' => $vipType,     'plan' => $vipPlan,     'price' => 99.99, 'days' => 30,  'active' => true,  'ref' => 'PAY-2026-V-003'],
        ];

        $count = 0;
        foreach ($clients as $index => $client) {
            $matrix = $subscriptionMatrix[$index] ?? null;
            if (!$matrix || !$matrix['type']) {
                continue;
            }

            $startsAt  = Carbon::now()->subDays(5);
            $expiresAt = $matrix['days'] > 0
                ? Carbon::now()->addDays($matrix['days'])
                : Carbon::now()->addDays($matrix['days']); // منتهي

            $membership = UserMembership::firstOrCreate(
                [
                    'user_id'           => $client->id,
                    'payment_reference' => $matrix['ref'],
                ],
                [
                    'membership_type_id'  => optional($matrix['type'])->id,
                    'subscription_plan_id'=> optional($matrix['plan'])->id,
                    'starts_at'           => $startsAt,
                    'expires_at'          => $expiresAt,
                    'is_active'           => $matrix['active'],
                    'payment_status'      => 'paid',
                    'payment_amount'      => $matrix['price'],
                ]
            );

            // تحديث membership_type_id على المستخدم
            if ($matrix['active'] && optional($matrix['type'])->id) {
                $client->update([
                    'membership_type_id'  => $matrix['type']->id,
                    'membership_expires_at' => $expiresAt,
                ]);
            }

            $count++;
        }

        $this->command->info("  Subscriptions seeded: {$count}");
    }

    // ─────────────────────────────────────────
    // Client Profiles
    // ─────────────────────────────────────────

    private function seedClientProfiles(array $clients): void
    {
        if (!Schema::hasTable('client_profiles')) {
            return;
        }

        // القيم المسموحة في ENUM: beginner | intermediate | advanced
        $profiles = [
            // محمد علي
            ['fitness_goal' => 'بناء الكتلة العضلية وزيادة القوة', 'target_weight' => 85.0, 'activity_level' => 'intermediate', 'preferred_contact_method' => 'whatsapp', 'injuries' => null, 'medical_notes' => null, 'onboarding_notes' => 'يرغب بزيادة 5 كغ عضلة خلال 6 أشهر'],
            // عبدالله
            ['fitness_goal' => 'إنقاص الوزن وتحسين اللياقة القلبية', 'target_weight' => 78.0, 'activity_level' => 'beginner', 'preferred_contact_method' => 'phone', 'injuries' => 'ألم خفيف في الركبة اليمنى', 'medical_notes' => 'يجب تجنب القفز المرتفع', 'onboarding_notes' => 'بدأ التدريب منذ 3 أشهر'],
            // فيصل
            ['fitness_goal' => 'تحسين القدرة التحملية واللياقة العامة', 'target_weight' => 75.0, 'activity_level' => 'beginner', 'preferred_contact_method' => 'whatsapp', 'injuries' => null, 'medical_notes' => null, 'onboarding_notes' => 'مبتدئ كامل، يحتاج برنامج تدريجي'],
            // خالد
            ['fitness_goal' => 'خسارة الدهون مع الحفاظ على العضلة', 'target_weight' => 80.0, 'activity_level' => 'advanced', 'preferred_contact_method' => 'email', 'injuries' => null, 'medical_notes' => 'مرض سكري من النوع الثاني - تحت السيطرة', 'onboarding_notes' => 'لاعب سابق، يريد العودة للتدريب'],
            // سعد
            ['fitness_goal' => 'اللياقة العامة وتحسين المرونة', 'target_weight' => 70.0, 'activity_level' => 'beginner', 'preferred_contact_method' => 'whatsapp', 'injuries' => null, 'medical_notes' => null, 'onboarding_notes' => 'يعمل بشكل مكتبي، يريد التحرك أكثر'],
            // يوسف
            ['fitness_goal' => 'تحسين القوة العضلية', 'target_weight' => 90.0, 'activity_level' => 'intermediate', 'preferred_contact_method' => 'phone', 'injuries' => 'إصابة قديمة في الكتف اليسرى', 'medical_notes' => 'لا ترفع أثقالاً فوق الرأس بالكتف اليسرى', 'onboarding_notes' => 'اشتراكه منتهي، تواصل معه للتجديد'],
            // سارة
            ['fitness_goal' => 'إنقاص الوزن وتنسيق الجسم', 'target_weight' => 60.0, 'activity_level' => 'beginner', 'preferred_contact_method' => 'whatsapp', 'injuries' => null, 'medical_notes' => null, 'onboarding_notes' => 'تفضل تمارين الكارديو والمرونة'],
            // لمياء
            ['fitness_goal' => 'بناء الثقة بالنفس وتحسين اللياقة', 'target_weight' => 65.0, 'activity_level' => 'intermediate', 'preferred_contact_method' => 'whatsapp', 'injuries' => null, 'medical_notes' => null, 'onboarding_notes' => 'VIP - تريد تدريباً خاصاً كاملاً'],
            // هنوف
            ['fitness_goal' => 'رياضة ما بعد الولادة وإعادة التأهيل', 'target_weight' => 58.0, 'activity_level' => 'beginner', 'preferred_contact_method' => 'phone', 'injuries' => null, 'medical_notes' => 'ولدت قبل 6 أشهر - تمارين منخفضة الشدة فقط', 'onboarding_notes' => 'تحتاج برنامجاً خاصاً بما بعد الولادة'],
            // ريم (VIP)
            ['fitness_goal' => 'نمط حياة صحي ومتوازن ورياضة منتظمة', 'target_weight' => 57.0, 'activity_level' => 'advanced', 'preferred_contact_method' => 'whatsapp', 'injuries' => null, 'medical_notes' => null, 'onboarding_notes' => 'VIP - تحضر 5 أيام أسبوعياً، منضبطة جداً'],
            // نادية
            ['fitness_goal' => 'تقليل الدهون وزيادة العضل', 'target_weight' => 62.0, 'activity_level' => 'intermediate', 'preferred_contact_method' => 'email', 'injuries' => null, 'medical_notes' => null, 'onboarding_notes' => 'اشتراك سنوي - هدفها 12 شهراً متواصلة'],
            // دلال
            ['fitness_goal' => 'المرونة والتوازن وتقليل التوتر', 'target_weight' => 55.0, 'activity_level' => 'beginner', 'preferred_contact_method' => 'whatsapp', 'injuries' => null, 'medical_notes' => 'ضغط دم مرتفع خفيف - تتابع طبياً', 'onboarding_notes' => 'تفضل اليوغا والتأمل الحركي'],
        ];

        foreach ($clients as $index => $client) {
            $profileData = $profiles[$index] ?? [];
            if (empty($profileData)) {
                continue;
            }

            ClientProfile::firstOrCreate(
                ['user_id' => $client->id],
                $profileData
            );
        }

        $this->command->info('  Client profiles seeded.');
    }

    // ─────────────────────────────────────────
    // Progress Check-Ins
    // ─────────────────────────────────────────

    private function seedProgressCheckIns(array $clients, array $coaches): void
    {
        if (!Schema::hasTable('progress_check_ins')) {
            return;
        }

        // قياسات ابتدائية وتطور تدريجي لكل عميل
        $progressData = [
            // محمد علي - يبني عضلة (وزن يزيد)
            0 => [
                ['w' => 79.5, 'bf' => 18.0, 'waist' => 87, 'energy' => 7, 'training' => 85, 'nutrition' => 75, 'daysAgo' => 90, 'notes' => 'البداية - جيد', 'feedback' => 'الالتزام ممتاز في الأسابيع الأولى'],
                ['w' => 80.8, 'bf' => 17.5, 'waist' => 86, 'energy' => 8, 'training' => 90, 'nutrition' => 80, 'daysAgo' => 60, 'notes' => 'تحسن ملحوظ في القوة', 'feedback' => 'استمر، الوزن يسير بالاتجاه الصحيح'],
                ['w' => 82.1, 'bf' => 16.8, 'waist' => 84, 'energy' => 9, 'training' => 95, 'nutrition' => 85, 'daysAgo' => 30, 'notes' => 'رفعت الأوزان بشكل ممتاز هذا الشهر', 'feedback' => 'تقدم رائع، نزيد الحمل الأسبوع القادم'],
            ],
            // عبدالله - يخسر وزناً
            1 => [
                ['w' => 92.0, 'bf' => 27.0, 'waist' => 98, 'energy' => 5, 'training' => 70, 'nutrition' => 60, 'daysAgo' => 90, 'notes' => 'بداية صعبة، الركبة تؤلم أحياناً', 'feedback' => 'نعدّل التمارين لتجنب الضغط على الركبة'],
                ['w' => 89.3, 'bf' => 25.5, 'waist' => 95, 'energy' => 6, 'training' => 80, 'nutrition' => 70, 'daysAgo' => 60, 'notes' => 'تحسن في التحمل، الركبة أفضل', 'feedback' => 'خسرت 2.7 كغ في شهرين - ممتاز'],
                ['w' => 87.1, 'bf' => 24.0, 'waist' => 92, 'energy' => 7, 'training' => 85, 'nutrition' => 75, 'daysAgo' => 30, 'notes' => 'الالتزام بالنظام الغذائي أفضل', 'feedback' => 'تقدم رائع! استمر على هذا المنوال'],
            ],
            // فيصل - مبتدئ
            2 => [
                ['w' => 70.0, 'bf' => 22.0, 'waist' => 82, 'energy' => 4, 'training' => 60, 'nutrition' => 55, 'daysAgo' => 75, 'notes' => 'صعوبة في البداية طبيعية', 'feedback' => 'صبر وثقة، التدريب يحتاج وقتاً'],
                ['w' => 69.2, 'bf' => 21.0, 'waist' => 80, 'energy' => 6, 'training' => 75, 'nutrition' => 65, 'daysAgo' => 45, 'notes' => 'بدأت أحب التدريب', 'feedback' => 'تطور جيد في الأداء والمتعة'],
                ['w' => 68.5, 'bf' => 19.5, 'waist' => 78, 'energy' => 7, 'training' => 80, 'nutrition' => 72, 'daysAgo' => 15, 'notes' => 'التنفس أفضل بكثير', 'feedback' => 'تحسن ملموس خلال 3 أشهر، واصل'],
            ],
            // خالد - لاعب سابق
            3 => [
                ['w' => 88.0, 'bf' => 20.0, 'waist' => 91, 'energy' => 8, 'training' => 90, 'nutrition' => 80, 'daysAgo' => 90, 'notes' => 'عودة قوية للتدريب', 'feedback' => 'الأداء الأولي ممتاز لشخص عاد بعد توقف'],
                ['w' => 86.0, 'bf' => 18.5, 'waist' => 88, 'energy' => 9, 'training' => 95, 'nutrition' => 85, 'daysAgo' => 50, 'notes' => 'أشعر أنني عدت لمستواي القديم', 'feedback' => 'ممتاز! السكر يتحكم به جيداً مع التمارين'],
                ['w' => 84.5, 'bf' => 17.0, 'waist' => 86, 'energy' => 9, 'training' => 95, 'nutrition' => 90, 'daysAgo' => 20, 'notes' => 'أفضل شهر على الإطلاق', 'feedback' => 'تقدم استثنائي! قريباً من الهدف'],
            ],
            // سارة
            6 => [
                ['w' => 70.5, 'bf' => 30.0, 'waist' => 78, 'energy' => 6, 'training' => 75, 'nutrition' => 70, 'daysAgo' => 80, 'notes' => 'تمارين الكارديو رائعة', 'feedback' => 'بداية ممتازة، الكارديو يناسبك جداً'],
                ['w' => 68.8, 'bf' => 28.5, 'waist' => 75, 'energy' => 7, 'training' => 80, 'nutrition' => 78, 'daysAgo' => 50, 'notes' => 'الملابس أصبحت أكثر رحابة!', 'feedback' => 'واضح الفرق! استمري بنفس الالتزام'],
                ['w' => 67.0, 'bf' => 27.0, 'waist' => 73, 'energy' => 8, 'training' => 88, 'nutrition' => 82, 'daysAgo' => 20, 'notes' => 'أشعر بالطاقة طوال اليوم', 'feedback' => 'تقدم مذهل! خسرت 3.5 كغ في 2 شهر'],
            ],
            // ريم (VIP)
            9 => [
                ['w' => 60.0, 'bf' => 22.0, 'waist' => 65, 'energy' => 9, 'training' => 95, 'nutrition' => 90, 'daysAgo' => 90, 'notes' => 'انتظام مثالي منذ اليوم الأول', 'feedback' => 'أداء استثنائي! تدريب يومي منتظم'],
                ['w' => 58.8, 'bf' => 20.5, 'waist' => 63, 'energy' => 9, 'training' => 98, 'nutrition' => 92, 'daysAgo' => 55, 'notes' => 'أعظم قرار اتخذته في حياتي', 'feedback' => 'مثال يُحتذى به! 5 أيام أسبوعياً بانتظام'],
                ['w' => 57.5, 'bf' => 19.0, 'waist' => 61, 'energy' => 10, 'training' => 100, 'nutrition' => 95, 'daysAgo' => 20, 'notes' => 'بلغت وزني المستهدف!', 'feedback' => 'وصلتِ للهدف! الآن نعمل على الصيانة والمحافظة'],
            ],
        ];

        $coachMap = [
            0 => $coaches[0], 1 => $coaches[0], 2 => $coaches[0],
            3 => $coaches[0], 4 => $coaches[0], 5 => $coaches[0],
            6 => $coaches[1], 7 => $coaches[1], 8 => $coaches[1],
            9 => $coaches[1], 10 => $coaches[1], 11 => $coaches[1],
        ];

        $count = 0;
        foreach ($progressData as $clientIndex => $checkIns) {
            $client = $clients[$clientIndex] ?? null;
            $coach  = $coachMap[$clientIndex] ?? $coaches[0];
            if (!$client) {
                continue;
            }

            foreach ($checkIns as $ci) {
                ProgressCheckIn::firstOrCreate(
                    [
                        'user_id'       => $client->id,
                        'checked_in_at' => Carbon::now()->subDays($ci['daysAgo'])->startOfDay(),
                    ],
                    [
                        'coach_id'            => $coach->id,
                        'submitted_by_user_id'=> $coach->id,
                        'weight'              => $ci['w'],
                        'body_fat_percentage' => $ci['bf'],
                        'waist_cm'            => $ci['waist'],
                        'energy_level'        => $ci['energy'],
                        'training_adherence'  => $ci['training'],
                        'nutrition_adherence' => $ci['nutrition'],
                        'notes'               => $ci['notes'],
                        'coach_feedback'      => $ci['feedback'],
                        'next_steps'          => 'مواصلة البرنامج الحالي مع مراجعة التغذية',
                    ]
                );
                $count++;
            }
        }

        $this->command->info("  Progress check-ins seeded: {$count}");
    }

    // ─────────────────────────────────────────
    // Training Sessions
    // ─────────────────────────────────────────

    private function seedTrainingSessions(array $coaches): array
    {
        if (!Schema::hasTable('training_sessions')) {
            return [];
        }

        $sessionsData = [
            // القيم المسموحة في ENUM: online | in_person | hybrid
            [
                'title'        => 'جلسة قوة للمبتدئين',
                'description'  => 'برنامج تدريبي مكثف للمبتدئين يشمل التمارين الأساسية لبناء القوة العضلية وتحسين التحمل.',
                'price'        => 150.00,
                'duration_hours' => 1,
                'session_type' => 'in_person',
                'capacity'     => 1,
                'location'     => 'صالة النادي الرئيسية',
                'is_visible'   => true,
                'sort_order'   => 1,
                'user_id'      => $coaches[0]->id,
                'audience_gender' => 'male',
            ],
            [
                'title'        => 'تدريب HIIT متقدم',
                'description'  => 'جلسة تدريب متقطع عالي الكثافة لحرق الدهون وتحسين اللياقة القلبية لدى المتقدمين.',
                'price'        => 200.00,
                'duration_hours' => 1,
                'session_type' => 'in_person',
                'capacity'     => 8,
                'location'     => 'صالة الكارديو',
                'is_visible'   => true,
                'sort_order'   => 2,
                'user_id'      => $coaches[0]->id,
                'audience_gender' => 'all',
            ],
            [
                'title'        => 'يوغا وتمدد للسيدات',
                'description'  => 'جلسة يوغا متكاملة تشمل تمارين التنفس والتمدد والاسترخاء، مخصصة للسيدات.',
                'price'        => 120.00,
                'duration_hours' => 1,
                'session_type' => 'in_person',
                'capacity'     => 10,
                'location'     => 'صالة السيدات',
                'is_visible'   => true,
                'sort_order'   => 3,
                'user_id'      => $coaches[1]->id,
                'audience_gender' => 'female',
            ],
            [
                'title'        => 'جلسة تدريب شخصي VIP',
                'description'  => 'تدريب شخصي واحد لواحد مخصص بالكامل لأهدافك الفردية مع متابعة دقيقة ومستمرة.',
                'price'        => 350.00,
                'duration_hours' => 2,
                'session_type' => 'in_person',
                'capacity'     => 1,
                'location'     => 'استوديو VIP',
                'is_visible'   => true,
                'sort_order'   => 4,
                'user_id'      => $coaches[1]->id,
                'audience_gender' => 'female',
            ],
            [
                'title'        => 'تدريب القوة الجماعي',
                'description'  => 'جلسة تدريب بالأثقال في مجموعة صغيرة لتحسين القوة وبناء العضلات بروح تنافسية ممتعة.',
                'price'        => 100.00,
                'duration_hours' => 1,
                'session_type' => 'in_person',
                'capacity'     => 6,
                'location'     => 'صالة الأثقال',
                'is_visible'   => true,
                'sort_order'   => 5,
                'user_id'      => $coaches[0]->id,
                'audience_gender' => 'male',
            ],
            [
                'title'        => 'كارديو أونلاين',
                'description'  => 'جلسة كارديو عبر الإنترنت يمكنك حضورها من المنزل. مناسبة للمبتدئين والمتوسطين.',
                'price'        => 80.00,
                'duration_hours' => 1,
                'session_type' => 'online',
                'capacity'     => 20,
                'location'     => 'أونلاين - زوم',
                'is_visible'   => true,
                'sort_order'   => 6,
                'user_id'      => $coaches[1]->id,
                'audience_gender' => 'all',
            ],
        ];

        $sessions = [];
        foreach ($sessionsData as $data) {
            $session = TrainingSession::firstOrCreate(
                ['title' => $data['title'], 'user_id' => $data['user_id']],
                $data
            );
            $sessions[] = $session;
        }

        $this->command->info('  Training sessions seeded: ' . count($sessions));
        return $sessions;
    }

    // ─────────────────────────────────────────
    // Session Bookings
    // ─────────────────────────────────────────

    private function seedSessionBookings(array $sessions, array $clients): void
    {
        if (!Schema::hasTable('session_bookings') || empty($sessions)) {
            return;
        }

        // حجوزات متنوعة: ماضية (attended/completed)، حاضرة (confirmed)، مستقبلية (confirmed/pending)
        // القيم المسموحة في attendance_status ENUM: scheduled | attended | missed | late_cancelled
        $bookingsData = [
            // جلسة قوة مبتدئين - محمد علي (3 حجوزات ماضية + 1 مستقبلية)
            ['session' => 0, 'client' => 0, 'daysOffset' => -30, 'status' => 'completed', 'attendance' => 'attended',  'payment' => 'paid',    'amount' => 150.00, 'ref' => 'BK-S001-001'],
            ['session' => 0, 'client' => 0, 'daysOffset' => -14, 'status' => 'completed', 'attendance' => 'attended',  'payment' => 'paid',    'amount' => 150.00, 'ref' => 'BK-S001-002'],
            ['session' => 0, 'client' => 0, 'daysOffset' => -7,  'status' => 'completed', 'attendance' => 'attended',  'payment' => 'paid',    'amount' => 150.00, 'ref' => 'BK-S001-003'],
            ['session' => 0, 'client' => 0, 'daysOffset' => 7,   'status' => 'confirmed',  'attendance' => 'scheduled', 'payment' => 'paid',    'amount' => 150.00, 'ref' => 'BK-S001-004'],
            // جلسة قوة - عبدالله
            ['session' => 0, 'client' => 1, 'daysOffset' => -21, 'status' => 'completed', 'attendance' => 'attended',  'payment' => 'paid',    'amount' => 150.00, 'ref' => 'BK-S001-005'],
            ['session' => 0, 'client' => 1, 'daysOffset' => 14,  'status' => 'confirmed',  'attendance' => 'scheduled', 'payment' => 'paid',    'amount' => 150.00, 'ref' => 'BK-S001-006'],
            // HIIT - خالد وسعد
            ['session' => 1, 'client' => 3, 'daysOffset' => -10, 'status' => 'completed', 'attendance' => 'attended',  'payment' => 'paid',    'amount' => 200.00, 'ref' => 'BK-S002-001'],
            ['session' => 1, 'client' => 4, 'daysOffset' => -10, 'status' => 'completed', 'attendance' => 'attended',  'payment' => 'paid',    'amount' => 200.00, 'ref' => 'BK-S002-002'],
            ['session' => 1, 'client' => 3, 'daysOffset' => 3,   'status' => 'confirmed',  'attendance' => 'scheduled', 'payment' => 'paid',    'amount' => 200.00, 'ref' => 'BK-S002-003'],
            // يوغا للسيدات - سارة، لمياء، هنوف، دلال
            ['session' => 2, 'client' => 6, 'daysOffset' => -20, 'status' => 'completed', 'attendance' => 'attended',  'payment' => 'paid',    'amount' => 120.00, 'ref' => 'BK-S003-001'],
            ['session' => 2, 'client' => 7, 'daysOffset' => -20, 'status' => 'completed', 'attendance' => 'attended',  'payment' => 'paid',    'amount' => 120.00, 'ref' => 'BK-S003-002'],
            ['session' => 2, 'client' => 11,'daysOffset' => -20, 'status' => 'completed', 'attendance' => 'missed',    'payment' => 'paid',    'amount' => 120.00, 'ref' => 'BK-S003-003'],
            ['session' => 2, 'client' => 6, 'daysOffset' => 5,   'status' => 'confirmed',  'attendance' => 'scheduled', 'payment' => 'paid',    'amount' => 120.00, 'ref' => 'BK-S003-004'],
            ['session' => 2, 'client' => 7, 'daysOffset' => 5,   'status' => 'confirmed',  'attendance' => 'scheduled', 'payment' => 'paid',    'amount' => 120.00, 'ref' => 'BK-S003-005'],
            // VIP - ريم
            ['session' => 3, 'client' => 9, 'daysOffset' => -28, 'status' => 'completed', 'attendance' => 'attended',  'payment' => 'paid',    'amount' => 350.00, 'ref' => 'BK-S004-001'],
            ['session' => 3, 'client' => 9, 'daysOffset' => -14, 'status' => 'completed', 'attendance' => 'attended',  'payment' => 'paid',    'amount' => 350.00, 'ref' => 'BK-S004-002'],
            ['session' => 3, 'client' => 9, 'daysOffset' => 7,   'status' => 'confirmed',  'attendance' => 'scheduled', 'payment' => 'paid',    'amount' => 350.00, 'ref' => 'BK-S004-003'],
            // كارديو أونلاين - نادية، هنوف، فيصل
            ['session' => 5, 'client' => 10,'daysOffset' => -5,  'status' => 'completed', 'attendance' => 'attended',  'payment' => 'paid',    'amount' => 80.00,  'ref' => 'BK-S006-001'],
            ['session' => 5, 'client' => 8, 'daysOffset' => -5,  'status' => 'completed', 'attendance' => 'attended',  'payment' => 'paid',    'amount' => 80.00,  'ref' => 'BK-S006-002'],
            ['session' => 5, 'client' => 2, 'daysOffset' => 10,  'status' => 'pending',    'attendance' => 'scheduled', 'payment' => 'pending', 'amount' => 80.00,  'ref' => null],
        ];

        // القيد الفريد هو (training_session_id, booking_date, booking_time) معاً
        // لذا يجب أن يكون لكل حجز في نفس الجلسة ونفس اليوم وقت مختلف
        $slotCounter = [];

        $count = 0;
        foreach ($bookingsData as $bd) {
            $session = $sessions[$bd['session']] ?? null;
            $client  = $clients[$bd['client']] ?? null;
            if (!$session || !$client) {
                continue;
            }

            $bookingDate = Carbon::now()->addDays($bd['daysOffset'])->toDateString();

            // توليد وقت فريد لكل (session_id, booking_date)
            $slotKey     = $session->id . '_' . $bookingDate;
            $slotIndex   = $slotCounter[$slotKey] ?? 0;
            $bookingTime = Carbon::createFromTime(9, 0)->addHours($slotIndex)->format('H:i');
            $slotCounter[$slotKey] = $slotIndex + 1;

            SessionBooking::firstOrCreate(
                [
                    'training_session_id' => $session->id,
                    'booking_date'        => $bookingDate,
                    'booking_time'        => $bookingTime,
                ],
                [
                    'user_id'           => $client->id,
                    'status'            => $bd['status'],
                    'attendance_status' => $bd['attendance'],
                    'payment_status'    => $bd['payment'],
                    'payment_amount'    => $bd['amount'],
                    'payment_reference' => $bd['ref'],
                ]
            );
            $count++;
        }

        $this->command->info("  Session bookings seeded: {$count}");
    }

    // ─────────────────────────────────────────
    // Workouts
    // ─────────────────────────────────────────

    private function seedWorkouts(array $coaches): array
    {
        if (!Schema::hasTable('workouts')) {
            return [];
        }

        $workoutsData = [
            [
                'name'            => 'برنامج القوة للمبتدئين - الجزء العلوي',
                'description'     => "تمرين شامل للجزء العلوي من الجسم يشمل:\n- بنش بريس: 3 × 10\n- تجديف بالبار: 3 × 10\n- عقلة: 3 × 6\n- ضغط كتف: 3 × 10\n- كيرل بيسبس: 3 × 12\n- تمديد ترايسبس: 3 × 12",
                'duration'        => 45,
                'difficulty'      => 'easy',
                'status'          => true,
                'user_id'         => $coaches[0]->id,
                'audience_gender' => 'male',
            ],
            [
                'name'            => 'برنامج القوة للمبتدئين - الجزء السفلي',
                'description'     => "تمرين للجزء السفلي من الجسم:\n- سكوات: 4 × 10\n- ديدليفت: 3 × 8\n- لانج: 3 × 12 لكل رجل\n- ليج بريس: 4 × 12\n- رفعة أكواع: 3 × 15",
                'duration'        => 50,
                'difficulty'      => 'medium',
                'status'          => true,
                'user_id'         => $coaches[0]->id,
                'audience_gender' => 'male',
            ],
            [
                'name'            => 'HIIT كارديو 20 دقيقة',
                'description'     => "جلسة HIIT متقطعة:\n- إحماء: 3 دقائق\n- 8 جولات (20 ثانية شدة عالية + 10 ثواني راحة):\n  • قفز النجمة\n  • بيربي\n  • ركض في المكان\n  • سبرينت قصير\n- تهدئة: 3 دقائق",
                'duration'        => 20,
                'difficulty'      => 'hard',
                'status'          => true,
                'user_id'         => $coaches[0]->id,
                'audience_gender' => 'all',
            ],
            [
                'name'            => 'يوغا للمبتدئات - تمارين الصباح',
                'description'     => "روتين يوغا صباحي:\n- تنفس عميق: 5 دقائق\n- وضعية القط والبقرة: 10 مرات\n- وضعية الكلب نحو الأسفل: 3 × 30 ثانية\n- وضعية المحارب الأول: 30 ثانية لكل جانب\n- وضعية الطفل: 2 دقيقة\n- تأمل: 5 دقائق",
                'duration'        => 30,
                'difficulty'      => 'easy',
                'status'          => true,
                'user_id'         => $coaches[1]->id,
                'audience_gender' => 'female',
            ],
            [
                'name'            => 'تمارين تنسيق الجسم للسيدات',
                'description'     => "برنامج تنسيق متكامل:\n- سكوات سومو: 4 × 15\n- ديدليفت رومانية: 3 × 12\n- هيب ثراست: 4 × 15\n- لانج جانبي: 3 × 12 لكل جانب\n- تمارين مؤخرة وخاصرة: 3 × 20",
                'duration'        => 45,
                'difficulty'      => 'medium',
                'status'          => true,
                'user_id'         => $coaches[1]->id,
                'audience_gender' => 'female',
            ],
            [
                'name'            => 'تدريب التحمل المتقدم',
                'description'     => "جلسة تحمل متقدمة:\n- ركض 10 دقائق\n- سلم الحبل: 3 × 60 ثانية\n- برو ميدبول: 4 × 15\n- بلانك ديناميكي: 3 × 45 ثانية\n- تمارين التنفس: 5 دقائق",
                'duration'        => 60,
                'difficulty'      => 'hard',
                'status'          => true,
                'user_id'         => $coaches[0]->id,
                'audience_gender' => 'all',
            ],
        ];

        $workouts = [];
        foreach ($workoutsData as $data) {
            $workout = Workout::firstOrCreate(
                ['name' => $data['name'], 'user_id' => $data['user_id']],
                $data
            );
            $workouts[] = $workout;
        }

        $this->command->info('  Workouts seeded: ' . count($workouts));
        return $workouts;
    }

    // ─────────────────────────────────────────
    // Workout Schedules
    // ─────────────────────────────────────────

    private function seedWorkoutSchedules(array $workouts, array $coaches): void
    {
        if (!Schema::hasTable('workout_schedules') || empty($workouts)) {
            return;
        }

        // برنامج مبتدئين - 4 أسابيع
        $scheduleData = [
            // الأسبوع 1
            ['workout' => 0, 'week' => 1, 'session' => 1, 'notes' => 'ركز على الشكل الصحيح', 'coach' => $coaches[0]],
            ['workout' => 1, 'week' => 1, 'session' => 2, 'notes' => 'استرح 90 ثانية بين الجولات', 'coach' => $coaches[0]],
            ['workout' => 2, 'week' => 1, 'session' => 3, 'notes' => 'شدة 60% فقط في البداية', 'coach' => $coaches[0]],
            // الأسبوع 2
            ['workout' => 0, 'week' => 2, 'session' => 1, 'notes' => 'زد الوزن بـ 5% إن استطعت', 'coach' => $coaches[0]],
            ['workout' => 1, 'week' => 2, 'session' => 2, 'notes' => 'نزّل وقت الراحة لـ 75 ثانية', 'coach' => $coaches[0]],
            ['workout' => 5, 'week' => 2, 'session' => 3, 'notes' => 'استهدف نبض القلب 70-80%', 'coach' => $coaches[0]],
            // برنامج السيدات - 3 أسابيع
            ['workout' => 3, 'week' => 1, 'session' => 1, 'notes' => 'تنفسي ببطء وتأمل', 'coach' => $coaches[1]],
            ['workout' => 4, 'week' => 1, 'session' => 2, 'notes' => 'تحسسي كل حركة', 'coach' => $coaches[1]],
            ['workout' => 3, 'week' => 2, 'session' => 1, 'notes' => 'امتدي أكثر اليوم', 'coach' => $coaches[1]],
            ['workout' => 4, 'week' => 2, 'session' => 2, 'notes' => 'أضيفي سيت إضافياً', 'coach' => $coaches[1]],
        ];

        $count = 0;
        foreach ($scheduleData as $sd) {
            $workout = $workouts[$sd['workout']] ?? null;
            if (!$workout) {
                continue;
            }

            WorkoutSchedule::firstOrCreate(
                [
                    'workout_id'     => $workout->id,
                    'week_number'    => $sd['week'],
                    'session_number' => $sd['session'],
                    'user_id'        => $sd['coach']->id,
                ],
                [
                    'notes'  => $sd['notes'],
                    'status' => true,
                ]
            );
            $count++;
        }

        $this->command->info("  Workout schedules seeded: {$count}");
    }

    // ─────────────────────────────────────────
    // Meal Plans
    // ─────────────────────────────────────────

    private function seedMealPlans(array $coaches): void
    {
        if (!Schema::hasTable('meal_plans')) {
            return;
        }

        $mealPlans = [
            [
                'name'         => 'وجبة إفطار بروتين عالي',
                'description'  => 'إفطار غني بالبروتين لبناء العضلات ومنح الطاقة طوال الصباح.',
                'meal_type'    => 'breakfast',
                'calories'     => 550,
                'protein'      => 45,
                'carbs'        => 40,
                'fats'         => 15,
                'ingredients'  => "4 بيضات كاملة\n100 غرام دجاج مشوي\n30 غرام شوفان\n100 مل حليب قليل الدسم\nموزة واحدة\nملعقة زبدة فول سوداني",
                'instructions' => "1. اطهِ البيض بالطريقة المفضلة\n2. سخّن الدجاج المشوي\n3. اطهِ الشوفان بالحليب\n4. اقدمها معاً مع الموز وزبدة الفول السوداني",
                'prep_time'    => 10,
                'cook_time'    => 15,
                'servings'     => 1,
                'difficulty'   => 'easy',
                'is_active'    => true,
                'user_id'      => $coaches[0]->id,
                'audience_gender' => 'male',
            ],
            [
                'name'         => 'غداء بناء الكتلة - دجاج وأرز',
                'description'  => 'وجبة غداء كلاسيكية لبناء العضلة مع توازن ممتاز بين الكربوهيدرات والبروتين.',
                'meal_type'    => 'lunch',
                'calories'     => 750,
                'protein'      => 55,
                'carbs'        => 85,
                'fats'         => 18,
                'ingredients'  => "200 غرام صدر دجاج\n150 غرام أرز أبيض أو بني\n200 غرام خضار مشكلة (بروكلي، جزر، فلفل)\nملعقتان زيت زيتون\nثوم، بهارات",
                'instructions' => "1. تبّل الدجاج بالثوم والبهارات\n2. اشوِ الدجاج 25 دقيقة على 200°\n3. اطهِ الأرز بماء مملح\n4. اطهِ الخضار بالبخار\n5. اقدم الكل معاً",
                'prep_time'    => 15,
                'cook_time'    => 30,
                'servings'     => 1,
                'difficulty'   => 'easy',
                'is_active'    => true,
                'user_id'      => $coaches[0]->id,
                'audience_gender' => 'all',
            ],
            [
                'name'         => 'عشاء خفيف - سلطة التونة',
                'description'  => 'عشاء خفيف وسريع وغني بالبروتين لتعزيز انتعاش العضلات أثناء النوم.',
                'meal_type'    => 'dinner',
                'calories'     => 380,
                'protein'      => 40,
                'carbs'        => 20,
                'fats'         => 14,
                'ingredients'  => "1 علبة تونة في الماء (150 غرام)\nخضار خضراء متنوعة (خس، طماطم، خيار)\n2 بيض مسلوق\nملعقة زيت زيتون\nعصير ليمون\nملح وفلفل",
                'instructions' => "1. صفّي التونة جيداً\n2. اقطع الخضار وجهزي قاعدة السلطة\n3. أضيفي التونة والبيض المقطع\n4. تبّل بالزيت والليمون والملح",
                'prep_time'    => 10,
                'cook_time'    => 10,
                'servings'     => 1,
                'difficulty'   => 'easy',
                'is_active'    => true,
                'user_id'      => $coaches[0]->id,
                'audience_gender' => 'all',
            ],
            [
                'name'         => 'وجبة إفطار للسيدات - سموذي صحي',
                'description'  => 'سموذي منعش ومغذٍ لبدء يوم مثالي بكميات مناسبة للمرأة.',
                'meal_type'    => 'breakfast',
                'calories'     => 400,
                'protein'      => 30,
                'carbs'        => 45,
                'fats'         => 10,
                'ingredients'  => "1 كوب حليب لوز أو كوكونت\n1 حصة بروتين فانيلا\nحفنة سبانخ\nموزة مجمدة\n100 غرام توت مجمد\nملعقة بذور شيا",
                'instructions' => "1. أضيفي جميع المكونات للخلاط\n2. اخلطي لمدة دقيقة حتى تحصلي على قوام ناعم\n3. قدميه فوراً مع بذور الشيا فوقه",
                'prep_time'    => 5,
                'cook_time'    => 0,
                'servings'     => 1,
                'difficulty'   => 'easy',
                'is_active'    => true,
                'user_id'      => $coaches[1]->id,
                'audience_gender' => 'female',
            ],
            [
                'name'         => 'غداء تنسيق للسيدات - سلمون وخضار',
                'description'  => 'وجبة غنية بأوميغا-3 ومضادات الأكسدة لدعم صحة الجلد والجسم والتنسيق.',
                'meal_type'    => 'lunch',
                'calories'     => 520,
                'protein'      => 42,
                'carbs'        => 30,
                'fats'         => 22,
                'ingredients'  => "180 غرام سمكة سلمون\n200 غرام خضار مشوية (كوسا، فلفل، طماطم)\n100 غرام بطاطا حلوة\nملعقتان زيت زيتون\nثوم وأعشاب عطرية",
                'instructions' => "1. تبّل السلمون بالثوم والزيتون\n2. اشوِ السلمون 15 دقيقة على 190°\n3. اشوِ الخضار والبطاطا الحلوة\n4. قدم الكل مع رشة أعشاب",
                'prep_time'    => 15,
                'cook_time'    => 25,
                'servings'     => 1,
                'difficulty'   => 'medium',
                'is_active'    => true,
                'user_id'      => $coaches[1]->id,
                'audience_gender' => 'female',
            ],
            [
                'name'         => 'وجبة ما بعد التمرين - شيك الإنعاش',
                'description'  => 'وجبة سريعة الامتصاص لتعويض الجليكوجين وإعادة بناء العضلات فور انتهاء التمرين.',
                'meal_type'    => 'snack',
                'calories'     => 350,
                'protein'      => 35,
                'carbs'        => 45,
                'fats'         => 5,
                'ingredients'  => "1 حصة بروتين واي شوكولاتة\n1 كوب حليب خالي الدسم\nموزة كبيرة\nملعقة عسل",
                'instructions' => "1. اخلط البروتين مع الحليب\n2. أضف الموز والعسل\n3. اخلط جيداً واشرب خلال 30 دقيقة من انتهاء التمرين",
                'prep_time'    => 3,
                'cook_time'    => 0,
                'servings'     => 1,
                'difficulty'   => 'easy',
                'is_active'    => true,
                'user_id'      => $coaches[0]->id,
                'audience_gender' => 'all',
            ],
        ];

        $count = 0;
        foreach ($mealPlans as $data) {
            MealPlan::firstOrCreate(
                ['name' => $data['name'], 'user_id' => $data['user_id']],
                $data
            );
            $count++;
        }

        $this->command->info("  Meal plans seeded: {$count}");
    }

    // ─────────────────────────────────────────
    // Testimonials
    // ─────────────────────────────────────────

    private function seedTestimonials(array $clients, User $admin): void
    {
        if (!Schema::hasTable('testimonials')) {
            return;
        }

        $testimonials = [
            [
                'name'          => 'محمد علي الشهري',
                'story_content' => 'في 3 أشهر فقط مع المدرب أحمد، استطعت بناء 2.5 كغ من العضل النقي! البرامج احترافية والمتابعة يومية. أنصح كل من يريد نتائج حقيقية.',
                'is_visible'    => true,
                'sort_order'    => 1,
                'user_id'       => $admin->id,
            ],
            [
                'name'          => 'ريم الحمادي',
                'story_content' => 'وصلت لوزني المستهدف بعد 3 أشهر من الانضباط مع المدربة نورا. ليس فقط الوزن، بل أسلوب حياة كامل تغير! التغذية والتمارين والراحة النفسية.',
                'is_visible'    => true,
                'sort_order'    => 2,
                'user_id'       => $admin->id,
            ],
            [
                'name'          => 'سارة المطيري',
                'story_content' => 'كنت أعتقد أن الكارديو وحده يكفي، لكن مع المدربة نورا اكتشفت مزيجاً رائعاً من اليوغا والكارديو والتغذية. خسرت 3.5 كغ في شهرين فقط!',
                'is_visible'    => true,
                'sort_order'    => 3,
                'user_id'       => $admin->id,
            ],
            [
                'name'          => 'خالد الزهراني',
                'story_content' => 'عدت للرياضة بعد 5 سنوات توقف بسبب السكري. المدرب أحمد صمم برنامجاً خاصاً يراعي حالتي الصحية. الآن أنا في أفضل حال منذ سنوات.',
                'is_visible'    => true,
                'sort_order'    => 4,
                'user_id'       => $admin->id,
            ],
            [
                'name'          => 'نادية العمري',
                'story_content' => 'الاشتراك السنوي كان أفضل قرار اتخذته. الاستمرارية هي السر. مع متابعة المدربة نورا كل أسبوع، أصبحت اللياقة جزءاً من حياتي اليومية.',
                'is_visible'    => true,
                'sort_order'    => 5,
                'user_id'       => $admin->id,
            ],
        ];

        $count = 0;
        foreach ($testimonials as $data) {
            Testimonial::firstOrCreate(
                ['name' => $data['name']],
                $data
            );
            $count++;
        }

        $this->command->info("  Testimonials seeded: {$count}");
    }

    // ─────────────────────────────────────────
    // Supplement Plans
    // ─────────────────────────────────────────

    private function seedSupplementPlans(array $coaches): void
    {
        if (! Schema::hasTable('supplement_plans')) {
            $this->command->warn('  ⚠ supplement_plans table missing — skipping.');
            return;
        }

        $coach = $coaches[0];

        $plans = [
            [
                'name'             => 'بروتين واي الذهبي',
                'supplement_type'  => 'protein',
                'dosage'           => '30 غرام مع 300 مل ماء أو حليب',
                'timing'           => 'post_workout',
                'brand'            => 'Optimum Nutrition',
                'description'      => 'بروتين واي عالي الجودة لبناء العضلات وتسريع التعافي بعد التمرين. يحتوي على 24 غرام بروتين لكل حصة.',
                'instructions'     => "اخلط كبسولة واحدة مع 300 مل ماء بارد أو حليب\nاستهلكه خلال 30 دقيقة من انتهاء التمرين\nيمكن استخدامه أيضاً كوجبة خفيفة بين الوجبات الرئيسية",
                'warnings'         => 'أشخاص الحساسية من منتجات الألبان يجب استشارة الطبيب قبل الاستخدام. لا يُستخدم كبديل عن الوجبات الرئيسية.',
                'is_active'        => true,
                'audience_gender'  => 'male',
                'required_membership_types' => [],
                'sort_order'       => 1,
                'user_id'          => $coach->id,
            ],
            [
                'name'             => 'كرياتين مونوهيدرات',
                'supplement_type'  => 'pre_workout',
                'dosage'           => '5 غرام يومياً',
                'timing'           => 'pre_workout',
                'brand'            => 'Creatine Monohydrate',
                'description'      => 'كرياتين نقي 100% لزيادة القوة والطاقة خلال التمارين عالية الكثافة. يساعد على تحسين الأداء الرياضي وزيادة كتلة العضلات.',
                'instructions'     => "خذ 5 غرام (ملعقة واحدة صغيرة) مع كوب ماء\nيُفضل تناوله قبل التمرين بـ 30 دقيقة\nفي أيام الراحة يمكن تناوله في أي وقت",
                'warnings'         => 'تأكد من شرب كميات كافية من الماء (2-3 لتر يومياً) عند استخدام الكرياتين. استشر طبيبك في حالة وجود مشاكل في الكلى.',
                'is_active'        => true,
                'audience_gender'  => 'male',
                'required_membership_types' => [],
                'sort_order'       => 2,
                'user_id'          => $coach->id,
            ],
            [
                'name'             => 'فيتامينات متعددة يومية',
                'supplement_type'  => 'vitamins',
                'dosage'           => 'قرص واحد يومياً',
                'timing'           => 'with_meal',
                'brand'            => 'GNC Mega Men',
                'description'      => 'مجمع فيتامينات ومعادن متكامل مصمم خصيصاً للرياضيين. يحتوي على أكثر من 30 عنصراً غذائياً أساسياً لدعم الصحة العامة.',
                'instructions'     => "تناول قرصاً واحداً يومياً مع وجبة الإفطار أو الغداء\nلا تتجاوز الجرعة الموصى بها",
                'warnings'         => null,
                'is_active'        => true,
                'audience_gender'  => 'all',
                'required_membership_types' => [],
                'sort_order'       => 3,
                'user_id'          => $coach->id,
            ],
            [
                'name'             => 'أوميغا 3 للقلب والمفاصل',
                'supplement_type'  => 'omega',
                'dosage'           => 'كبسولتان يومياً (1000 مغ لكل كبسولة)',
                'timing'           => 'with_meal',
                'brand'            => 'Omega-3 Fish Oil',
                'description'      => 'زيت السمك الغني بأحماض أوميغا 3 الدهنية (EPA وDHA). يدعم صحة القلب والأوعية الدموية ويقلل التهاب المفاصل ويسرع التعافي.',
                'instructions'     => "تناول كبسولتين مع وجبة دسمة (غداء أو عشاء) لتحسين الامتصاص\nيُنصح بالاستمرار لمدة 3 أشهر على الأقل لرؤية النتائج",
                'warnings'         => 'إذا كنت تتناول أدوية مضادة للتخثر، استشر طبيبك قبل الاستخدام.',
                'is_active'        => true,
                'audience_gender'  => 'all',
                'required_membership_types' => [],
                'sort_order'       => 4,
                'user_id'          => $coach->id,
            ],
            [
                'name'             => 'BCAA للتعافي السريع',
                'supplement_type'  => 'post_workout',
                'dosage'           => '10 غرام (2 ملعقة كبيرة)',
                'timing'           => 'post_workout',
                'brand'            => 'Xtend BCAAs',
                'description'      => 'أحماض أمينية متشعبة السلسلة (Leucine, Isoleucine, Valine) بنسبة 2:1:1 لتقليل تكسر العضلات وتسريع التعافي.',
                'instructions'     => "اخلط الجرعة مع 400 مل ماء بارد\nيمكن استخدامه خلال التمرين وبعده مباشرة\nآمن للاستخدام اليومي حتى في أيام الراحة",
                'warnings'         => null,
                'is_active'        => true,
                'audience_gender'  => 'male',
                'required_membership_types' => [],
                'sort_order'       => 5,
                'user_id'          => $coach->id,
            ],
        ];

        $count = 0;
        foreach ($plans as $data) {
            SupplementPlan::firstOrCreate(
                ['name' => $data['name'], 'user_id' => $data['user_id']],
                $data
            );
            $count++;
        }

        $this->command->info("  Supplement Plans seeded: {$count}");
    }

    // ─────────────────────────────────────────
    // Client Journey Pages
    // ─────────────────────────────────────────

    private function seedClientJourneyPages(User $coach): void
    {
        if (! Schema::hasTable('pages')) {
            $this->command->warn('  ⚠ pages table missing — skipping.');
            return;
        }

        $pages = [
            [
                'title'        => 'برنامجك الشهري — نصائح وإرشادات المدرب',
                'slug'         => 'monthly-program-coach-tips',
                'content'      => '<h2>أهلاً بك في برنامجك الشهري</h2>
<p>هذه الصفحة خاصة بك وتحتوي على توجيهات المدرب الشخصية لهذا الشهر.</p>

<h3>أهداف الشهر</h3>
<ul>
<li>زيادة الكتلة العضلية بمقدار 0.5-1 كغ</li>
<li>تحسين القوة في تمرين الضغط والسحب</li>
<li>الالتزام بـ 4 جلسات تدريب أسبوعياً</li>
</ul>

<h3>نصائح التغذية لهذا الشهر</h3>
<p>تأكد من تناول وجبة غنية بالبروتين خلال 30 دقيقة من انتهاء التمرين. الهدف اليومي هو 1.8-2 غرام من البروتين لكل كيلوغرام من وزن الجسم.</p>

<h3>جدول التمرين الأسبوعي الموصى به</h3>
<ul>
<li><strong>الأحد:</strong> تمارين الصدر والكتفين</li>
<li><strong>الاثنين:</strong> تمارين الظهر والبايسبس</li>
<li><strong>الأربعاء:</strong> تمارين الأرجل والبطن</li>
<li><strong>الجمعة:</strong> تمرين HIIT + كارديو</li>
</ul>

<h3>قياسات هذا الشهر</h3>
<p>يرجى قياس وزنك كل أسبوع في نفس الوقت (الصباح قبل الإفطار) وتسجيله في تطبيق المتابعة.</p>',
                'access_level' => 'membership',
                'is_published' => true,
                'published_at' => now(),
                'user_id'      => $coach->id,
                'audience_gender' => 'male',
                'required_membership_types' => [],
                'menu_order'   => 1,
            ],
            [
                'title'        => 'دليل التغذية للمبتدئين',
                'slug'         => 'nutrition-guide-beginners',
                'content'      => '<h2>دليلك الشامل للتغذية الرياضية</h2>
<p>التغذية السليمة هي 70% من نجاح رحلة اللياقة البدنية. اتبع هذه الإرشادات للحصول على أفضل النتائج.</p>

<h3>المبادئ الأساسية</h3>
<ul>
<li><strong>البروتين:</strong> 1.6-2.2 غرام لكل كغ من وزن الجسم يومياً</li>
<li><strong>الكربوهيدرات:</strong> 4-6 غرام لكل كغ (مصدر الطاقة الرئيسي)</li>
<li><strong>الدهون الصحية:</strong> 0.8-1.2 غرام لكل كغ</li>
<li><strong>الماء:</strong> 3-4 لتر يومياً على الأقل</li>
</ul>

<h3>أفضل مصادر البروتين</h3>
<ul>
<li>صدر الدجاج المشوي (31 غرام بروتين/100 غرام)</li>
<li>التونة بالماء (26 غرام بروتين/100 غرام)</li>
<li>البيض (6 غرام بروتين/بيضة)</li>
<li>الزبادي اليوناني (10 غرام بروتين/100 غرام)</li>
</ul>

<h3>ما يجب تجنبه</h3>
<ul>
<li>الوجبات السريعة والمقليات</li>
<li>المشروبات الغازية والعصائر المصنعة</li>
<li>الحلويات والسكريات المضافة</li>
</ul>',
                'access_level' => 'public',
                'is_published' => true,
                'published_at' => now(),
                'user_id'      => $coach->id,
                'audience_gender' => 'all',
                'required_membership_types' => [],
                'menu_order'   => 2,
            ],
            [
                'title'        => 'برنامج التعافي والنوم',
                'slug'         => 'recovery-sleep-program',
                'content'      => '<h2>التعافي: الجزء المنسي من التدريب</h2>
<p>كثير من الرياضيين يهتمون بالتمرين والتغذية لكن يهملون التعافي. النوم الجيد والراحة الكافية ضروريان مثل التمرين تماماً.</p>

<h3>أهمية النوم للرياضيين</h3>
<ul>
<li>إفراز هرمون النمو يحدث بشكل رئيسي أثناء النوم</li>
<li>إصلاح وبناء الأنسجة العضلية يتم أثناء الراحة</li>
<li>تحسين الأداء الرياضي والتركيز</li>
<li>تقليل خطر الإصابات</li>
</ul>

<h3>نصائح لنوم أفضل</h3>
<ul>
<li>نم 7-9 ساعات يومياً</li>
<li>حافظ على مواعيد نوم منتظمة</li>
<li>تجنب الشاشات قبل النوم بساعة</li>
<li>اجعل غرفة النوم باردة ومظلمة وهادئة</li>
<li>تجنب الكافيين بعد الساعة 3 عصراً</li>
</ul>

<h3>تمارين الإطالة بعد التدريب</h3>
<p>خصص 10-15 دقيقة لتمارين الإطالة بعد كل جلسة تدريب. هذا يساعد على تسريع التعافي وتقليل ألم العضلات.</p>',
                'access_level' => 'membership',
                'is_published' => true,
                'published_at' => now(),
                'user_id'      => $coach->id,
                'audience_gender' => 'all',
                'required_membership_types' => [],
                'menu_order'   => 3,
            ],
        ];

        $count = 0;
        foreach ($pages as $data) {
            Page::firstOrCreate(
                ['slug' => $data['slug']],
                $data
            );
            $count++;
        }

        $this->command->info("  Client Journey Pages seeded: {$count}");
    }
}
