<?php

namespace App\Services\Platform;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Operational alerts for the platform owner (failed provisioning, pool running low, stuck payments).
 * Always logs; emails the configured recipients at most once per dedupe window.
 */
class OpsAlert
{
    /**
     * @param  array<string, mixed>  $context
     */
    public static function critical(string $subject, string $body, ?string $dedupeKey = null, int $dedupeMinutes = 60, array $context = []): void
    {
        Log::critical('[ops-alert] '.$subject, ['body' => $body] + $context);

        if ($dedupeKey !== null && ! Cache::add('ops-alert:'.$dedupeKey, 1, now()->addMinutes($dedupeMinutes))) {
            return;
        }

        $recipients = self::recipients();
        if ($recipients === []) {
            return;
        }

        try {
            Mail::raw($body."\n\n— EtosCoach Ops", function ($message) use ($recipients, $subject) {
                $message->to($recipients)->subject('[EtosCoach] '.$subject);
            });
        } catch (\Throwable $e) {
            Log::error('ops-alert mail failed', ['message' => $e->getMessage()]);
        }
    }

    /**
     * @return list<string>
     */
    public static function recipients(): array
    {
        $emails = (array) config('platform.alert_emails', []);
        if ($emails === []) {
            $emails = (array) config('platform.owner_emails', []);
        }

        return array_values(array_filter($emails));
    }
}
