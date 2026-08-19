<?php

namespace Database\Seeders\Tenants;

use App\Models\ClientProfile;
use App\Models\Habit;
use App\Models\HabitLog;
use App\Models\SessionBooking;
use App\Models\TrainingSession;
use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutLog;
use App\Models\WorkoutSchedule;
use App\Services\WorkoutScheduleService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * بيانات تجريبية للجدول اليومي للمتدرب (صفحة /client/home)
 * يُكمّل App1DemoSeeder ببرنامج أسبوعي كامل + عادات + سجلات التزام
 */
class ClientDailyDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('--- Client Daily Demo Seeder ---');

        $coach = User::where('email', 'ahmad.coach@app1.local')->first();
        $client = User::where('email', 'mohammed.ali@app1.local')->first();

        if (! $coach || ! $client) {
            $this->command->warn('  ⚠ Coach or client missing — run App1DemoSeeder first.');
            return;
        }

        $this->seedClientProgramWeek($client);
        $workouts = $this->ensureWorkouts($coach);
        $schedules = $this->seedWeeklySchedule($coach, $workouts);
        $this->seedWorkoutLogs($client, $schedules);
        $this->seedHabits($client, $coach);
        $this->seedTodayBooking($client, $coach);

        $this->command->info('✅ Client daily demo ready for: mohammed.ali@app1.local');
        $this->command->line('   Login: mohammed.ali@app1.local / password');
        $this->command->line('   Page:  /client/home');
    }

    private function seedClientProgramWeek(User $client): void
    {
        if (! Schema::hasTable('client_profiles')) {
            return;
        }

        ClientProfile::updateOrCreate(
            ['user_id' => $client->id],
            ['current_program_week' => 1]
        );

        $this->command->info('  current_program_week = 1 for محمد علي');
    }

    /**
     * @return array<int, Workout>
     */
    private function ensureWorkouts(User $coach): array
    {
        if (! Schema::hasTable('workouts')) {
            return [];
        }

        $definitions = [
            'upper' => [
                'name' => 'برنامج القوة للمبتدئين - الجزء العلوي',
                'description' => "بنش بريس، تجديف، ضغط كتف، بايسبس وترايسبس.",
                'duration' => 45,
                'difficulty' => 'easy',
                'audience_gender' => 'male',
            ],
            'lower' => [
                'name' => 'برنامج القوة للمبتدئين - الجزء السفلي',
                'description' => "سكوات، ديدليفت، لانج، ليج بريس.",
                'duration' => 50,
                'difficulty' => 'medium',
                'audience_gender' => 'male',
            ],
            'hiit' => [
                'name' => 'HIIT كارديو 20 دقيقة',
                'description' => "جولات عالية الكثافة لحرق الدهون.",
                'duration' => 20,
                'difficulty' => 'hard',
                'audience_gender' => 'all',
            ],
            'endurance' => [
                'name' => 'تدريب التحمل المتقدم',
                'description' => "ركض، حبل، برو ميدبول وتمارين تنفس.",
                'duration' => 60,
                'difficulty' => 'hard',
                'audience_gender' => 'all',
            ],
        ];

        $workouts = [];
        foreach ($definitions as $key => $data) {
            $workouts[$key] = Workout::firstOrCreate(
                ['name' => $data['name'], 'user_id' => $coach->id],
                array_merge($data, ['status' => true, 'user_id' => $coach->id])
            );
        }

        return $workouts;
    }

    /**
     * @param  array<int, Workout>  $workouts
     * @return array<int, WorkoutSchedule>
     */
    private function seedWeeklySchedule(User $coach, array $workouts): array
    {
        if (! Schema::hasTable('workout_schedules') || empty($workouts)) {
            return [];
        }

        $weekPlan = [
            1 => ['workout' => $workouts['upper'], 'notes' => 'السبت — ركز على الشكل الصحيح'],
            2 => ['workout' => $workouts['hiit'], 'notes' => 'الأحد — شدة 60%'],
            3 => ['workout' => $workouts['lower'], 'notes' => 'الاثنين — راحة 90 ثانية بين الجولات'],
            4 => ['workout' => $workouts['upper'], 'notes' => 'الثلاثاء — زد الوزن 5% إن أمكن'],
            5 => ['workout' => $workouts['lower'], 'notes' => 'الأربعاء — تحكم بالحركة'],
            6 => ['workout' => $workouts['hiit'], 'notes' => 'الخميس — استهدف نبض 70-80%'],
            7 => ['workout' => $workouts['endurance'], 'notes' => 'الجمعة — تمرين اليوم: تحمل وقوة تحملية'],
        ];

        $schedules = [];
        foreach ($weekPlan as $session => $item) {
            $schedules[$session] = WorkoutSchedule::firstOrCreate(
                [
                    'workout_id' => $item['workout']->id,
                    'week_number' => 1,
                    'session_number' => $session,
                    'user_id' => $coach->id,
                ],
                [
                    'notes' => $item['notes'],
                    'status' => true,
                    'audience_gender' => 'male',
                ]
            );
        }

        $this->command->info('  Weekly schedule (7 days, week 1) seeded.');

        return $schedules;
    }

    /**
     * @param  array<int, WorkoutSchedule>  $schedules
     */
    private function seedWorkoutLogs(User $client, array $schedules): void
    {
        if (! Schema::hasTable('workout_logs') || empty($schedules)) {
            return;
        }

        $service = app(WorkoutScheduleService::class);
        $today = now();
        $todaySession = $service->sessionNumberForDate($today);

        $weekStart = $today->copy()->startOfDay();
        $daysBack = $todaySession - 1;
        $weekStart->subDays($daysBack);

        $count = 0;
        for ($session = 1; $session <= 7; $session++) {
            $schedule = $schedules[$session] ?? null;
            if (! $schedule) {
                continue;
            }

            $date = $weekStart->copy()->addDays($session - 1)->toDateString();

            if ($session < $todaySession) {
                WorkoutLog::updateOrCreate(
                    [
                        'user_id' => $client->id,
                        'workout_schedule_id' => $schedule->id,
                        'scheduled_on' => $date,
                    ],
                    [
                        'workout_id' => $schedule->workout_id,
                        'status' => $session === 2 ? 'skipped' : 'completed',
                        'completed_at' => Carbon::parse($date)->setTime(18, 0),
                        'notes' => $session === 2 ? 'راحة نشطة' : null,
                    ]
                );
                $count++;
            }
        }

        $this->command->info("  Workout logs seeded: {$count} (today session {$todaySession} left open)");
    }

    private function seedHabits(User $client, User $coach): void
    {
        if (! Schema::hasTable('habits')) {
            return;
        }

        $habitDefs = [
            ['name' => 'شرب 3 لتر ماء', 'unit' => 'لتر', 'target' => 3],
            ['name' => 'نوم 7 ساعات', 'unit' => 'ساعة', 'target' => 7],
            ['name' => '10 آلاف خطوة', 'unit' => 'خطوة', 'target' => 10000],
        ];

        $today = now()->toDateString();
        $count = 0;

        foreach ($habitDefs as $index => $def) {
            $habit = Habit::firstOrCreate(
                [
                    'client_user_id' => $client->id,
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

            HabitLog::updateOrCreate(
                [
                    'habit_id' => $habit->id,
                    'user_id' => $client->id,
                    'logged_on' => $today,
                ],
                [
                    'is_completed' => $index < 2,
                    'value' => $index < 2 ? $def['target'] : 0,
                ]
            );
            $count++;
        }

        $this->command->info("  Habits seeded: {$count}");
    }

    private function seedTodayBooking(User $client, User $coach): void
    {
        if (! Schema::hasTable('session_bookings')) {
            return;
        }

        $session = TrainingSession::firstOrCreate(
            ['title' => 'جلسة قوة للمبتدئين', 'user_id' => $coach->id],
            [
                'description' => 'جلسة متابعة أسبوعية مع المدرب أحمد.',
                'price' => 150.00,
                'duration_hours' => 1,
                'session_type' => 'in_person',
                'capacity' => 1,
                'location' => 'صالة النادي الرئيسية',
                'is_visible' => true,
                'sort_order' => 1,
                'audience_gender' => 'male',
            ]
        );

        $today = now()->toDateString();

        SessionBooking::firstOrCreate(
            [
                'training_session_id' => $session->id,
                'user_id' => $client->id,
                'booking_date' => $today,
            ],
            [
                'booking_time' => '17:00',
                'status' => 'confirmed',
                'attendance_status' => 'scheduled',
                'payment_status' => 'paid',
                'payment_amount' => 150.00,
                'payment_reference' => 'BK-DAILY-'.now()->format('Ymd'),
            ]
        );

        $this->command->info('  Today session booking added.');
    }
}
