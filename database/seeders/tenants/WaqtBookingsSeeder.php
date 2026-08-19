<?php

namespace Database\Seeders\Tenants;

use App\Models\SessionBooking;
use App\Models\TrainingSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * حجوزات تجريبية لعملاء وقت اللياقة على جلسات مدرب (أحمد) ومدربة (نورة).
 */
class WaqtBookingsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('--- Waqt Bookings Seeder ---');

        if (! Schema::hasTable('training_sessions') || ! Schema::hasTable('session_bookings')) {
            $this->command->warn('  Skipping: training_sessions / session_bookings tables missing.');

            return;
        }

        $maleCoach = User::query()->where('email', 'ahmad@waqt.local')->first();
        $femaleCoach = User::query()->where('email', 'noura@waqt.local')->first();

        if (! $maleCoach || ! $femaleCoach) {
            $this->command->warn('  Skipping: ahmad@waqt.local or noura@waqt.local not found.');

            return;
        }

        $maleClients = User::query()
            ->whereIn('email', ['majed@waqt.local', 'yousef@waqt.local', 'hamoud@waqt.local'])
            ->orderBy('id')
            ->get()
            ->keyBy('email');

        $femaleClients = User::query()
            ->whereIn('email', ['nouf@waqt.local', 'maram@waqt.local', 'jouri@waqt.local'])
            ->orderBy('id')
            ->get()
            ->keyBy('email');

        if ($maleClients->count() < 3 || $femaleClients->count() < 3) {
            $this->command->warn('  Skipping: expected client accounts are missing.');

            return;
        }

        $sessions = $this->seedSessions($maleCoach, $femaleCoach);
        $count = $this->seedBookings($sessions, $maleClients, $femaleClients);

        $this->command->info("  Session bookings ready: {$count}");
        $this->command->info('✅ Waqt bookings seeded for أحمد and نورة.');
    }

    /**
     * @return array<string, TrainingSession>
     */
    private function seedSessions(User $maleCoach, User $femaleCoach): array
    {
        $definitions = [
            'male_personal' => [
                'title' => 'جلسة قوة شخصية',
                'user_id' => $maleCoach->id,
                'description' => 'تدريب شخصي مع الكابتن أحمد لبناء القوة وتحسين الأداء.',
                'price' => 150.00,
                'duration_hours' => 1,
                'session_type' => 'in_person',
                'capacity' => 1,
                'location' => 'صالة النادي الرئيسية',
                'is_visible' => true,
                'sort_order' => 20,
                'audience_gender' => 'male',
            ],
            'male_group' => [
                'title' => 'تدريب أوزان جماعي',
                'user_id' => $maleCoach->id,
                'description' => 'جلسة أثقال جماعية بإشراف الكابتن أحمد.',
                'price' => 100.00,
                'duration_hours' => 1,
                'session_type' => 'in_person',
                'capacity' => 6,
                'location' => 'صالة الأثقال',
                'is_visible' => true,
                'sort_order' => 21,
                'audience_gender' => 'male',
            ],
            'female_yoga' => [
                'title' => 'يوغا وتمدد مع نورة',
                'user_id' => $femaleCoach->id,
                'description' => 'جلسة يوغا وتمدد للسيدات مع الكابتن نورة.',
                'price' => 120.00,
                'duration_hours' => 1,
                'session_type' => 'in_person',
                'capacity' => 8,
                'location' => 'صالة السيدات',
                'is_visible' => true,
                'sort_order' => 30,
                'audience_gender' => 'female',
            ],
            'female_personal' => [
                'title' => 'تدريب شخصي للسيدات',
                'user_id' => $femaleCoach->id,
                'description' => 'تدريب شخصي واحد لواحد مع الكابتن نورة.',
                'price' => 180.00,
                'duration_hours' => 1,
                'session_type' => 'in_person',
                'capacity' => 1,
                'location' => 'استوديو السيدات',
                'is_visible' => true,
                'sort_order' => 31,
                'audience_gender' => 'female',
            ],
        ];

        $sessions = [];
        foreach ($definitions as $key => $data) {
            $session = TrainingSession::firstOrCreate(
                ['title' => $data['title'], 'user_id' => $data['user_id']],
                $data
            );

            $session->fill([
                'is_visible' => true,
                'audience_gender' => $data['audience_gender'],
                'location' => $data['location'],
                'capacity' => $data['capacity'],
                'price' => $data['price'],
            ]);
            if ($session->isDirty()) {
                $session->save();
            }

            $sessions[$key] = $session;
        }

        TrainingSession::clearCache();
        $this->command->info('  Training sessions ready for أحمد and نورة.');

        return $sessions;
    }

    /**
     * @param  array<string, TrainingSession>  $sessions
     * @param  \Illuminate\Support\Collection<string, User>  $maleClients
     * @param  \Illuminate\Support\Collection<string, User>  $femaleClients
     */
    private function seedBookings(array $sessions, $maleClients, $femaleClients): int
    {
        $majed = $maleClients['majed@waqt.local'];
        $yousef = $maleClients['yousef@waqt.local'];
        $hamoud = $maleClients['hamoud@waqt.local'];
        $nouf = $femaleClients['nouf@waqt.local'];
        $maram = $femaleClients['maram@waqt.local'];
        $jouri = $femaleClients['jouri@waqt.local'];

        $rows = [
            // الكابتن أحمد — قوة شخصية لماجد
            ['session' => 'male_personal', 'client' => $majed, 'days' => -14, 'time' => '10:00', 'status' => 'completed', 'attendance' => 'attended', 'payment' => 'paid', 'amount' => 150.00, 'ref' => 'WAQT-AHMAD-001', 'notes' => 'جلسة تقييم قوة أولية'],
            ['session' => 'male_personal', 'client' => $majed, 'days' => -7, 'time' => '10:00', 'status' => 'completed', 'attendance' => 'attended', 'payment' => 'paid', 'amount' => 150.00, 'ref' => 'WAQT-AHMAD-002', 'notes' => 'متابعة الأوزان المركبة'],
            ['session' => 'male_personal', 'client' => $majed, 'days' => 3, 'time' => '10:00', 'status' => 'confirmed', 'attendance' => 'scheduled', 'payment' => 'paid', 'amount' => 150.00, 'ref' => 'WAQT-AHMAD-003', 'notes' => 'جلسة قادمة مع الكابتن أحمد'],
            // الكابتن أحمد — أوزان جماعي ليوسف وحمود
            ['session' => 'male_group', 'client' => $yousef, 'days' => -10, 'time' => '16:00', 'status' => 'completed', 'attendance' => 'attended', 'payment' => 'paid', 'amount' => 100.00, 'ref' => 'WAQT-AHMAD-004', 'notes' => null],
            ['session' => 'male_group', 'client' => $hamoud, 'days' => -10, 'time' => '15:00', 'status' => 'completed', 'attendance' => 'missed', 'payment' => 'paid', 'amount' => 100.00, 'ref' => 'WAQT-AHMAD-005', 'notes' => 'لم يحضر الجلسة'],
            ['session' => 'male_group', 'client' => $yousef, 'days' => 5, 'time' => '16:00', 'status' => 'confirmed', 'attendance' => 'scheduled', 'payment' => 'paid', 'amount' => 100.00, 'ref' => 'WAQT-AHMAD-006', 'notes' => null],
            ['session' => 'male_group', 'client' => $hamoud, 'days' => 5, 'time' => '15:00', 'status' => 'confirmed', 'attendance' => 'scheduled', 'payment' => 'paid', 'amount' => 100.00, 'ref' => 'WAQT-AHMAD-007', 'notes' => null],
            // الكابتن نورة — يوغا للسيدات
            ['session' => 'female_yoga', 'client' => $nouf, 'days' => -21, 'time' => '09:00', 'status' => 'completed', 'attendance' => 'attended', 'payment' => 'paid', 'amount' => 120.00, 'ref' => 'WAQT-NOURA-001', 'notes' => null],
            ['session' => 'female_yoga', 'client' => $maram, 'days' => -21, 'time' => '10:00', 'status' => 'completed', 'attendance' => 'attended', 'payment' => 'paid', 'amount' => 120.00, 'ref' => 'WAQT-NOURA-002', 'notes' => null],
            ['session' => 'female_yoga', 'client' => $jouri, 'days' => -21, 'time' => '11:00', 'status' => 'completed', 'attendance' => 'missed', 'payment' => 'paid', 'amount' => 120.00, 'ref' => 'WAQT-NOURA-003', 'notes' => 'غياب بعذر'],
            ['session' => 'female_yoga', 'client' => $nouf, 'days' => 4, 'time' => '09:00', 'status' => 'confirmed', 'attendance' => 'scheduled', 'payment' => 'paid', 'amount' => 120.00, 'ref' => 'WAQT-NOURA-004', 'notes' => null],
            ['session' => 'female_yoga', 'client' => $maram, 'days' => 4, 'time' => '10:00', 'status' => 'confirmed', 'attendance' => 'scheduled', 'payment' => 'paid', 'amount' => 120.00, 'ref' => 'WAQT-NOURA-005', 'notes' => null],
            ['session' => 'female_yoga', 'client' => $jouri, 'days' => 4, 'time' => '11:00', 'status' => 'pending', 'attendance' => 'scheduled', 'payment' => 'pending', 'amount' => 120.00, 'ref' => null, 'notes' => 'بانتظار تأكيد الدفع'],
            // الكابتن نورة — تدريب شخصي
            ['session' => 'female_personal', 'client' => $nouf, 'days' => -14, 'time' => '12:00', 'status' => 'completed', 'attendance' => 'attended', 'payment' => 'paid', 'amount' => 180.00, 'ref' => 'WAQT-NOURA-006', 'notes' => 'خطة أسبوعية جديدة'],
            ['session' => 'female_personal', 'client' => $nouf, 'days' => 7, 'time' => '12:00', 'status' => 'confirmed', 'attendance' => 'scheduled', 'payment' => 'paid', 'amount' => 180.00, 'ref' => 'WAQT-NOURA-007', 'notes' => 'جلسة قادمة مع الكابتن نورة'],
            ['session' => 'female_personal', 'client' => $maram, 'days' => 10, 'time' => '11:00', 'status' => 'confirmed', 'attendance' => 'scheduled', 'payment' => 'paid', 'amount' => 180.00, 'ref' => 'WAQT-NOURA-008', 'notes' => null],
        ];

        $count = 0;
        foreach ($rows as $row) {
            $session = $sessions[$row['session']] ?? null;
            if (! $session) {
                continue;
            }

            $date = $this->onWeekday($row['days'])->toDateString();

            SessionBooking::updateOrCreate(
                [
                    'training_session_id' => $session->id,
                    'booking_date' => $date,
                    'booking_time' => $row['time'],
                ],
                [
                    'user_id' => $row['client']->id,
                    'status' => $row['status'],
                    'attendance_status' => $row['attendance'],
                    'payment_status' => $row['payment'],
                    'payment_amount' => $row['amount'],
                    'payment_reference' => $row['ref'],
                    'notes' => $row['notes'],
                ]
            );
            $count++;
        }

        return $count;
    }

    private function onWeekday(int $daysFromNow): Carbon
    {
        $date = Carbon::now()->startOfDay()->addDays($daysFromNow);

        while ($date->isFriday() || $date->isSaturday()) {
            $date = $daysFromNow >= 0 ? $date->addDay() : $date->subDay();
        }

        return $date;
    }
}
