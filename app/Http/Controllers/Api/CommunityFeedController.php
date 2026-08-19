<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CommunityComment;
use App\Models\CommunityPost;
use App\Models\CommunityReaction;
use App\Services\CommunityNotificationService;
use Illuminate\Http\Request;

class CommunityFeedController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($user && $user->hasAnyRole(['user', 'client', 'coach', 'admin']), 403);

        $posts = CommunityPost::query()
            ->where('is_visible', true)
            ->with(['user:id,name', 'comments' => fn ($q) => $q->with('user:id,name')->latest()->limit(5), 'reactions'])
            ->withCount(['comments', 'reactions'])
            ->latest()
            ->paginate(15);

        $mapped = $posts->getCollection()->map(fn (CommunityPost $post) => $this->postPayload($post, $user));

        return response()->json([
            'date' => now()->toDateString(),
            'screen' => [
                'title' => 'المجتمع',
                'subtitle' => 'تواصل وتفاعل مع الأعضاء',
                'compose_title' => 'شارك تقدّمك',
                'compose_placeholder' => 'اكتب منشوراً...',
                'publish_label' => 'نشر',
                'comment_placeholder' => 'أضف تعليقاً...',
                'send_comment_label' => 'إرسال',
                'empty_label' => 'لا توجد منشورات بعد. كن أول من يشارك!',
                'react_label' => 'إعجاب',
            ],
            'posts' => $mapped->values(),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
                'has_more' => $posts->hasMorePages(),
            ],
            'actions' => [
                'list_url' => url('/api/v1/community/posts'),
                'create_url' => url('/api/v1/community/posts'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        abort_unless($user->hasAnyRole(['user', 'client', 'coach', 'admin']), 403);

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $post = CommunityPost::create([
            'user_id' => $user->id,
            'content' => trim($validated['content']),
            'is_visible' => true,
        ]);

        $post->load(['user:id,name', 'comments.user:id,name', 'reactions'])
            ->loadCount(['comments', 'reactions']);

        return response()->json([
            'status' => 'ok',
            'message' => 'تم نشر المنشور.',
            'post' => $this->postPayload($post, $user),
        ], 201);
    }

    public function react(Request $request, CommunityPost $post)
    {
        $user = $request->user();
        abort_unless($user->hasAnyRole(['user', 'client', 'coach', 'admin']), 403);

        $validated = $request->validate([
            'reaction' => 'nullable|string|max:30',
        ]);

        $existing = CommunityReaction::query()
            ->where('post_id', $post->id)
            ->where('user_id', $user->id)
            ->first();

        // Toggle: نفس التفاعل مرة ثانية يلغيه.
        if ($existing && ($validated['reaction'] ?? 'like') === $existing->reaction) {
            $existing->delete();
            $reacted = false;
            $reactionValue = null;
        } else {
            $reaction = CommunityReaction::updateOrCreate(
                ['post_id' => $post->id, 'user_id' => $user->id],
                ['reaction' => $validated['reaction'] ?? 'like']
            );
            $reacted = true;
            $reactionValue = $reaction->reaction;
        }

        app(CommunityNotificationService::class)->notifyPostOwnerOfReaction($post, $user, $reacted);

        $post->load(['user:id,name', 'comments.user:id,name', 'reactions'])
            ->loadCount(['comments', 'reactions']);

        return response()->json([
            'status' => 'ok',
            'message' => $reacted ? 'تم تسجيل الإعجاب.' : 'تم إلغاء الإعجاب.',
            'reacted' => $reacted,
            'reaction' => $reactionValue,
            'post' => $this->postPayload($post, $user),
        ]);
    }

    public function comment(Request $request, CommunityPost $post)
    {
        $user = $request->user();
        abort_unless($user->hasAnyRole(['user', 'client', 'coach', 'admin']), 403);

        $validated = $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $comment = CommunityComment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'content' => trim($validated['content']),
        ]);

        app(CommunityNotificationService::class)->notifyPostOwnerOfComment(
            $post,
            $user,
            $comment->content
        );

        $post->load(['user:id,name', 'comments' => fn ($q) => $q->with('user:id,name')->latest()->limit(5), 'reactions'])
            ->loadCount(['comments', 'reactions']);

        return response()->json([
            'status' => 'ok',
            'message' => 'تم إضافة التعليق.',
            'comment' => $this->commentPayload($comment->load('user:id,name')),
            'post' => $this->postPayload($post, $user),
        ], 201);
    }

    /**
     * @return array<string, mixed>
     */
    private function postPayload(CommunityPost $post, $viewer): array
    {
        $viewerReaction = $post->relationLoaded('reactions')
            ? $post->reactions->firstWhere('user_id', $viewer->id)
            : null;

        $comments = $post->relationLoaded('comments')
            ? $post->comments->take(5)->map(fn (CommunityComment $c) => $this->commentPayload($c))->values()->all()
            : [];

        return [
            'id' => $post->id,
            'content' => $post->content,
            'author' => [
                'id' => $post->user?->id,
                'name' => $post->user?->name ?? 'عضو',
                'avatar_url' => $post->user?->profile_photo_url,
            ],
            'created_at' => optional($post->created_at)->toIso8601String(),
            'created_at_label' => $this->relativeTimeLabel($post->created_at),
            'reactions_count' => (int) ($post->reactions_count ?? $post->reactions->count()),
            'comments_count' => (int) ($post->comments_count ?? $post->comments->count()),
            'reactions_label' => '❤️ '.(int) ($post->reactions_count ?? $post->reactions->count()),
            'comments_label' => '💬 '.(int) ($post->comments_count ?? $post->comments->count()),
            'viewer_has_reacted' => $viewerReaction !== null,
            'viewer_reaction' => $viewerReaction?->reaction,
            'comments' => $comments,
            'actions' => [
                'react_url' => url('/api/v1/community/posts/'.$post->id.'/react'),
                'comment_url' => url('/api/v1/community/posts/'.$post->id.'/comment'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function commentPayload(CommunityComment $comment): array
    {
        return [
            'id' => $comment->id,
            'content' => $comment->content,
            'author' => [
                'id' => $comment->user?->id,
                'name' => $comment->user?->name ?? 'عضو',
            ],
            'created_at' => optional($comment->created_at)->toIso8601String(),
            'created_at_label' => $this->relativeTimeLabel($comment->created_at),
            'display_line' => ($comment->user?->name ?? 'عضو').': '.$comment->content,
        ];
    }

    private function relativeTimeLabel($sentAt): ?string
    {
        if (! $sentAt) {
            return null;
        }

        $carbon = \Carbon\Carbon::parse($sentAt);
        $minutes = (int) $carbon->diffInMinutes(now());

        if ($minutes < 1) {
            return 'الآن';
        }
        if ($minutes < 60) {
            return "منذ {$minutes} دقيقة";
        }

        $hours = (int) $carbon->diffInHours(now());
        if ($hours < 24) {
            return $hours === 1 ? 'منذ ساعة' : "منذ {$hours} ساعة";
        }

        $days = (int) $carbon->diffInDays(now());
        if ($days === 1) {
            return 'منذ يوم';
        }
        if ($days < 7) {
            return "منذ {$days} أيام";
        }

        return $carbon->format('d/m/Y');
    }
}
