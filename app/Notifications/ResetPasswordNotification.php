<?php

namespace App\Notifications;

use App\Services\Mail\TenantMailBrandingService;
use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends BaseResetPassword
{
    protected function buildMailMessage($url): MailMessage
    {
        app()->setLocale('ar');

        $branding = app(TenantMailBrandingService::class);
        $siteName = $branding->displayName();
        $audience = $branding->audience();
        $expire = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

        $accountLabel = $audience === TenantMailBrandingService::AUDIENCE_PLATFORM
            ? 'حسابك في '.$siteName
            : 'حسابك في منصة '.$siteName;

        return (new MailMessage)
            ->subject($branding->subject('إعادة تعيين كلمة المرور'))
            ->from(
                (string) config('mail.from.address', 'no-reply@etoscoach.com'),
                $branding->fromName()
            )
            ->view('emails.reset-password', [
                'url' => $url,
                'siteName' => $siteName,
                'accountLabel' => $accountLabel,
                'expireMinutes' => $expire,
                'isPlatform' => $audience === TenantMailBrandingService::AUDIENCE_PLATFORM,
            ]);
    }
}
