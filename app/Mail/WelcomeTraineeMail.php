<?php

namespace App\Mail;

use App\Models\User;
use App\Services\Mail\TenantMailBrandingService;
use App\Services\TenantService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Club → trainee welcome (always club/coach branded, never EtosCoach).
 */
class WelcomeTraineeMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $clubName;

    public string $clubUrl;

    public string $traineeName;

    public string $traineeEmail;

    public function __construct(
        string $clubName,
        string $clubUrl,
        string $traineeName,
        string $traineeEmail,
        public ?string $loginUrl = null,
    ) {
        app()->setLocale('ar');
        app(TenantMailBrandingService::class)->forceAudience(TenantMailBrandingService::AUDIENCE_CLUB);

        $this->clubName = $clubName;
        $this->clubUrl = rtrim($clubUrl, '/');
        $this->traineeName = $traineeName;
        $this->traineeEmail = $traineeEmail;
        $this->loginUrl = $loginUrl ?: ($this->clubUrl.'/login');

        config(['app.name' => $this->clubName]);
    }

    public static function forUser(User $user): self
    {
        $branding = app(TenantMailBrandingService::class);
        $branding->forceAudience(TenantMailBrandingService::AUDIENCE_CLUB);

        $tenant = TenantService::getTenant();
        $clubName = $branding->displayName($tenant);

        $scheme = app()->environment('local') ? 'http://' : 'https://';
        if ($tenant?->domain) {
            $clubUrl = $scheme.$tenant->domain.(app()->environment('local') ? ':8000' : '');
        } elseif (request()?->getHost()) {
            $clubUrl = request()->getSchemeAndHttpHost();
        } else {
            $clubUrl = rtrim((string) config('app.url'), '/');
        }

        return new self(
            clubName: $clubName,
            clubUrl: $clubUrl,
            traineeName: (string) ($user->name ?: 'مرحباً'),
            traineeEmail: (string) $user->email,
            loginUrl: rtrim($clubUrl, '/').'/login',
        );
    }

    public function envelope(): Envelope
    {
        $fromAddress = (string) config('mail.from.address', 'no-reply@etoscoach.com');

        return new Envelope(
            from: new Address($fromAddress, $this->clubName),
            subject: 'مرحباً بك في '.$this->clubName,
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.welcome-trainee',
            with: [
                'clubName' => $this->clubName,
                'clubUrl' => $this->clubUrl,
                'loginUrl' => $this->loginUrl,
                'traineeName' => $this->traineeName,
                'traineeEmail' => $this->traineeEmail,
            ],
        );
    }
}
