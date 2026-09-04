<?php

namespace App\Listeners;

use App\Mail\WelcomeTraineeMail;
use App\Models\User;
use App\Services\Mail\TenantMailBrandingService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWelcomeTraineeMail
{
    public function handle(Registered $event): void
    {
        $user = $event->user;
        if (! $user instanceof User) {
            return;
        }

        // Only trainees/clients — not platform tenant provisioning admins on system host.
        $branding = app(TenantMailBrandingService::class);
        if ($branding->isPlatformHostRequest() && ! $branding->isClubAudience()) {
            return;
        }

        if (method_exists($user, 'hasAnyRole')) {
            try {
                if ($user->hasAnyRole(['admin']) && ! $user->hasAnyRole(['user', 'client'])) {
                    return;
                }
            } catch (\Throwable) {
                // roles table may not be ready in edge cases
            }
        }

        try {
            Mail::to($user->email)->send(WelcomeTraineeMail::forUser($user));
        } catch (\Throwable $e) {
            report($e);
            Log::warning('welcome_trainee_mail_failed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
