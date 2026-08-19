<?php

namespace App\Jobs;

use App\Models\MessageBroadcast;
use App\Models\MessageBroadcastRecipient;
use App\Models\Tenant;
use App\Services\MessagingService;
use App\Services\TenantService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class DeliverBroadcastRecipientJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 60;

    public function __construct(
        public int $tenantId,
        public int $recipientRowId
    ) {
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [5, 20, 60];
    }

    public function handle(MessagingService $messaging): void
    {
        $tenant = Tenant::on('system')->find($this->tenantId);
        if (! $tenant) {
            return;
        }

        TenantService::switchToTenant($tenant);

        try {
            $row = MessageBroadcastRecipient::query()->find($this->recipientRowId);
            if (! $row || $row->status !== MessageBroadcastRecipient::STATUS_PENDING) {
                return;
            }

            $messaging->deliverBroadcastRecipient($row);
        } catch (Throwable $e) {
            Log::warning('DeliverBroadcastRecipientJob failed', [
                'tenant_id' => $this->tenantId,
                'recipient_row_id' => $this->recipientRowId,
                'error' => $e->getMessage(),
            ]);

            $row = MessageBroadcastRecipient::query()->find($this->recipientRowId);
            if ($row && $row->status === MessageBroadcastRecipient::STATUS_PENDING) {
                $row->update([
                    'status' => MessageBroadcastRecipient::STATUS_FAILED,
                    'error_message' => mb_substr($e->getMessage(), 0, 1000),
                ]);

                $broadcast = MessageBroadcast::query()->find($row->broadcast_id);
                if ($broadcast) {
                    $messaging->refreshBroadcastCounters($broadcast);
                }
            }

            throw $e;
        } finally {
            TenantService::switchToDefault();
        }
    }
}
