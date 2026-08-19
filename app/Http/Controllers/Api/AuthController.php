<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Tenant\TenantDiscoveryService;
use App\Services\TenantService;
use App\Services\WhatsAppOtpService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $this->ensureTenantResolved();

        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:120',
        ]);

        /** @var User|null $user */
        $user = User::query()->where('email', $validated['email'])->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['البريد الإلكتروني غير مسجل.'],
            ]);
        }

        if (! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['كلمة المرور غير صحيحة.'],
            ]);
        }

        if (! $user->hasTraineeRole() && ! $user->hasAnyRole(['admin', 'coach', 'user', 'client'])) {
            throw ValidationException::withMessages([
                'email' => ['هذا الحساب غير مفعّل للدخول إلى التطبيق.'],
            ]);
        }

        return $this->issueTokenResponse($user, $validated['device_name'] ?? null, $request);
    }

    public function forgotPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::query()->where('email', $validated['email'])->first();
        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['البريد الإلكتروني غير مسجل.'],
            ]);
        }

        $status = Password::broker()->sendResetLink([
            'email' => $validated['email'],
        ]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني.',
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $status = Password::broker()->reset(
            [
                'email' => $validated['email'],
                'password' => $validated['password'],
                'password_confirmation' => $request->input('password_confirmation'),
                'token' => $validated['token'],
            ],
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'تم تحديث كلمة المرور بنجاح. يمكنك تسجيل الدخول الآن.',
        ]);
    }

    public function requestWhatsappOtp(Request $request, WhatsAppOtpService $otpService)
    {
        $validated = $request->validate([
            'phone' => 'required|string|min:8|max:20',
        ]);

        $user = $otpService->findUserByPhone($validated['phone']);
        if (! $user) {
            throw ValidationException::withMessages([
                'phone' => ['رقم الجوال غير مسجل.'],
            ]);
        }

        if (! $user->hasTraineeRole() && ! $user->hasAnyRole(['admin', 'coach', 'user', 'client'])) {
            throw ValidationException::withMessages([
                'phone' => ['هذا الحساب غير مفعّل للدخول إلى التطبيق.'],
            ]);
        }

        $result = $otpService->sendLoginOtp($user, $validated['phone']);

        return response()->json([
            'status' => 'ok',
            'message' => 'تم إرسال رمز التحقق عبر واتساب.',
            ...$result,
        ]);
    }

    public function verifyWhatsappOtp(Request $request, WhatsAppOtpService $otpService)
    {
        $validated = $request->validate([
            'phone' => 'required|string|min:8|max:20',
            'code' => 'required|string|min:4|max:8',
            'device_name' => 'nullable|string|max:120',
        ]);

        $user = $otpService->verifyLoginOtp($validated['phone'], $validated['code']);
        if (! $user) {
            throw ValidationException::withMessages([
                'code' => ['رمز التحقق غير صحيح أو منتهي الصلاحية.'],
            ]);
        }

        return $this->issueTokenResponse($user, $validated['device_name'] ?? null, $request);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $this->userPayload($request->user()),
        ]);
    }

    public function logout(Request $request)
    {
        $token = $request->user()?->currentAccessToken();
        if ($token) {
            $token->delete();
        }

        return response()->json(['status' => 'ok']);
    }

    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['status' => 'ok']);
    }

    private function issueTokenResponse(User $user, ?string $deviceName, Request $request)
    {
        $deviceName = $deviceName ?: ($request->userAgent() ?: 'mobile-app');
        $user->tokens()->where('name', $deviceName)->delete();
        $token = $user->createToken($deviceName, ['mobile'])->plainTextToken;

        $payload = [
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->userPayload($user),
            'bootstrap_url' => route('api.v1.mobile.bootstrap'),
        ];

        $tenant = TenantService::getTenant();
        if ($tenant) {
            $payload['organization'] = app(TenantDiscoveryService::class)->toPublicPayload($tenant);
        }

        return response()->json($payload);
    }

    private function ensureTenantResolved(): void
    {
        if (TenantService::getTenant()) {
            return;
        }

        abort(response()->json([
            'message' => 'لم يتم التعرف على المستأجر. أرسل ترويسة X-Tenant-Domain بقيمة دومين المستأجر (مثال: app3.cmsglobals.test).',
            'error' => 'tenant_not_resolved',
        ], 400));
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'roles' => $user->getRoleNames()->values(),
            'coach_id' => $user->coach_id ?? null,
            'is_trainee' => $user->hasTraineeRole(),
            'profile_photo_url' => $user->profile_photo_url ?? null,
        ];
    }
}
