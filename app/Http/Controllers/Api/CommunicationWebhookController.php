<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IntegrationWebhookLog;
use Illuminate\Http\Request;

class CommunicationWebhookController extends Controller
{
    public function __invoke(Request $request, string $provider)
    {
        $payload = $request->all();

        $log = IntegrationWebhookLog::create([
            'provider' => $provider,
            'event_type' => $payload['event'] ?? $payload['type'] ?? 'unknown',
            'payload' => $payload,
            'status_code' => 202,
            'reference' => $payload['id'] ?? $payload['message_id'] ?? null,
        ]);

        return response()->json(['status' => 'accepted', 'log_id' => $log->id], 202);
    }
}
