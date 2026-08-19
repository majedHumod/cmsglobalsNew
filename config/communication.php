<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Communication channels (logical product channels)
    |--------------------------------------------------------------------------
    */
    'channels' => [
        'dm' => [
            'key' => 'dm',
            'label_ar' => 'رسائل خاصة',
            'label_en' => 'Direct messages',
            'screen' => 'messages',
        ],
        'notification' => [
            'key' => 'notification',
            'label_ar' => 'إشعارات',
            'label_en' => 'Notifications',
            'screen' => 'notifications',
        ],
        'broadcast' => [
            'key' => 'broadcast',
            'label_ar' => 'بث جماعي',
            'label_en' => 'Broadcast',
            'screen' => 'messages',
        ],
        'community' => [
            'key' => 'community',
            'label_ar' => 'المجتمع',
            'label_en' => 'Community',
            'screen' => 'community',
        ],
        'system' => [
            'key' => 'system',
            'label_ar' => 'نظام',
            'label_en' => 'System',
            'screen' => 'notifications',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Delivery transports (how a signal leaves the server)
    |--------------------------------------------------------------------------
    */
    'transports' => [
        'in_app' => ['key' => 'in_app', 'label_ar' => 'داخل التطبيق'],
        'web_push' => ['key' => 'web_push', 'label_ar' => 'تنبيه جهاز'],
        'realtime' => ['key' => 'realtime', 'label_ar' => 'فوري'],
        'external' => ['key' => 'external', 'label_ar' => 'قناة خارجية'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification type catalog (stable contracts for mobile)
    |--------------------------------------------------------------------------
    | category: booking|membership|habit|checkin|message|community|system
    | channel: which inbox tab the item belongs to
    | screen: mobile screen key from bootstrap
    | priority: low|normal|high
    */
    'notification_types' => [
        'message.received' => [
            'category' => 'message',
            'channel' => 'dm',
            'screen' => 'messages',
            'priority' => 'high',
            'label_ar' => 'رسالة جديدة',
        ],
        'message.broadcast' => [
            'category' => 'message',
            'channel' => 'broadcast',
            'screen' => 'messages',
            'priority' => 'high',
            'label_ar' => 'رسالة جماعية',
        ],
        'booking.created' => [
            'category' => 'booking',
            'channel' => 'notification',
            'screen' => 'bookings',
            'priority' => 'high',
            'label_ar' => 'حجز جديد',
        ],
        'booking.confirmed' => [
            'category' => 'booking',
            'channel' => 'notification',
            'screen' => 'bookings',
            'priority' => 'high',
            'label_ar' => 'تأكيد حجز',
        ],
        'booking.cancelled' => [
            'category' => 'booking',
            'channel' => 'notification',
            'screen' => 'bookings',
            'priority' => 'high',
            'label_ar' => 'إلغاء حجز',
        ],
        'booking.rescheduled' => [
            'category' => 'booking',
            'channel' => 'notification',
            'screen' => 'bookings',
            'priority' => 'high',
            'label_ar' => 'إعادة جدولة',
        ],
        'booking.updated' => [
            'category' => 'booking',
            'channel' => 'notification',
            'screen' => 'bookings',
            'priority' => 'normal',
            'label_ar' => 'تحديث حجز',
        ],
        'booking.status_updated' => [
            'category' => 'booking',
            'channel' => 'notification',
            'screen' => 'bookings',
            'priority' => 'normal',
            'label_ar' => 'تحديث حالة حجز',
        ],
        'checkin.submitted' => [
            'category' => 'checkin',
            'channel' => 'notification',
            'screen' => 'checkin',
            'priority' => 'normal',
            'label_ar' => 'متابعة جديدة',
        ],
        'checkin.missing_7' => [
            'category' => 'checkin',
            'channel' => 'notification',
            'screen' => 'checkin',
            'priority' => 'normal',
            'label_ar' => 'تذكير متابعة',
        ],
        'checkin.missing_14' => [
            'category' => 'checkin',
            'channel' => 'notification',
            'screen' => 'checkin',
            'priority' => 'high',
            'label_ar' => 'تذكير متابعة متأخر',
        ],
        'membership.activated' => [
            'category' => 'membership',
            'channel' => 'notification',
            'screen' => 'home',
            'priority' => 'high',
            'label_ar' => 'تفعيل عضوية',
        ],
        'membership.expiring_0' => [
            'category' => 'membership',
            'channel' => 'notification',
            'screen' => 'home',
            'priority' => 'high',
            'label_ar' => 'انتهاء عضوية اليوم',
        ],
        'membership.expiring_3' => [
            'category' => 'membership',
            'channel' => 'notification',
            'screen' => 'home',
            'priority' => 'high',
            'label_ar' => 'اقتراب انتهاء عضوية',
        ],
        'membership.expiring_7' => [
            'category' => 'membership',
            'channel' => 'notification',
            'screen' => 'home',
            'priority' => 'normal',
            'label_ar' => 'تذكير تجديد عضوية',
        ],
        'habit.coach_updated' => [
            'category' => 'habit',
            'channel' => 'notification',
            'screen' => 'habits',
            'priority' => 'normal',
            'label_ar' => 'تحديث عادة',
        ],
        'habit.missed_streak' => [
            'category' => 'habit',
            'channel' => 'notification',
            'screen' => 'habits',
            'priority' => 'normal',
            'label_ar' => 'تذكير عادات',
        ],
        'coach.client_at_risk' => [
            'category' => 'system',
            'channel' => 'notification',
            'screen' => 'messages',
            'priority' => 'high',
            'label_ar' => 'عميل بحاجة متابعة',
        ],
        'community.comment' => [
            'category' => 'community',
            'channel' => 'community',
            'screen' => 'community',
            'priority' => 'normal',
            'label_ar' => 'تعليق جديد',
        ],
        'community.reaction' => [
            'category' => 'community',
            'channel' => 'community',
            'screen' => 'community',
            'priority' => 'low',
            'label_ar' => 'تفاعل جديد',
        ],
    ],

    'default_notification_meta' => [
        'category' => 'system',
        'channel' => 'notification',
        'screen' => 'notifications',
        'priority' => 'normal',
        'label_ar' => 'إشعار',
    ],
];
