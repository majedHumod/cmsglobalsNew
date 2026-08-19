<?php

namespace App\Services;

use App\Models\NotificationFeed;
use App\Models\PushSubscription;
use App\Services\Communication\CommunicationCatalog;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushService
{
    public function sendForNotification(NotificationFeed $notification): void
    {
        $prefs = app(NotificationPreferenceService::class);
        if (! $prefs->allowsPush((int) $notification->user_id, (string) $notification->type)) {
            return;
        }

        // Any catalogued (or default) notification type can be pushed when prefs allow.
        app(CommunicationCatalog::class)->metaForType((string) $notification->type);

        $publicKey = env('VAPID_PUBLIC_KEY');
        $privateKey = env('VAPID_PRIVATE_KEY');
        if (! $publicKey || ! $privateKey) {
            return;
        }

        $subscriptions = PushSubscription::query()
            ->where('user_id', $notification->user_id)
            ->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        try {
            $webPush = new WebPush([
                'VAPID' => [
                    'subject' => env('VAPID_SUBJECT', config('app.url')),
                    'publicKey' => $publicKey,
                    'privateKey' => $privateKey,
                ],
            ]);

            $presentation = app(CommunicationCatalog::class)->presentNotification(
                (string) $notification->type,
                is_array($notification->payload) ? $notification->payload : []
            );

            $payload = json_encode([
                'title' => $notification->title,
                'body' => $notification->body,
                'url' => $this->urlForNotification($notification),
                'type' => $notification->type,
                'channel' => $presentation['channel'],
                'screen' => $presentation['screen'],
                'action' => $presentation['action'],
                'priority' => $presentation['priority'],
            ], JSON_UNESCAPED_UNICODE);

            foreach ($subscriptions as $subscription) {
                $webPush->queueNotification(
                    Subscription::create([
                        'endpoint' => $subscription->endpoint,
                        'keys' => [
                            'p256dh' => $subscription->public_key,
                            'auth' => $subscription->auth_token,
                        ],
                    ]),
                    $payload
                );
            }

            foreach ($webPush->flush() as $report) {
                if (! $report->isSuccess()) {
                    Log::warning('Web push delivery failed', [
                        'reason' => $report->getReason(),
                    ]);
                }
            }
        } catch (\Throwable $exception) {
            Log::warning('Web push service error', ['error' => $exception->getMessage()]);
        }
    }

    private function urlForNotification(NotificationFeed $notification): string
    {
        $payload = is_array($notification->payload) ? $notification->payload : [];
        $action = $payload['action'] ?? [];
        $screen = $action['screen'] ?? $payload['screen'] ?? null;

        if (($action['conversation_id'] ?? null) || $notification->type === 'message.received') {
            $conversationId = $action['conversation_id'] ?? $payload['conversation_id'] ?? null;
            if ($conversationId) {
                return route('client.messages.show', ['conversation' => $conversationId]);
            }

            return route('client.messages.index');
        }

        if (str_starts_with((string) $notification->type, 'membership.expiring')) {
            return $payload['renew_url'] ?? route('client.home');
        }

        return match ($screen) {
            'community' => route('client.community.index'),
            'messages' => route('client.messages.index'),
            'habits' => route('client.habits.index'),
            'checkin' => route('client.progress.index'),
            'bookings' => route('client.bookings.index'),
            'home' => route('client.home'),
            default => route('client.notifications.index'),
        };
    }
}
