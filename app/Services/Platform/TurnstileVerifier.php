<?php

namespace App\Services\Platform;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Optional Cloudflare Turnstile verification. Disabled (always passes) when no secret key is configured.
 */
class TurnstileVerifier
{
    public function enabled(): bool
    {
        return trim((string) config('services.turnstile.secret_key')) !== ''
            && trim((string) config('services.turnstile.site_key')) !== '';
    }

    public function siteKey(): string
    {
        return $this->enabled() ? (string) config('services.turnstile.site_key') : '';
    }

    public function passes(?string $token, ?string $ip = null): bool
    {
        if (! $this->enabled()) {
            return true;
        }

        if (! is_string($token) || trim($token) === '') {
            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout(8)
                ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', array_filter([
                    'secret' => config('services.turnstile.secret_key'),
                    'response' => $token,
                    'remoteip' => $ip,
                ]));

            return $response->ok() && (bool) $response->json('success');
        } catch (\Throwable $e) {
            // Fail closed: if we cannot verify, do not let automated traffic through.
            Log::warning('turnstile verification failed', ['message' => $e->getMessage()]);

            return false;
        }
    }
}
