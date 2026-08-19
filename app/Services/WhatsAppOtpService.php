<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class WhatsAppOtpService
{
    public function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?: '';

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        // Saudi local format 05xxxxxxxx → 9665xxxxxxxx
        if (str_starts_with($digits, '0') && strlen($digits) === 10) {
            $digits = '966' . substr($digits, 1);
        }

        // Missing country code with 9 digits starting with 5
        if (strlen($digits) === 9 && str_starts_with($digits, '5')) {
            $digits = '966' . $digits;
        }

        return '+' . ltrim($digits, '+');
    }

    public function findUserByPhone(string $phone): ?User
    {
        $normalized = $this->normalizePhone($phone);

        return User::query()
            ->where('phone', $normalized)
            ->orWhere('phone', ltrim($normalized, '+'))
            ->orWhere('phone', $phone)
            ->first();
    }

    /**
     * @return array{status: string, expires_in: int, debug_code?: string}
     */
    public function sendLoginOtp(User $user, string $phone): array
    {
        $normalized = $this->normalizePhone($phone);
        $rateKey = 'whatsapp_otp_rate:' . $normalized;

        if (Cache::has($rateKey)) {
            throw new HttpResponseException(response()->json([
                'message' => 'يرجى الانتظار قبل طلب رمز جديد.',
                'errors' => ['phone' => ['يرجى الانتظار قبل طلب رمز جديد.']],
            ], 429));
        }

        $code = (string) random_int(100000, 999999);
        $ttl = (int) config('services.whatsapp.otp_ttl', 300);

        Cache::put('whatsapp_otp:' . $normalized, [
            'code_hash' => hash('sha256', $code),
            'user_id' => $user->id,
            'attempts' => 0,
        ], $ttl);

        Cache::put($rateKey, true, (int) config('services.whatsapp.otp_resend_seconds', 60));

        $message = 'رمز التحقق لتطبيق المتدرب: ' . $code . ' صالح لمدة ' . (int) ceil($ttl / 60) . ' دقائق.';
        $sent = $this->dispatchWhatsApp($normalized, $message);

        $payload = [
            'status' => $sent ? 'sent' : 'queued_log',
            'expires_in' => $ttl,
            'phone' => $this->maskPhone($normalized),
        ];

        if (config('services.whatsapp.otp_debug')) {
            $payload['debug_code'] = $code;
        }

        return $payload;
    }

    public function verifyLoginOtp(string $phone, string $code): ?User
    {
        $normalized = $this->normalizePhone($phone);
        $cacheKey = 'whatsapp_otp:' . $normalized;
        $payload = Cache::get($cacheKey);

        if (! is_array($payload)) {
            return null;
        }

        $attempts = (int) ($payload['attempts'] ?? 0);
        if ($attempts >= 5) {
            Cache::forget($cacheKey);

            return null;
        }

        $payload['attempts'] = $attempts + 1;
        Cache::put($cacheKey, $payload, (int) config('services.whatsapp.otp_ttl', 300));

        if (! hash_equals($payload['code_hash'] ?? '', hash('sha256', $code))) {
            return null;
        }

        Cache::forget($cacheKey);

        return User::query()->find($payload['user_id'] ?? null);
    }

    protected function dispatchWhatsApp(string $toE164, string $message): bool
    {
        $driver = config('services.whatsapp.driver', 'log');

        if ($driver === 'twilio') {
            $sid = config('services.twilio.sid');
            $token = config('services.twilio.token');
            $from = config('services.whatsapp.from');

            if (! $sid || ! $token || ! $from || ! class_exists(Client::class)) {
                Log::warning('WhatsApp OTP skipped: Twilio is not fully configured.', [
                    'to' => $toE164,
                ]);
                Log::info('WhatsApp OTP (fallback log)', ['to' => $toE164, 'message' => $message]);

                return false;
            }

            $client = new Client($sid, $token);
            $client->messages->create('whatsapp:' . $toE164, [
                'from' => str_starts_with($from, 'whatsapp:') ? $from : 'whatsapp:' . $from,
                'body' => $message,
            ]);

            return true;
        }

        Log::info('WhatsApp OTP (log driver)', [
            'to' => $toE164,
            'message' => $message,
        ]);

        return false;
    }

    protected function maskPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?: '';
        if (strlen($digits) < 4) {
            return '****';
        }

        return str_repeat('*', max(0, strlen($digits) - 4)) . substr($digits, -4);
    }
}
