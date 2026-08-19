<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyCommunicationWebhookSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = (string) (config('services.communication_webhook.secret') ?? '');

        if (! app()->isProduction()) {
            if ($secret === '') {
                return $next($request);
            }
        } else {
            if ($secret === '') {
                return response()->json(['message' => 'Webhook secret is not configured.'], 503);
            }
        }

        $signature = (string) $request->header('X-Webhook-Signature', '');
        if ($signature === '') {
            return response()->json(['message' => 'Missing webhook signature.'], 401);
        }

        $expected = hash_hmac('sha256', (string) $request->getContent(), $secret);
        if (! hash_equals($expected, $signature)) {
            return response()->json(['message' => 'Invalid webhook signature.'], 401);
        }

        return $next($request);
    }
}
