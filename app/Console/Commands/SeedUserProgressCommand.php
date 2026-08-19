<?php

namespace App\Console\Commands;

use App\Models\ChallengeParticipant;
use App\Models\ClientProfile;
use App\Models\Habit;
use App\Models\HabitLog;
use App\Models\ProgressCheckIn;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WeeklyChallenge;
use App\Models\Workout;
use App\Models\WorkoutLog;
use App\Models\WorkoutSchedule;
use App\Services\ClientHomeService;
use App\Services\TenantService;
use App\Services\WorkoutScheduleService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class SeedUserProgressCommand extends Command
{
    protected $signature = 'client:seed-progress
                            {user_id=5 : User ID inside the tenant DB}
                            {--domain=app3.cmsglobals.test : Tenant domain}';

    protected $description = 'Seed home progress_overview demo data for a specific trainee user';

    public function handle(WorkoutScheduleService $scheduleService, ClientHomeService $homeService): int
    {
        $domain = (string) $this->option('domain');
        $userId = (int) $this->argument('user_id');

        $tenant = Tenant::on('system')->where('domain', $domain)->first();
        if (! $tenant) {
            $this->error("Tenant not found: {$domain}");

            return self::FAILURE;
        }

        TenantService::switchToTenant($tenant);
        $this->info("Tenant: {$tenant->name} ({$domain})");

        $user = User::query()->find($userId);
        if (! $user) {
            $this->error("User #{$userId} not found in tenant DB.");

            return self::FAILURE;
        }

        $this->line("User: #{$user->id} | {$user->name} | {$user->email}");

        if (! $user->hasTraineeRole()) {
            $user->assignRole('user');
            $this->warn('Assigned role: user');
        }

        $coach = null;
        if ($user->coach_id) {
            $coach = User::query()->find($user->coach_id);
        }
        if (! $coach) {
            $coach = User::query()->role('coach')->first();
        }
        if (! $coach) {
            $coach = User::query()->firstOrCreate(
                ['email' => 'ahmad.coach@app1.local'],
                [
                    'name' => 'أحمد المدرب',
                    'password' => Hash::make('password'),
                ]
            );
            if (method_exists($coach, 'assignRole') && ! $coach->hasRole('coach')) {
                $coach->assignRole('coach');
            }
        }

        if (! $user->coach_id) {
            $user->update(['coach_id' => $coach->id]);
        }

        $this->seedProgramWeek($user, 3);
        $schedules = $this->seedWeekSchedules($coach, 3);
        $this->seedWorkoutLogs($user, $schedules, $scheduleService);
        $this->seedHabits($user, $coach);
        $this->seedCheckIn($user, $coach);
        $this->seedCoachMessage($user, $coach);
        $this->seedNutrition($user, $coach);
        $this->seedCommunity($user, $coach);
        $this->seedBookings($user, $coach);

        $payload = $homeService->payloadFor($user->fresh());
        $overview = $payload['progress_overview'] ?? [];
        $todayWorkouts = $payload['today_workouts'] ?? [];
        $coachMessage = $payload['coach_message'] ?? null;
        $nutritionCard = $payload['nutrition_card'] ?? null;
        $challengesCard = $payload['challenges_card'] ?? null;
        $communityCard = $payload['community_card'] ?? null;
        $this->newLine();
        $this->info('✅ progress_overview for user #'.$user->id.':');
        $this->line(json_encode($overview, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        $this->newLine();
        $this->info('✅ today_workouts ('.count($todayWorkouts).'):');
        $this->line(json_encode(
            collect($todayWorkouts)->map(fn ($item) => [
                'workout_schedule_id' => $item['schedule']->id,
                'name' => $item['workout']?->name,
                'meta_line' => ($item['workout']?->duration ?? '').' دقيقة • '.($item['workout']?->difficulty_name ?? ''),
                'is_completed' => (bool) $item['is_completed'],
            ]),
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        ));
        $this->newLine();
        $this->info('✅ coach_message:');
        $this->line(json_encode($coachMessage, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        $this->newLine();
        $this->info('✅ nutrition_card:');
        $this->line(json_encode($nutritionCard, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        $this->newLine();
        $this->info('✅ challenges_card:');
        $this->line(json_encode($challengesCard, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        $this->newLine();
        $this->info('✅ community_card:');
        $this->line(json_encode($communityCard, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        $this->newLine();
        $this->line("Login: {$user->email} / password");
        $this->line('API: GET /api/v1/client/home  → community_card');
        $this->line('API: GET /api/v1/community/posts');
        $this->line('API: POST /api/v1/community/posts');

        return self::SUCCESS;
    }

    private function seedProgramWeek(User $user, int $week): void
    {
        if (! Schema::hasTable('client_profiles')) {
            return;
        }

        ClientProfile::updateOrCreate(
            ['user_id' => $user->id],
            ['current_program_week' => $week]
        );

        $this->info("  program_week = {$week}");
    }

    /**
     * @return array<int, WorkoutSchedule>
     */
    private function seedWeekSchedules(User $coach, int $week): array
    {
        if (! Schema::hasTable('workouts') || ! Schema::hasTable('workout_schedules')) {
            return [];
        }

        $upperBody = Workout::updateOrCreate(
            ['name' => 'Upper Body', 'user_id' => $coach->id],
            [
                'description' => 'يركز هذا التمرين على عضلات الجزء العلوي: الصدر، الأكتاف، الظهر والذراعين باستخدام وزن الجسم والأوزان الحرة.',
                'duration' => 45,
                'exercise_count' => 6,
                'equipment_label' => 'معدات',
                'difficulty' => 'medium',
                'video_url' => 'https://www.youtube.com/watch?v=IODxDxX7oi4',
                'video_duration_seconds' => 765,
                'coach_notes' => [
                    'ركز على التقنية الصحيحة قبل زيادة الأوزان.',
                    'خذ راحة 60–90 ثانية بين المجموعات.',
                    'حافظ على التنفس المنتظم طوال التمرين.',
                ],
                'status' => true,
                'audience_gender' => 'all',
            ]
        );

        $lowerBody = Workout::updateOrCreate(
            ['name' => 'Lower Body', 'user_id' => $coach->id],
            [
                'description' => 'تمرين الجزء السفلي يركز على الأرجل والأرداف والثبات باستخدام القرفصاء والطعنات وتمارين الجسم.',
                'duration' => 40,
                'exercise_count' => 5,
                'equipment_label' => 'معدات',
                'difficulty' => 'medium',
                'video_url' => 'https://www.youtube.com/watch?v=2pLT-olgUJs',
                'video_duration_seconds' => 620,
                'coach_notes' => [
                    'حافظ على استقامة الظهر أثناء القرفصاء.',
                    'لا تمدّ الركبة أبعد من أصابع القدم.',
                    'خذ راحة 60 ثانية بين المجموعات.',
                ],
                'status' => true,
                'audience_gender' => 'all',
            ]
        );

        $fullBodyHiit = Workout::updateOrCreate(
            ['name' => 'Full Body HIIT', 'user_id' => $coach->id],
            [
                'description' => 'تمرين كامل للجسم بأسلوب HIIT لرفع اللياقة وحرق الدهون، مع متابعة الفيديو خطوة بخطوة.',
                'duration' => 30,
                'exercise_count' => 8,
                'equipment_label' => 'بدون معدات',
                'difficulty' => 'medium',
                'video_url' => 'https://youtu.be/RfUBfR7p8as?si=8JOFdjne5IGjkClz',
                'video_duration_seconds' => 900,
                'coach_notes' => [
                    'سخّن جسمك دقيقتين قبل البدء.',
                    'حافظ على إيقاع ثابت خلال الجولات.',
                    'توقف فوراً عند أي ألم حاد.',
                ],
                'status' => true,
                'audience_gender' => 'all',
            ]
        );

        // 6 أيام تمارين؛ نترك جلسة اليوم ناقصة حتى يظهر زر «ابدأ التمرين».
        $sessions = [1, 2, 3, 4, 5, 6];
        $schedules = [];

        WorkoutSchedule::query()
            ->where('week_number', $week)
            ->where(function ($q) {
                $q->where('notes', 'جدول تحقق بطاقة التقدم')
                    ->orWhere('notes', 'تمرين اليوم — Upper Body')
                    ->orWhere('notes', 'تمرين اليوم — Lower Body')
                    ->orWhere('notes', 'تمرين اليوم — Full Body HIIT')
                    ->orWhere('notes', 'تمرين اليوم — Wall Push-ups');
            })
            ->delete();

        // أزل أي جداول متعارضة لنفس التمارين/الأسبوع (قيد unique على workout+week+session).
        WorkoutSchedule::query()
            ->where('week_number', $week)
            ->whereIn('workout_id', [$upperBody->id, $lowerBody->id, $fullBodyHiit->id])
            ->delete();

        // أخفِ جداول الأسبوع الأخرى حتى تظهر بطاقات التحقق فقط.
        WorkoutSchedule::query()
            ->where('week_number', $week)
            ->where('status', true)
            ->update(['status' => false]);

        foreach ($sessions as $session) {
            $schedules[$session] = WorkoutSchedule::create([
                'workout_id' => $upperBody->id,
                'week_number' => $week,
                'session_number' => $session,
                'user_id' => $coach->id,
                'status' => true,
                'notes' => 'تمرين اليوم — Upper Body',
                'audience_gender' => 'all',
            ]);
        }

        // تمارين إضافية لجلسة اليوم فقط → تظهر معاً في today_workouts.
        $todaySession = app(WorkoutScheduleService::class)->sessionNumberForDate(now());
        $schedules['today_extra_lower'] = WorkoutSchedule::create([
            'workout_id' => $lowerBody->id,
            'week_number' => $week,
            'session_number' => $todaySession,
            'user_id' => $coach->id,
            'status' => true,
            'notes' => 'تمرين اليوم — Lower Body',
            'audience_gender' => 'all',
        ]);
        $schedules['today_extra_hiit'] = WorkoutSchedule::create([
            'workout_id' => $fullBodyHiit->id,
            'week_number' => $week,
            'session_number' => $todaySession,
            'user_id' => $coach->id,
            'status' => true,
            'notes' => 'تمرين اليوم — Full Body HIIT',
            'audience_gender' => 'all',
        ]);

        $wallPushups = Workout::updateOrCreate(
            ['name' => 'Wall Push-ups', 'user_id' => $coach->id],
            [
                'description' => 'تمرين ضغط الحائط لتقوية الصدر والذراعين والكتفين بطريقة مناسبة للمبتدئين، مع متابعة الصورة المتحركة للتقنية الصحيحة.',
                'duration' => 15,
                'exercise_count' => 1,
                'equipment_label' => 'بدون معدات',
                'difficulty' => 'easy',
                'video_url' => null,
                'image' => '/media/workouts/wall-pushups.png',
                'coach_notes' => [
                    'ضع يديك على الحائط بعرض الكتفين.',
                    'اخفض جسمك ببطء ثم ادفع للعودة.',
                    'حافظ على استقامة الجسم طوال الحركة.',
                ],
                'status' => true,
                'audience_gender' => 'all',
            ]
        );

        WorkoutSchedule::query()
            ->where('week_number', $week)
            ->where('workout_id', $wallPushups->id)
            ->delete();

        $schedules['today_extra_wall'] = WorkoutSchedule::create([
            'workout_id' => $wallPushups->id,
            'week_number' => $week,
            'session_number' => $todaySession,
            'user_id' => $coach->id,
            'status' => true,
            'notes' => 'تمرين اليوم — Wall Push-ups',
            'audience_gender' => 'all',
        ]);

        $this->info('  workout schedules: week '.$week.' Upper Body (1-6) + Lower/HIIT/Wall Push-ups (today session '.$todaySession.')');

        return $schedules;
    }

    /**
     * @param  array<int|string, WorkoutSchedule>  $schedules
     */
    private function seedWorkoutLogs(User $user, array $schedules, WorkoutScheduleService $scheduleService): void
    {
        if (! Schema::hasTable('workout_logs') || $schedules === []) {
            return;
        }

        $today = now();
        $todaySession = $scheduleService->sessionNumberForDate($today);
        $weekStart = $today->copy()->startOfDay()->subDays($todaySession - 1);

        foreach ($schedules as $schedule) {
            WorkoutLog::query()
                ->where('user_id', $user->id)
                ->where('workout_schedule_id', $schedule->id)
                ->whereBetween('scheduled_on', [
                    $weekStart->toDateString(),
                    $weekStart->copy()->addDays(6)->toDateString(),
                ])
                ->delete();
        }

        // أكمل جلسات Upper Body ما عدا اليوم؛ اترك Lower Body مفتوحاً أيضاً.
        $count = 0;

        foreach ($schedules as $key => $schedule) {
            if (! is_int($key) || $key === $todaySession) {
                continue;
            }

            $date = $weekStart->copy()->addDays($key - 1);

            WorkoutLog::create([
                'user_id' => $user->id,
                'workout_schedule_id' => $schedule->id,
                'scheduled_on' => $date->toDateString(),
                'workout_id' => $schedule->workout_id,
                'status' => 'completed',
                'completed_at' => $date->copy()->setTime(18, 0),
                'notes' => 'مكتمل — بيانات تحقق التقدم',
            ]);
            $count++;
        }

        $this->info("  workout logs written: {$count} (today session {$todaySession} left open for Upper Body + Lower Body)");
    }

    private function seedHabits(User $user, User $coach): void
    {
        if (! Schema::hasTable('habits')) {
            return;
        }

        Habit::query()
            ->where('client_user_id', $user->id)
            ->update(['is_active' => false]);

        // بيانات تحقق محدثة — قيم واضحة مختلفة عن النسخة السابقة.
        $defs = [
            ['name' => 'شرب 3 لتر ماء', 'unit' => 'لتر', 'target' => 3, 'today' => true, 'miss' => [5, 6]],
            ['name' => '8,000 خطوة', 'unit' => 'خطوة', 'target' => 8000, 'today' => false, 'miss' => [2, 3, 4, 5, 6]],
            ['name' => 'وجبات صحية', 'unit' => 'وجبة', 'target' => 3, 'today' => false, 'miss' => [3, 4, 5, 6]],
            ['name' => 'قراءة 20 صفحة', 'unit' => 'صفحة', 'target' => 20, 'today' => false, 'miss' => [1, 2, 3, 4, 5, 6]],
            ['name' => 'تمرين القوة', 'unit' => 'دقيقة', 'target' => 30, 'today' => false, 'miss' => [3, 4, 5, 6]],
        ];

        $weekStart = now()->copy()->startOfWeek(\Carbon\Carbon::SATURDAY)->startOfDay();

        foreach ($defs as $def) {
            $habit = Habit::updateOrCreate(
                [
                    'client_user_id' => $user->id,
                    'name' => $def['name'],
                ],
                [
                    'created_by_user_id' => $coach->id,
                    'unit' => $def['unit'],
                    'target_value' => $def['target'],
                    'is_active' => true,
                ]
            );

            if (! Schema::hasTable('habit_logs')) {
                continue;
            }

            HabitLog::query()
                ->where('habit_id', $habit->id)
                ->where('logged_on', '>=', now()->subDays(20)->toDateString())
                ->delete();

            for ($i = 0; $i < 7; $i++) {
                $date = $weekStart->copy()->addDays($i);
                $isToday = $date->isSameDay(now());
                $isCompleted = ! in_array($i, $def['miss'], true);

                if ($isToday) {
                    $isCompleted = (bool) $def['today'];
                }

                // سلسلة قصيرة = يومين فقط (اليوم + أمس) لعادة الماء.
                if ($def['name'] === 'شرب 3 لتر ماء') {
                    $isCompleted = $date->lte(now()->copy()->startOfDay())
                        && $date->gte(now()->copy()->subDay()->startOfDay());
                }

                if ($date->gt(now())) {
                    continue;
                }

                HabitLog::create([
                    'habit_id' => $habit->id,
                    'user_id' => $user->id,
                    'logged_on' => $date->toDateString(),
                    'value' => $isCompleted ? $def['target'] : 0,
                    'is_completed' => $isCompleted,
                ]);
            }
        }

        // عطّل الأسماء القديمة حتى لا تختلط مع بيانات التحقق الجديدة.
        Habit::query()
            ->where('client_user_id', $user->id)
            ->whereNotIn('name', array_column($defs, 'name'))
            ->update(['is_active' => false]);

        $this->seedHabitChallengeAndBadges($user);
        $this->info('  habits VERIFY set: water today only, streak~2, challenge 18/20, points~40');
    }

    private function seedHabitChallengeAndBadges(User $user): void
    {
        if (Schema::hasTable('weekly_challenges')) {
            WeeklyChallenge::query()->where('is_active', true)->update(['is_active' => false]);

            $challenge = WeeklyChallenge::updateOrCreate(
                ['title' => 'تحدي الأسبوع: 8000 خطوة'],
                [
                    'challenge_type' => 'steps',
                    'target_value' => 8000,
                    'starts_on' => now()->copy()->startOfWeek(\Carbon\Carbon::SATURDAY)->toDateString(),
                    'ends_on' => now()->copy()->startOfWeek(\Carbon\Carbon::SATURDAY)->addDays(6)->toDateString(),
                    'is_active' => true,
                ]
            );

            // عطّل أي تحديات أخرى نشطة غير هذا.
            WeeklyChallenge::query()
                ->where('id', '!=', $challenge->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            ChallengeParticipant::updateOrCreate(
                ['challenge_id' => $challenge->id, 'user_id' => $user->id],
                [
                    'progress_value' => 6000,
                    'is_completed' => false,
                ]
            );
        }

        if (Schema::hasTable('gamification_badges') && Schema::hasTable('user_badges')) {
            $gamification = app(\App\Services\GamificationService::class);
            $gamification->bootstrapBadges();

            // امنح شارات أوضح لشاشة التحديات (نقاط ≈ 190 أو أقرب للـ wireframe).
            \App\Models\UserBadge::query()->where('user_id', $user->id)->delete();

            $earnCodes = ['streak_5', 'inspiring', 'achiever'];
            foreach ($earnCodes as $code) {
                $badge = \App\Models\GamificationBadge::query()->where('code', $code)->first();
                if (! $badge) {
                    continue;
                }
                \App\Models\UserBadge::updateOrCreate(
                    ['user_id' => $user->id, 'badge_id' => $badge->id],
                    ['awarded_at' => now()->subDays(2), 'meta' => ['seeded' => true]]
                );
            }
        }
    }

    private function seedCheckIn(User $user, User $coach): void
    {
        if (! Schema::hasTable('progress_check_ins')) {
            return;
        }

        ProgressCheckIn::updateOrCreate(
            [
                'user_id' => $user->id,
                'checked_in_at' => now()->subDay()->setTime(9, 30),
            ],
            [
                'coach_id' => $coach->id,
                'submitted_by_user_id' => $user->id,
                'weight' => 78.5,
                'body_fat_percentage' => 18.0,
                'waist_cm' => 82,
                'energy_level' => 7,
                'training_adherence' => 7,
                'nutrition_adherence' => 7,
                'notes' => 'Check-in تجريبي لبطاقة تقدمك الكلي (user #'.$user->id.').',
            ]
        );

        $this->info('  check-in seeded (adherence 7/10)');
    }

    private function seedCoachMessage(User $user, User $coach): void
    {
        if (! Schema::hasTable('messages') || ! Schema::hasTable('conversations')) {
            return;
        }

        $firstName = explode(' ', trim((string) $user->name))[0] ?: 'صديقي';
        $body = "{$firstName}، استمر في التزامك! أنت تقدم أداءً رائعاً هذا الأسبوع 💪";

        $messaging = app(\App\Services\MessagingService::class);
        $conversation = $messaging->findOrCreateDirectConversation($coach, $user, 'رسالة المدرب');

        // حدّث آخر رسالة من المدرب إن وُجدت، وإلا أنشئ رسالة جديدة منذ ساعتين.
        $existing = \App\Models\Message::query()
            ->where('conversation_id', $conversation->id)
            ->where('sender_user_id', $coach->id)
            ->where('body', $body)
            ->latest('sent_at')
            ->first();

        if ($existing) {
            $existing->update(['sent_at' => now()->subHours(2)]);
        } else {
            \App\Models\Message::create([
                'conversation_id' => $conversation->id,
                'sender_user_id' => $coach->id,
                'body' => $body,
                'sent_at' => now()->subHours(2),
            ]);
            $conversation->update(['last_message_at' => now()->subHours(2)]);
        }

        $this->info('  coach_message seeded (2 hours ago)');
    }

    private function seedNutrition(User $user, User $coach): void
    {
        if (! Schema::hasTable('meal_plans')) {
            return;
        }

        $plans = [
            [
                'name' => 'إفطار بروتين',
                'meal_type' => 'breakfast',
                'description' => 'بيض + شوفان + فواكه لبداية يوم نشطة.',
                'calories' => 420,
                'protein' => 32,
                'carbs' => 40,
                'fats' => 14,
                'ingredients' => "2 بيض\nشوفان 40غ\nتوت أو موز\nلبن قليل الدسم",
                'instructions' => 'حضّر الشوفان ثم أضف البيض المسلوق والفاكهة.',
                'prep_time' => 10,
                'cook_time' => 5,
                'servings' => 1,
                'difficulty' => 'easy',
            ],
            [
                'name' => 'غداء متوازن 1800',
                'meal_type' => 'lunch',
                'description' => 'صدر دجاج مع أرز بني وسلطة خضار.',
                'calories' => 550,
                'protein' => 45,
                'carbs' => 55,
                'fats' => 12,
                'ingredients' => "صدر دجاج 150غ\nأرز بني 120غ مطبوخ\nسلطة خضراء\nزيت زيتون ملعقة صغيرة",
                'instructions' => 'اشوِ الدجاج وقدّمه مع الأرز والسلطة.',
                'prep_time' => 15,
                'cook_time' => 20,
                'servings' => 1,
                'difficulty' => 'medium',
            ],
            [
                'name' => 'عشاء خفيف',
                'meal_type' => 'dinner',
                'description' => 'سمك مشوي مع خضار مطهوة على البخار.',
                'calories' => 480,
                'protein' => 40,
                'carbs' => 25,
                'fats' => 18,
                'ingredients' => "سمك أبيض 160غ\nبروكلي وجزر\nليمون وتوابل",
                'instructions' => 'اشوِ السمك وقدّمه مع الخضار.',
                'prep_time' => 10,
                'cook_time' => 18,
                'servings' => 1,
                'difficulty' => 'easy',
            ],
            [
                'name' => 'سناك يوناني',
                'meal_type' => 'snack',
                'description' => 'زبادي يوناني مع مكسرات.',
                'calories' => 220,
                'protein' => 18,
                'carbs' => 12,
                'fats' => 10,
                'ingredients' => "زبادي يوناني 150غ\nلوز أو جوز 15غ",
                'instructions' => 'اخلط الزبادي مع المكسرات وقدّم فوراً.',
                'prep_time' => 3,
                'cook_time' => 0,
                'servings' => 1,
                'difficulty' => 'easy',
            ],
        ];

        $created = [];
        foreach ($plans as $plan) {
            $created[$plan['meal_type']] = \App\Models\MealPlan::updateOrCreate(
                ['name' => $plan['name'], 'user_id' => $coach->id],
                array_merge($plan, [
                    'is_active' => true,
                    'audience_gender' => 'all',
                ])
            );
        }

        if (Schema::hasTable('meal_logs')) {
            \App\Models\MealLog::query()
                ->where('user_id', $user->id)
                ->where('logged_on', '>=', now()->subDays(7)->toDateString())
                ->delete();

            // سجّل بعض الأيام السابقة لرفع الالتزام ≈ 75%.
            for ($d = 1; $d <= 5; $d++) {
                $date = now()->subDays($d)->toDateString();
                foreach (['breakfast', 'lunch', 'dinner'] as $slot) {
                    \App\Models\MealLog::create([
                        'user_id' => $user->id,
                        'meal_plan_id' => $created[$slot]->id ?? null,
                        'logged_on' => $date,
                        'meal_slot' => $slot,
                        'adherence_score' => $d === 1 ? 7 : 8,
                        'notes' => 'تسجيل تحقق خطة التغذية',
                    ]);
                }
            }

            // وجبة واحدة اليوم (إفطار) ويبقى الغداء/العشاء للتسجيل من التطبيق.
            \App\Models\MealLog::create([
                'user_id' => $user->id,
                'meal_plan_id' => $created['breakfast']->id ?? null,
                'logged_on' => now()->toDateString(),
                'meal_slot' => 'breakfast',
                'adherence_score' => 9,
                'notes' => 'إفطار اليوم — جاهز للتحقق',
            ]);
        }

        $this->info('  nutrition plans + meal logs seeded (~75% adherence)');
    }

    private function seedCommunity(User $user, User $coach): void
    {
        if (! Schema::hasTable('community_posts')) {
            return;
        }

        // منشور تحقق من المدرب
        $coachPost = \App\Models\CommunityPost::updateOrCreate(
            [
                'user_id' => $coach->id,
                'content' => 'مرحباً بالجميع! شاركونا إنجازات هذا الأسبوع 💪',
            ],
            [
                'is_visible' => true,
                'created_at' => now()->subHours(5),
                'updated_at' => now()->subHours(5),
            ]
        );

        $traineePost = \App\Models\CommunityPost::updateOrCreate(
            [
                'user_id' => $user->id,
                'content' => 'أنجزت تحدي الأسبوع! 💪',
            ],
            [
                'is_visible' => true,
                'created_at' => now()->subHours(3),
                'updated_at' => now()->subHours(3),
            ]
        );

        if (Schema::hasTable('community_reactions')) {
            \App\Models\CommunityReaction::updateOrCreate(
                ['post_id' => $traineePost->id, 'user_id' => $coach->id],
                ['reaction' => 'like']
            );
            // تفاعلات إضافية وهمية من نفس المدرب لا تكفي — أضف تفاعل من المستخدم على منشور المدرب
            \App\Models\CommunityReaction::updateOrCreate(
                ['post_id' => $coachPost->id, 'user_id' => $user->id],
                ['reaction' => 'like']
            );
        }

        if (Schema::hasTable('community_comments')) {
            \App\Models\CommunityComment::updateOrCreate(
                [
                    'post_id' => $traineePost->id,
                    'user_id' => $coach->id,
                    'content' => 'مبروك! استمر على نفس الوتيرة.',
                ],
                ['created_at' => now()->subHours(2), 'updated_at' => now()->subHours(2)]
            );

            // منشور ثانٍ من متدرب آخر إن وُجد
            $peer = User::query()
                ->where('id', '!=', $user->id)
                ->whereHas('roles', fn ($q) => $q->whereIn('name', ['user', 'client']))
                ->first();

            if ($peer) {
                $peerPost = \App\Models\CommunityPost::updateOrCreate(
                    [
                        'user_id' => $peer->id,
                        'content' => 'اليوم ركزت على التغذية والتزمت بالخطة كاملة ✅',
                    ],
                    [
                        'is_visible' => true,
                        'created_at' => now()->subHours(1),
                        'updated_at' => now()->subHours(1),
                    ]
                );

                \App\Models\CommunityReaction::updateOrCreate(
                    ['post_id' => $peerPost->id, 'user_id' => $user->id],
                    ['reaction' => 'like']
                );
            }
        }

        $this->info('  community posts/comments seeded');
    }

    private function seedBookings(User $user, User $coach): void
    {
        if (! Schema::hasTable('session_bookings') || ! Schema::hasTable('training_sessions')) {
            $this->warn('  bookings tables missing — skipped');

            return;
        }

        $sessions = \App\Models\TrainingSession::query()
            ->visible()
            ->visibleTo($user)
            ->ordered()
            ->limit(3)
            ->get();

        if ($sessions->isEmpty()) {
            $session = \App\Models\TrainingSession::query()->firstOrCreate(
                [
                    'title' => 'جلسة تدريب شخصي',
                    'user_id' => $coach->id,
                ],
                [
                    'description' => 'جلسة تدريب شخصي مع المدرب',
                    'price' => 0,
                    'duration_hours' => 1,
                    'session_type' => 'in_person',
                    'capacity' => 1,
                    'location' => 'الصالة الرئيسية',
                    'is_visible' => true,
                    'sort_order' => 1,
                    'audience_gender' => 'all',
                ]
            );
            $sessions = collect([$session]);
        }

        // ضمان توفر المدرب لأيام الأسبوع
        if (Schema::hasTable('coach_availabilities')) {
            foreach ([0, 1, 2, 3, 4, 5, 6] as $day) {
                \App\Models\CoachAvailability::updateOrCreate(
                    [
                        'user_id' => $coach->id,
                        'day_of_week' => $day,
                        'start_time' => '16:00:00',
                    ],
                    [
                        'end_time' => '21:00:00',
                        'slot_duration_minutes' => 60,
                        'buffer_minutes' => 0,
                        'location' => 'الصالة الرئيسية',
                        'capacity' => 5,
                        'is_active' => true,
                    ]
                );
            }
        }

        $defs = [
            ['offset' => 3, 'time' => '17:00', 'status' => 'confirmed', 'attendance' => 'scheduled'],
            ['offset' => 7, 'time' => '18:00', 'status' => 'confirmed', 'attendance' => 'scheduled'],
            ['offset' => 10, 'time' => '17:00', 'status' => 'confirmed', 'attendance' => 'scheduled'],
            ['offset' => -14, 'time' => '18:00', 'status' => 'completed', 'attendance' => 'attended'],
            ['offset' => -7, 'time' => '17:00', 'status' => 'completed', 'attendance' => 'attended'],
        ];

        $count = 0;
        foreach ($defs as $i => $def) {
            $session = $sessions[$i % $sessions->count()];
            $date = now()->copy()->addDays($def['offset'])->toDateString();

            \App\Models\SessionBooking::updateOrCreate(
                [
                    'training_session_id' => $session->id,
                    'user_id' => $user->id,
                    'booking_date' => $date,
                    'booking_time' => $def['time'],
                ],
                [
                    'status' => $def['status'],
                    'attendance_status' => $def['attendance'],
                    'payment_status' => 'paid',
                    'payment_amount' => $session->price ?? 0,
                    'video_meeting_url' => $session->video_meeting_url,
                    'notes' => null,
                ]
            );
            $count++;
        }

        // جلسات إضافية بأسماء متنوعة للعرض إن لزم
        if ($sessions->count() === 1 && Schema::hasTable('training_sessions')) {
            \App\Models\TrainingSession::firstOrCreate(
                ['title' => 'قياسات الجسم', 'user_id' => $coach->id],
                [
                    'description' => 'جلسة قياسات الجسم والتقييم',
                    'price' => 0,
                    'duration_hours' => 1,
                    'session_type' => 'in_person',
                    'capacity' => 4,
                    'location' => 'عيادة القياسات',
                    'is_visible' => true,
                    'sort_order' => 2,
                    'audience_gender' => 'all',
                ]
            );
            \App\Models\TrainingSession::firstOrCreate(
                ['title' => 'استشارة تغذية', 'user_id' => $coach->id],
                [
                    'description' => 'استشارة تغذية أونلاين',
                    'price' => 0,
                    'duration_hours' => 1,
                    'session_type' => 'online',
                    'capacity' => 1,
                    'location' => 'أونلاين',
                    'video_meeting_url' => 'https://meet.example.com/nutrition',
                    'is_visible' => true,
                    'sort_order' => 3,
                    'audience_gender' => 'all',
                ]
            );
        }

        $this->info("  bookings seeded ({$count} rows for user #{$user->id})");
    }
}
