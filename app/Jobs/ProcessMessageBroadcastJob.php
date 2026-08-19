<?php

namespace App\Jobs;

use App\Models\MessageBroadcast;
use App\Models\MessageBroadcastRecipient;
use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessMessageBroadcastJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public int $tenantId,
        public int $broadcastId
    ) {
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [5, 30, 90];
    }

    public function handle(): void
    {
        $tenant = Tenant::on('system')->find($this->tenantId);
        if (! $tenant) {
            return;
        }

        TenantService::switchToTenant($tenant);

        try {
            $broadcast = MessageBroadcast::query()->find($this->broadcastId);
            if (! $broadcast) {
                return;
            }

            if ($broadcast->status === MessageBroadcast::STATUS_QUEUED) {
                $broadcast->update([
                    'status' => MessageBroadcast::STATUS_PROCESSING,
                    'started_at' => $broadcast->started_at ?: now(),
                    'sent_at' => $broadcast->sent_at ?: now(),
                ]);
            }

            MessageBroadcastRecipient::query()
                ->where('broadcast_id', $broadcast->id)
                ->where('status', MessageBroadcastRecipient::STATUS_PENDING)
                ->orderBy('id')
                ->chunkById(50, function ($recipients) {
                    foreach ($recipients as $recipient) {
                        DeliverBroadcastRecipientJob::dispatch($this->tenantId, (int) $recipient->id);
                    }
                });
        } catch (Throwable $e) {
            Log::error('ProcessMessageBroadcastJob failed', [
                'tenant_id' => $this->tenantId,
                'broadcast_id' => $this->broadcastId,
                'error' => $e->getMessage(),
            ]);

            MessageBroadcast::query()->whereKey($this->broadcastId)->update([
                'status' => MessageBroadcast::STATUS_FAILED,
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            throw $e;
        } finally {
            TenantService::switchToDefault();
        }
    }
}
