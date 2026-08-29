<?php

namespace App\Notifications;

use App\Services\Mail\TenantMailBrandingService;
use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends BaseResetPassword
{
    protected function buildMailMessage($url): MailMessage
    {
        $branding = app(TenantMailBrandingService::class);
        $siteName = $branding->displayName();
        $expire = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

        return (new MailMessage)
            ->subject($branding->subject('إعادة تعيين كلمة المرور'))
            ->view('emails.reset-password', [
                'url' => $url,
                'siteName' => $siteName,
                'expireMinutes' => $expire,
            ]);
    }
}
