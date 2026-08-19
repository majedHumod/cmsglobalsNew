<?php

namespace App\Services;

use App\Models\CommunityPost;
use App\Models\User;

class CommunityNotificationService
{
    public function notifyPostOwnerOfComment(CommunityPost $post, User $actor, string $commentExcerpt): void
    {
        if ((int) $post->user_id === (int) $actor->id) {
            return;
        }

        app(NotificationFeedService::class)->pushToUser(
            (int) $post->user_id,
            'community.comment',
            'تعليق جديد على منشورك',
            $actor->name.': '.mb_substr($commentExcerpt, 0, 120),
            [
                'post_id' => $post->id,
                'actor_user_id' => $actor->id,
            ],
            true
        );
    }

    public function notifyPostOwnerOfReaction(CommunityPost $post, User $actor, bool $reacted): void
    {
        if (! $reacted || (int) $post->user_id === (int) $actor->id) {
            return;
        }

        app(NotificationFeedService::class)->pushToUser(
            (int) $post->user_id,
            'community.reaction',
            'تفاعل جديد على منشورك',
            'أعجب '.$actor->name.' بمنشورك.',
            [
                'post_id' => $post->id,
                'actor_user_id' => $actor->id,
            ],
            true
        );
    }
}
