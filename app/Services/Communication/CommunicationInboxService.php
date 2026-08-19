<?php

namespace App\Services\Communication;

use App\Models\NotificationFeed;
use App\Models\User;
use App\Services\MessagingService;

class CommunicationInboxService
{
    public function __construct(
        private MessagingService $messaging,
        private CommunicationCatalog $catalog,
    ) {
    }

    /**
     * Unified unread / inbox summary for mobile badges and home.
     *
     * @return array<string, mixed>
     */
    public function summaryFor(User $user): array
    {
        $messagesUnread = $this->messaging->unreadCountFor($user);

        $unreadNotifications = NotificationFeed::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->get(['type']);

        $notificationsUnread = $unreadNotifications->count();

        $byCategory = $unreadNotifications
            ->groupBy(fn (NotificationFeed $row) => $this->catalog->metaForType((string) $row->type)['category'])
            ->map(fn ($rows) => $rows->count())
            ->all();

        $byChannel = $unreadNotifications
            ->groupBy(fn (NotificationFeed $row) => $this->catalog->metaForType((string) $row->type)['channel'])
            ->map(fn ($rows) => $rows->count())
            ->all();

        return [
            'messages_unread' => $messagesUnread,
            'notifications_unread' => $notificationsUnread,
            'total_unread' => $messagesUnread + $notificationsUnread,
            'by_category' => (object) $byCategory,
            'by_channel' => (object) $byChannel,
            'channels' => [
                'dm' => [
                    'unread' => $messagesUnread,
                    'screen' => 'messages',
                ],
                'notification' => [
                    'unread' => $notificationsUnread,
                    'screen' => 'notifications',
                ],
                'community' => [
                    'unread' => (int) ($byChannel['community'] ?? 0),
                    'screen' => 'community',
                ],
                'broadcast' => [
                    'unread' => (int) ($byChannel['broadcast'] ?? 0),
                    'screen' => 'messages',
                ],
            ],
        ];
    }
}
