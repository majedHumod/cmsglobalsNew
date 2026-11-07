<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Services\Billing\SignupReservationService;
use App\Services\Platform\TurnstileVerifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

/**
 * Proves the buyer owns the email before a new-club checkout is created.
 * Step 1: POST /subscribe/email-code   → mails a 6-digit code
 * Step 2: POST /subscribe/email-verify → marks the email as verified for the checkout
 */
class SignupEmailVerificationController extends Controller
{
    private const CODE_TTL_MINUTES = 10;
    private const VERIFIED_TTL_MINUTES = 60;
    private const MAX_ATTEMPTS = 5;
    private const RESEND_SECONDS = 60;

    public function __construct(
        private readonly SignupReservationService $reservations,
        private readonly TurnstileVerifier $turnstile,
    ) {
    }

    public static function verifiedCacheKey(string $email): string
    {
        return 'signup:email_verified:'.self::emailKey($email);
    }

    public static function isVerified(string $email): bool
    {
        return (bool) Cache::get(self::verifiedCacheKey($email));
    }

    public static function forgetVerified(string $email): void
    {
        Cache::forget(self::verifiedCacheKey($email));
    }

    public function send(Request $request): JsonResponse
    {
        if (filled($request->input('website'))) {
            return response()->json(['error' => 'تعذر إرسال الرمز.'], 422);
        }

        $validator = Validator::make($request->all(), ['email' => 'required|email|max:190'], [
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email' => 'أدخل بريداً إلكترونياً صالحاً.',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (! $this->turnstile->passes($request->input('cf-turnstile-response'), $request->ip())) {
            return response()->json(['error' => 'فشل التحقق الأمني. حدّث الصفحة وأعد المحاولة.', 'code' => 'captcha_failed'], 422);
        }

        $email = SignupReservationService::normalizeEmail((string) $request->input('email'));

        if ($this->reservations->emailInUse($email)) {
            return response()->json([
                'error' => 'هذا البريد الإلكتروني مرتبط بنادٍ قائم. سجّل الدخول لتجديد اشتراكك بدلاً من إنشاء نادٍ جديد.',
                'code' => 'email_in_use',
                'login_url' => $this->loginUrl(),
            ], 422);
        }

        if (! Cache::add('signup:email_cooldown:'.self::emailKey($email), 1, now()->addSeconds(self::RESEND_SECONDS))) {
            return response()->json(['error' => 'تم إرسال رمز قبل قليل. انتظر دقيقة ثم أعد الإرسال.', 'code' => 'cooldown'], 429);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Cache::put('signup:email_code:'.self::emailKey($email), [
            'hash' => $this->codeHash($email, $code),
            'attempts' => 0,
        ], now()->addMinutes(self::CODE_TTL_MINUTES));

        try {
            Mail::raw(
                "رمز التحقق الخاص بك لإنشاء نادٍ على EtosCoach هو: {$code}\n\nصالح لمدة ".self::CODE_TTL_MINUTES." دقائق. إذا لم تطلب هذا الرمز فتجاهل الرسالة.",
                function ($message) use ($email) {
                    $message->to($email)->subject('رمز التحقق من بريدك الإلكتروني - EtosCoach');
                }
            );
        } catch (\Throwable $e) {
            report($e);
            Cache::forget('signup:email_cooldown:'.self::emailKey($email));

            return response()->json(['error' => 'تعذر إرسال رسالة التحقق الآن. حاول لاحقاً.'], 422);
        }

        return response()->json(['message' => 'أرسلنا رمز التحقق إلى بريدك. أدخله هنا لإكمال الاشتراك.']);
    }

    public function verify(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:190',
            'code' => 'required|digits:6',
        ], [
            'code.required' => 'أدخل رمز التحقق.',
            'code.digits' => 'رمز التحقق مكوّن من 6 أرقام.',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $email = SignupReservationService::normalizeEmail((string) $request->input('email'));
        $key = 'signup:email_code:'.self::emailKey($email);
        $entry = Cache::get($key);

        if (! is_array($entry)) {
            return response()->json(['error' => 'انتهت صلاحية الرمز. اطلب رمزاً جديداً.', 'code' => 'expired'], 422);
        }

        if (($entry['attempts'] ?? 0) >= self::MAX_ATTEMPTS) {
            Cache::forget($key);

            return response()->json(['error' => 'تجاوزت عدد المحاولات. اطلب رمزاً جديداً.', 'code' => 'too_many_attempts'], 422);
        }

        if (! hash_equals((string) $entry['hash'], $this->codeHash($email, (string) $request->input('code')))) {
            $entry['attempts'] = (int) ($entry['attempts'] ?? 0) + 1;
            Cache::put($key, $entry, now()->addMinutes(self::CODE_TTL_MINUTES));

            return response()->json(['error' => 'رمز التحقق غير صحيح.', 'code' => 'invalid_code'], 422);
        }

        Cache::forget($key);
        Cache::put(self::verifiedCacheKey($email), true, now()->addMinutes(self::VERIFIED_TTL_MINUTES));

        return response()->json(['verified' => true, 'message' => 'تم التحقق من بريدك الإلكتروني.']);
    }

    private static function emailKey(string $email): string
    {
        return hash_hmac('sha256', SignupReservationService::normalizeEmail($email), (string) config('app.key'));
    }

    private function codeHash(string $email, string $code): string
    {
        return hash_hmac('sha256', $email.'|'.$code, (string) config('app.key'));
    }

    private function loginUrl(): string
    {
        $app = rtrim((string) config('platform.app_url'), '/') ?: 'https://app.etoscoach.com';

        return $app.'/account/login?redirect='.urlencode($app.'/subscribe');
    }
}
