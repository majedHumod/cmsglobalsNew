<?php

namespace App\Http\Controllers;

use App\Models\CommunityComment;
use App\Models\CommunityPost;
use App\Models\CommunityReaction;
use App\Services\CommunityNotificationService;
use Illuminate\Http\Request;

class CommunityFeedController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|coach|user|client']);
    }

    public function index()
    {
        $posts = CommunityPost::query()
            ->where('is_visible', true)
            ->with(['user:id,name', 'comments.user:id,name', 'reactions'])
            ->latest()
            ->paginate(20);

        return view('community.index', compact('posts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        CommunityPost::create([
            'user_id' => $request->user()->id,
            'content' => trim($validated['content']),
            'is_visible' => true,
        ]);

        return back()->with('success', 'تم نشر المنشور.');
    }

    public function react(Request $request, CommunityPost $post)
    {
        $validated = $request->validate([
            'reaction' => 'nullable|string|max:30',
        ]);

        $reaction = $validated['reaction'] ?? 'like';
        CommunityReaction::updateOrCreate(
            ['post_id' => $post->id, 'user_id' => $request->user()->id],
            ['reaction' => $reaction]
        );

        app(CommunityNotificationService::class)->notifyPostOwnerOfReaction($post, $request->user(), true);

        return back()->with('success', 'تم تسجيل التفاعل.');
    }

    public function comment(Request $request, CommunityPost $post)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $comment = CommunityComment::create([
            'post_id' => $post->id,
            'user_id' => $request->user()->id,
            'content' => trim($validated['content']),
        ]);

        app(CommunityNotificationService::class)->notifyPostOwnerOfComment(
            $post,
            $request->user(),
            $comment->content
        );

        return back()->with('success', 'تم إضافة التعليق.');
    }
}
