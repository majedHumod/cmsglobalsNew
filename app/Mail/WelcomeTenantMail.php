<?php

namespace App\Mail;

use App\Services\Mail\TenantMailBrandingService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Platform → tenant welcome (always EtosCoach branded).
 */
class WelcomeTenantMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $platformName;

    public function __construct(
        public string $tenantName,
        public string $tenantDomainUrl,
        public string $contactEmail,
        public ?array $plan = null,
        public ?string $passwordResetUrl = null,
        public ?string $contactName = null,
    ) {
        app()->setLocale('ar');
        app(TenantMailBrandingService::class)->forceAudience(TenantMailBrandingService::AUDIENCE_PLATFORM);
        $this->platformName = app(TenantMailBrandingService::class)->platformName();
        config(['app.name' => $this->platformName]);
    }

    public function envelope(): Envelope
    {
        $fromAddress = (string) config('mail.from.address', 'no-reply@etoscoach.com');

        return new Envelope(
            from: new Address($fromAddress, $this->platformName),
            subject: 'مرحباً بك في '.$this->platformName.' — منصتك جاهزة',
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.welcome-tenant',
            with: [
                'platformName' => $this->platformName,
                'tenantName' => $this->tenantName,
                'tenantDomainUrl' => $this->tenantDomainUrl,
                'contactEmail' => $this->contactEmail,
                'contactName' => $this->contactName,
                'plan' => $this->plan,
                'passwordResetUrl' => $this->passwordResetUrl,
            ],
        );
    }
}
