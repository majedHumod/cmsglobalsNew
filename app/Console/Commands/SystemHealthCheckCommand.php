<?php

namespace App\Console\Commands;

use App\Models\IntegrationWebhookLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;

class SystemHealthCheckCommand extends Command
{
    protected $signature = 'system:health-check {--json} {--fail-on-warning}';

    protected $description = 'Run health checks for DB, cache, queue and scheduler-related indicators';

    public function handle(): int
    {
        $status = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'queue' => $this->checkQueueBacklog(),
            'webhooks' => $this->checkWebhookFailures(),
        ];

        if ($this->option('json')) {
            $this->line(json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } else {
            foreach ($status as $name => $result) {
                $this->line(sprintf('[%s] %s - %s', strtoupper($name), $result['state'], $result['message']));
            }
        }

        $hasWarning = collect($status)->contains(fn ($item) => $item['state'] !== 'ok');
        if ($hasWarning && (bool) $this->option('fail-on-warning')) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function checkDatabase(): array
    {
        try {
            DB::select('select 1 as ok');
            return ['state' => 'ok', 'message' => 'database reachable'];
        } catch (\Throwable $e) {
            return ['state' => 'error', 'message' => 'database unreachable: ' . $e->getMessage()];
        }
    }

    private function checkCache(): array
    {
        try {
            $key = 'health:cache:' . now()->timestamp;
            Cache::put($key, 'ok', now()->addMinute());
            $value = Cache::get($key);
            return ['state' => $value === 'ok' ? 'ok' : 'warn', 'message' => 'cache read/write check'];
        } catch (\Throwable $e) {
            return ['state' => 'error', 'message' => 'cache failed: ' . $e->getMessage()];
        }
    }

    private function checkQueueBacklog(): array
    {
        try {
            $queueSize = (int) Redis::llen('queues:default');
            if ($queueSize > 500) {
                return ['state' => 'warn', 'message' => "queue backlog high ({$queueSize})"];
            }

            return ['state' => 'ok', 'message' => "queue backlog normal ({$queueSize})"];
        } catch (\Throwable) {
            return ['state' => 'warn', 'message' => 'queue backlog unavailable (non-redis queue or redis unreachable)'];
        }
    }

    private function checkWebhookFailures(): array
    {
        try {
            if (! Schema::hasTable('integration_webhook_logs')) {
                return ['state' => 'warn', 'message' => 'integration_webhook_logs table is not available in current context'];
            }

            $failed = IntegrationWebhookLog::query()
                ->whereNotNull('status_code')
                ->where('status_code', '>=', 400)
                ->where('created_at', '>=', now()->subHours(24))
                ->count();

            if ($failed > 20) {
                return ['state' => 'warn', 'message' => "webhook failures last 24h: {$failed}"];
            }

            return ['state' => 'ok', 'message' => "webhook failures last 24h: {$failed}"];
        } catch (\Throwable $e) {
            return ['state' => 'warn', 'message' => 'webhook check failed: ' . $e->getMessage()];
        }
    }
}
