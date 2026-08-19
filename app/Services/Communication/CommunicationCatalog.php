<?php

namespace App\Services\Communication;

class CommunicationCatalog
{
    /**
     * @return array<string, mixed>
     */
    public function catalog(): array
    {
        return [
            'version' => 1,
            'channels' => array_values(config('communication.channels', [])),
            'transports' => array_values(config('communication.transports', [])),
            'notification_types' => collect(config('communication.notification_types', []))
                ->map(fn (array $meta, string $type) => array_merge(['type' => $type], $meta))
                ->values()
                ->all(),
            'categories' => [
                ['key' => 'message', 'label_ar' => 'رسائل'],
                ['key' => 'booking', 'label_ar' => 'حجوزات'],
                ['key' => 'membership', 'label_ar' => 'عضوية'],
                ['key' => 'habit', 'label_ar' => 'عادات'],
                ['key' => 'checkin', 'label_ar' => 'متابعة'],
                ['key' => 'community', 'label_ar' => 'مجتمع'],
                ['key' => 'system', 'label_ar' => 'نظام'],
            ],
        ];
    }

    /**
     * @return array{category:string,channel:string,screen:string,priority:string,label_ar:string}
     */
    public function metaForType(string $type): array
    {
        $defaults = config('communication.default_notification_meta', []);
        $specific = config('communication.notification_types.'.$type, []);

        return array_merge($defaults, $specific);
    }

    /**
     * Enrich a notification payload with stable mobile navigation hints.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function enrichPayload(string $type, array $payload = []): array
    {
        $meta = $this->metaForType($type);

        $action = $payload['action'] ?? [
            'screen' => $meta['screen'],
            'channel' => $meta['channel'],
            'type' => $type,
        ];

        if (! empty($payload['conversation_id'])) {
            $action['conversation_id'] = (int) $payload['conversation_id'];
            $action['screen'] = 'messages';
        }

        if (! empty($payload['broadcast_id'])) {
            $action['broadcast_id'] = (int) $payload['broadcast_id'];
        }

        if (! empty($payload['booking_id'])) {
            $action['booking_id'] = (int) $payload['booking_id'];
            $action['screen'] = 'bookings';
        }

        if (! empty($payload['check_in_id'])) {
            $action['check_in_id'] = (int) $payload['check_in_id'];
            $action['screen'] = 'checkin';
        }

        if (! empty($payload['habit_id'])) {
            $action['habit_id'] = (int) $payload['habit_id'];
            $action['screen'] = 'habits';
        }

        if (! empty($payload['post_id'])) {
            $action['post_id'] = (int) $payload['post_id'];
            $action['screen'] = 'community';
        }

        return array_merge($payload, [
            'action' => $action,
            'channel' => $meta['channel'],
            'category' => $meta['category'],
            'priority' => $meta['priority'],
            'screen' => $action['screen'] ?? $meta['screen'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function presentNotification(string $type, ?array $payload = null): array
    {
        $meta = $this->metaForType($type);
        $enriched = $this->enrichPayload($type, $payload ?? []);

        return [
            'category' => $meta['category'],
            'channel' => $meta['channel'],
            'screen' => $enriched['screen'] ?? $meta['screen'],
            'priority' => $meta['priority'],
            'label_ar' => $meta['label_ar'],
            'action' => $enriched['action'] ?? null,
        ];
    }
}
