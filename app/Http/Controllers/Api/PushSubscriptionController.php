<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PushSubscription;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => 'required|string|max:1024',
            'keys.p256dh' => 'nullable|string',
            'keys.auth' => 'nullable|string',
            'payload' => 'nullable|array',
        ]);

        $subscription = PushSubscription::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'endpoint' => $validated['endpoint'],
            ],
            [
                'public_key' => $validated['keys']['p256dh'] ?? null,
                'auth_token' => $validated['keys']['auth'] ?? null,
                'payload' => $validated['payload'] ?? null,
                'last_seen_at' => now(),
            ]
        );

        return response()->json(['status' => 'ok', 'id' => $subscription->id]);
    }

    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => 'required|string|max:1024',
        ]);

        PushSubscription::query()
            ->where('user_id', $request->user()->id)
            ->where('endpoint', $validated['endpoint'])
            ->delete();

        return response()->json(['status' => 'ok']);
    }
}
