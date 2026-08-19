<?php

namespace Database\Seeders\Tenants;

use App\Models\NotificationFeed;
use App\Models\ProgressCheckIn;
use App\Models\SessionBooking;
use App\Models\TrainingSession;
use App\Models\User;
use App\Models\UserMembership;
use App\Models\WorkoutLog;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * بيانات تجريبية للمرحلة 2: عملاء معرّضون للانقطاع، عضوية تنتهي قريباً، إشعارات، حجز قابل للإلغاء
 */
class PhaseTwoDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('--- Phase Two Demo Seeder ---');

        $coach = User::where('email', 'ahmad.coach@app1.local')->first();
        $mohammed = User::where('email', 'mohammed.ali@app1.local')->first();
        $saad = User::where('email', 'saad.otaibi@app1.local')->first();

        if (! $coach || ! $mohammed) {
            $this->command->warn('  ⚠ Coach or mohammed.ali missing — run App1DemoSeeder first.');

            return;
        }

        if ($saad) {
            $this->seedAtRiskClient($saad, $coach);
        }

        $this->seedExpiringMembership($mohammed);
        $this->seedNotifications($mohammed, $coach);
        $this->seedCancellableBooking($mohammed, $coach);

        $this->command->info('✅ Phase Two demo ready.');
        $this->command->line('   At-risk client: saad.otaibi@app1.local / password');
        $this->command->line('   Expiring soon:  mohammed.ali@app1.local / password');
        $this->command->line('   Coach workspace: /coach/workspace');
        $this->command->line('   Client bookings: /client/bookings');
    }

    private function seedAtRiskClient(User $client, User $coach): void
    {
        if (Schema::hasTable('progress_check_ins')) {
            ProgressCheckIn::query()
                ->where('user_id', $client->id)
                ->where('checked_in_at', '>=', now()->subDays(14))
                ->delete();

            ProgressCheckIn::updateOrCreate(
                [
                    'user_id' => $client->id,
                    'checked_in_at' => now()->subDays(20)->startOfDay(),
                ],
                [
                    'coach_id' => $coach->id,
                    'weight' => 85.0,
                    'body_fat_percentage' => 26.0,
                    'waist_cm' => 94,
                    'energy_level' => 4,
                    'training_adherence' => 5,
                    'nutrition_adherence' => 4,
                    'notes' => 'آخر check-in منذ 20 يوماً — للعرض التجريبي',
                    'coach_feedback' => 'يحتاج متابعة عاجلة',
                ]
            );
        }

        if (Schema::hasTable('workout_logs')) {
            WorkoutLog::query()
                ->where('user_id', $client->id)
                ->where('scheduled_on', '>=', now()->subDays(14)->toDateString())
                ->delete();
        }

        $this->command->info('  At-risk profile: saad.otaibi (old check-in, low compliance)');
    }

    private function seedExpiringMembership(User $client): void
    {
        $expiresAt = now()->addDays(5)->endOfDay();

        $client->forceFill(['membership_expires_at' => $expiresAt])->save();

        if (Schema::hasTable('user_memberships')) {
            $membership = UserMembership::query()
                ->where('user_id', $client->id)
                ->where('is_active', true)
                ->latest('expires_at')
                ->first();

            if ($membership) {
                $membership->update(['expires_at' => $expiresAt]);
            }
        }

        $this->command->info('  Membership expires in 5 days: mohammed.ali');
    }

    private function seedNotifications(User $client, User $coach): void
    {
        if (! Schema::hasTable('notifications_feed')) {
            return;
        }

        $now = now();

        NotificationFeed::query()->updateOrCreate(
            [
                'user_id' => $client->id,
                'type' => 'membership.expiring_7',
                'created_at' => $now->copy()->startOfDay(),
            ],
            [
                'title' => 'اشتراكك ينتهي خلال أسبوع',
                'body' => 'متبقي 5 أيام — جدّد الآن للاستمرار في برنامجك.',
                'payload' => ['days_remaining' => 5],
                'read_at' => null,
            ]
        );

        NotificationFeed::query()->updateOrCreate(
            [
                'user_id' => $client->id,
                'type' => 'habit.missed_streak',
                'created_at' => $now->copy()->subDay()->startOfDay(),
            ],
            [
                'title' => 'تذكير العادات',
                'body' => 'لم تُسجّل عاداتك أمس.',
                'payload' => [],
                'read_at' => $now->copy()->subHours(2),
            ]
        );

        NotificationFeed::query()->updateOrCreate(
            [
                'user_id' => $coach->id,
                'type' => 'coach.client_at_risk',
                'created_at' => $now->copy()->startOfDay(),
            ],
            [
                'title' => 'عميل يحتاج متابعة',
                'body' => 'العميل سعد العتيبي متأخر في Check-in ومنخفض الالتزام بالتمارين.',
                'payload' => ['workspace_url' => '/coach/workspace?filter=low_compliance'],
                'read_at' => null,
            ]
        );

        $this->command->info('  Notification feed entries seeded');
    }

    private function seedCancellableBooking(User $client, User $coach): void
    {
        if (! Schema::hasTable('session_bookings')) {
            return;
        }

        $session = TrainingSession::query()
            ->where('user_id', $coach->id)
            ->where('is_visible', true)
            ->orderBy('id')
            ->first();

        if (! $session) {
            return;
        }

        $bookingDate = Carbon::now()->addDays(10)->toDateString();
        $bookingTime = '09:00:00';

        SessionBooking::updateOrCreate(
            [
                'training_session_id' => $session->id,
                'booking_date' => $bookingDate,
                'booking_time' => $bookingTime,
            ],
            [
                'user_id' => $client->id,
                'status' => 'confirmed',
                'attendance_status' => 'scheduled',
                'payment_status' => 'paid',
                'payment_amount' => 150.00,
                'payment_reference' => 'BK-PHASE2-DEMO',
            ]
        );

        $this->command->info('  Cancellable booking in 10 days for mohammed.ali');
    }
}
