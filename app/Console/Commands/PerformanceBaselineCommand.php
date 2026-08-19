<?php

namespace App\Console\Commands;

use App\Models\ConversationParticipant;
use App\Models\HabitLog;
use App\Models\NotificationFeed;
use App\Models\ProgressCheckIn;
use App\Models\SessionBooking;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PerformanceBaselineCommand extends Command
{
    protected $signature = 'performance:baseline {--json}';

    protected $description = 'Measure baseline timings for critical queries';

    public function handle(): int
    {
        $ranges = [
            'active_clients_last_7d' => fn () => ProgressCheckIn::query()
                ->where('checked_in_at', '>=', now()->subDays(7))
                ->distinct('user_id')
                ->count('user_id'),
            'habit_logs_last_7d' => fn () => HabitLog::query()
                ->where('logged_on', '>=', now()->subDays(6)->toDateString())
                ->count(),
            'upcoming_bookings' => fn () => SessionBooking::query()->upcoming()->count(),
            'unread_notifications_last_7d' => fn () => NotificationFeed::query()
                ->whereNull('read_at')
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
            'conversation_participants' => fn () => ConversationParticipant::query()->count(),
            'clients_total' => fn () => User::query()->clients()->count(),
        ];

        $results = [];
        foreach ($ranges as $name => $callback) {
            DB::connection()->flushQueryLog();
            DB::connection()->enableQueryLog();
            $start = microtime(true);
            $value = $callback();
            $durationMs = (int) round((microtime(true) - $start) * 1000);
            $queries = DB::connection()->getQueryLog();

            $results[$name] = [
                'duration_ms' => $durationMs,
                'query_count' => count($queries),
                'value' => $value,
            ];
        }

        if ($this->option('json')) {
            $this->line(json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return self::SUCCESS;
        }

        $this->info('Performance baseline:');
        foreach ($results as $name => $item) {
            $this->line(sprintf(
                '- %s => %dms (%d queries), value=%s',
                $name,
                $item['duration_ms'],
                $item['query_count'],
                (string) $item['value']
            ));
        }

        return self::SUCCESS;
    }
}
