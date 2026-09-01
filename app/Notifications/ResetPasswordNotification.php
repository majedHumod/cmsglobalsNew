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
            ->greeting('مرحباً!')
            ->line('تلقّينا طلباً لإعادة تعيين كلمة المرور لحسابك في '.$siteName.'.')
            ->action('تعيين كلمة المرور', $url)
            ->line('رابط إعادة التعيين صالح لمدة '.$expire.' دقيقة.')
            ->line('إذا لم تطلب إعادة تعيين كلمة المرور، يمكنك تجاهل هذا البريد.')
            ->salutation("مع التحية،\n".$siteName);
    }
}
