<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\NotificationResource;
use App\Models\NotificationFeed;
use App\Services\NotificationFeedService;
use App\Services\NotificationPreferenceService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = NotificationFeed::query()
            ->where('user_id', $request->user()->id)
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')))
            ->when($request->filled('category'), function ($query) use ($request) {
                $category = (string) $request->string('category');
                $types = collect(config('communication.notification_types', []))
                    ->filter(fn (array $meta) => ($meta['category'] ?? null) === $category)
                    ->keys()
                    ->all();
                $query->whereIn('type', $types !== [] ? $types : ['__none__']);
            })
            ->when($request->filled('channel'), function ($query) use ($request) {
                $channel = (string) $request->string('channel');
                $types = collect(config('communication.notification_types', []))
                    ->filter(fn (array $meta) => ($meta['channel'] ?? null) === $channel)
                    ->keys()
                    ->all();
                $query->whereIn('type', $types !== [] ? $types : ['__none__']);
            })
            ->when($request->filled('state'), function ($query) use ($request) {
                if ($request->string('state') === 'unread') {
                    $query->whereNull('read_at');
                }
                if ($request->string('state') === 'read') {
                    $query->whereNotNull('read_at');
                }
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        return NotificationResource::collection($notifications);
    }

    public function unreadCount(Request $request)
    {
        return response()->json([
            'count' => NotificationFeed::query()
                ->where('user_id', $request->user()->id)
                ->whereNull('read_at')
                ->count(),
        ]);
    }

    public function markAsRead(Request $request, NotificationFeed $notification)
    {
        abort_unless((int) $notification->user_id === (int) $request->user()->id, 403);
        if (! $notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        return response()->json(['status' => 'ok']);
    }

    public function markAllAsRead(Request $request, NotificationFeedService $service)
    {
        $service->markAllAsRead($request->user());

        return response()->json(['status' => 'ok']);
    }

    public function preferences(Request $request, NotificationPreferenceService $prefs)
    {
        return response()->json([
            'data' => $prefs->forUser($request->user()),
        ]);
    }

    public function updatePreferences(Request $request, NotificationPreferenceService $prefs)
    {
        $validated = $request->validate([
            'preferences' => 'required|array',
        ]);

        $updated = $prefs->updateForUser($request->user(), $validated['preferences']);

        return response()->json([
            'status' => 'ok',
            'data' => $updated,
        ]);
    }
}
