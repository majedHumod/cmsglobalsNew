<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\NotificationController as ApiNotificationController;
use App\Models\NotificationFeed;
use App\Services\NotificationFeedService;
use Illuminate\Http\Request;

class ClientNotificationWebController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'trainee']);
    }

    public function index(Request $request)
    {
        $notifications = NotificationFeed::query()
            ->where('user_id', $request->user()->id)
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

        return view('client.notifications.index', compact('notifications'));
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

    public function markAsRead(Request $request, NotificationFeed $notification, NotificationFeedService $service)
    {
        abort_unless((int) $notification->user_id === (int) $request->user()->id, 403);
        if (! $notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        if ($request->expectsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return back()->with('success', 'تم تحديث الإشعار.');
    }

    public function markAllAsRead(Request $request, NotificationFeedService $service)
    {
        $service->markAllAsRead($request->user());

        if ($request->expectsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return back()->with('success', 'تم تحديد جميع الإشعارات كمقروءة.');
    }
}
