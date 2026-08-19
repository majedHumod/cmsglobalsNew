<?php

namespace App\Jobs;

use App\Models\FollowUpCampaignRun;
use App\Models\User;
use App\Services\Communication\CommunicationGatewayInterface;
use App\Services\NotificationFeedService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendInactiveClientFollowUpJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(private readonly int $userId, private readonly string $campaignKey)
    {
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [10, 60, 180];
    }

    public function handle(
        NotificationFeedService $notificationFeedService,
        CommunicationGatewayInterface $communicationGateway
    ): void
    {
        $user = User::query()->find($this->userId);
        if (! $user || ! $user->hasAnyRole(['user', 'client'])) {
            return;
        }

        $alreadySent = FollowUpCampaignRun::query()
            ->where('campaign_key', $this->campaignKey)
            ->where('user_id', $user->id)
            ->whereDate('created_at', now()->toDateString())
            ->exists();

        if ($alreadySent) {
            return;
        }

        $notificationFeedService->pushToUser(
            $user->id,
            'followup.inactive_client',
            'نفتقد نشاطك',
            'عد اليوم وسجّل Check-in أو عادة واحدة على الأقل.',
            ['campaign_key' => $this->campaignKey]
        );

        $gatewayResult = $communicationGateway->sendTemplate($user->id, 'inactive_followup', [
            'campaign_key' => $this->campaignKey,
        ]);

        FollowUpCampaignRun::create([
            'campaign_key' => $this->campaignKey,
            'user_id' => $user->id,
            'status' => 'sent',
            'meta' => ['source' => 'automated_job', 'gateway' => $gatewayResult],
            'sent_at' => now(),
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('SendInactiveClientFollowUpJob failed', [
            'user_id' => $this->userId,
            'campaign_key' => $this->campaignKey,
            'message' => $exception->getMessage(),
        ]);
    }
}
