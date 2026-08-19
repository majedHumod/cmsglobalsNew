<?php

namespace App\Http\Controllers;

use App\Models\NotificationFeed;
use App\Services\NotificationFeedService;
use Illuminate\Http\Request;

class NotificationCenterController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|coach|user|client']);
    }

    public function index(Request $request)
    {
        $notifications = NotificationFeed::query()
            ->where('user_id', $request->user()->id)
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')))
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

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Request $request, NotificationFeed $notification)
    {
        abort_unless((int) $notification->user_id === (int) $request->user()->id, 403);
        if (! $notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        return back()->with('success', 'تم تحديث الإشعار.');
    }

    public function markAllAsRead(Request $request, NotificationFeedService $service)
    {
        $service->markAllAsRead($request->user());
        return back()->with('success', 'تم تحديد جميع الإشعارات كمقروءة.');
    }
}
