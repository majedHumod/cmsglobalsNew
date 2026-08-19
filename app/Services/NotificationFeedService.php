<?php

namespace App\Services;

use App\Models\NotificationFeed;
use App\Models\User;
use App\Services\Communication\CommunicationCatalog;

class NotificationFeedService
{
    public function pushToUser(
        int $userId,
        string $type,
        string $title,
        ?string $body = null,
        array $payload = [],
        bool $sendPush = true
    ): ?NotificationFeed {
        $prefs = app(NotificationPreferenceService::class);
        if (! $prefs->allowsInApp($userId, $type)) {
            return null;
        }

        $payload = app(CommunicationCatalog::class)->enrichPayload($type, $payload);

        $notification = NotificationFeed::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'payload' => $payload,
            'created_at' => now(),
        ]);

        if ($sendPush && $prefs->allowsPush($userId, $type)) {
            app(WebPushService::class)->sendForNotification($notification);
        }

        return $notification;
    }

    /**
     * @param  array<int>  $userIds
     */
    public function pushToUsers(
        array $userIds,
        string $type,
        string $title,
        ?string $body = null,
        array $payload = [],
        bool $sendPush = true
    ): void {
        $uniqueIds = array_values(array_unique(array_map('intval', $userIds)));
        if ($uniqueIds === []) {
            return;
        }

        $prefs = app(NotificationPreferenceService::class);
        $payload = app(CommunicationCatalog::class)->enrichPayload($type, $payload);
        $now = now();
        $rows = [];
        $acceptedUserIds = [];

        foreach ($uniqueIds as $userId) {
            if (! $prefs->allowsInApp($userId, $type)) {
                continue;
            }

            $rows[] = [
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'created_at' => $now,
            ];
            $acceptedUserIds[] = $userId;
        }

        if ($rows === []) {
            return;
        }

        NotificationFeed::insert($rows);

        if (! $sendPush) {
            return;
        }

        $created = NotificationFeed::query()
            ->whereIn('user_id', $acceptedUserIds)
            ->where('type', $type)
            ->where('created_at', $now)
            ->orderByDesc('id')
            ->limit(count($acceptedUserIds))
            ->get();

        foreach ($created as $notification) {
            if ($prefs->allowsPush((int) $notification->user_id, $type)) {
                app(WebPushService::class)->sendForNotification($notification);
            }
        }
    }

    public function markAllAsRead(User $user): void
    {
        NotificationFeed::where('user_id', $user->id)->whereNull('read_at')->update(['read_at' => now()]);
    }
}
