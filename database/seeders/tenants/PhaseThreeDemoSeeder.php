<?php

namespace Database\Seeders\Tenants;

use App\Models\CommunityPost;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\MealLog;
use App\Models\Message;
use App\Models\NotificationFeed;
use App\Models\User;
use App\Models\WeeklyChallenge;
use App\Services\GamificationService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * بيانات تجريبية للمرحلة 3: رسائل، تغذية، مجتمع، وتحديات
 */
class PhaseThreeDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('--- Phase Three Demo Seeder ---');

        $coach = User::where('email', 'ahmad.coach@app1.local')->first();
        $client = User::where('email', 'mohammed.ali@app1.local')->first();

        if (! $coach || ! $client) {
            $this->command->warn('  ⚠ Coach or client missing — run App1DemoSeeder first.');

            return;
        }

        $this->seedCoachClientConversation($coach, $client);
        $this->seedMealLogs($client);
        $this->seedCommunityActivity($client);
        $this->seedWeeklyChallenge();
        $this->seedNotifications($client);

        $this->command->info('✅ Phase Three demo ready for: mohammed.ali@app1.local');
        $this->command->line('   /client/messages — /client/nutrition — /client/community — /client/challenges');
    }

    private function seedCoachClientConversation(User $coach, User $client): void
    {
        if (! Schema::hasTable('conversations')) {
            return;
        }

        $conversation = Conversation::query()
            ->whereHas('participants', fn ($q) => $q->where('user_id', $coach->id))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $client->id))
            ->first();

        if (! $conversation) {
            $conversation = Conversation::create([
                'created_by_user_id' => $coach->id,
                'subject' => 'متابعة البرنامج',
                'last_message_at' => now(),
            ]);

            ConversationParticipant::insert([
                [
                    'conversation_id' => $conversation->id,
                    'user_id' => $coach->id,
                    'last_read_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'conversation_id' => $conversation->id,
                    'user_id' => $client->id,
                    'last_read_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        Message::updateOrCreate(
            [
                'conversation_id' => $conversation->id,
                'sender_user_id' => $coach->id,
                'body' => 'محمد، لا تنسَ تسجيل وجباتك وإرسال Check-in هذا الأسبوع.',
            ],
            ['sent_at' => now()->subHours(2)]
        );

        $conversation->update(['last_message_at' => now()->subHours(2)]);

        $this->command->info('  Coach-client conversation seeded');
    }

    private function seedMealLogs(User $client): void
    {
        if (! Schema::hasTable('meal_logs')) {
            return;
        }

        foreach (['breakfast', 'lunch', 'dinner'] as $index => $slot) {
            MealLog::updateOrCreate(
                [
                    'user_id' => $client->id,
                    'logged_on' => now()->toDateString(),
                    'meal_slot' => $slot,
                ],
                [
                    'adherence_score' => 7 + $index,
                    'notes' => 'وجبة تجريبية للمرحلة 3',
                ]
            );
        }

        MealLog::updateOrCreate(
            [
                'user_id' => $client->id,
                'logged_on' => now()->subDay()->toDateString(),
                'meal_slot' => 'lunch',
            ],
            ['adherence_score' => 5, 'notes' => 'يوم أقل التزاماً']
        );

        $this->command->info('  Meal logs seeded');
    }

    private function seedCommunityActivity(User $client): void
    {
        if (! Schema::hasTable('community_posts')) {
            return;
        }

        CommunityPost::updateOrCreate(
            [
                'user_id' => $client->id,
                'content' => 'أنهيت تمرين اليوم والتزمت بخطة الغذاء! 💪',
            ],
            ['is_visible' => true]
        );

        $this->command->info('  Community post seeded');
    }

    private function seedWeeklyChallenge(): void
    {
        if (! Schema::hasTable('weekly_challenges')) {
            return;
        }

        app(GamificationService::class)->bootstrapBadges();

        WeeklyChallenge::updateOrCreate(
            [
                'title' => 'تحدي الالتزام الأسبوعي',
                'starts_on' => now()->startOfWeek(Carbon::SATURDAY)->toDateString(),
            ],
            [
                'challenge_type' => 'habit_completion',
                'target_value' => 5,
                'ends_on' => now()->endOfWeek(Carbon::FRIDAY)->toDateString(),
                'is_active' => true,
            ]
        );

        $this->command->info('  Weekly challenge seeded');
    }

    private function seedNotifications(User $client): void
    {
        if (! Schema::hasTable('notifications_feed')) {
            return;
        }

        NotificationFeed::updateOrCreate(
            [
                'user_id' => $client->id,
                'type' => 'message.received',
                'created_at' => now()->subHours(2)->startOfHour(),
            ],
            [
                'title' => 'رسالة جديدة',
                'body' => 'لديك رسالة جديدة من أحمد الغامدي',
                'payload' => ['messages_url' => '/client/messages'],
                'read_at' => null,
            ]
        );
    }
}
