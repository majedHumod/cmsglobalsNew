<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeTenantMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $tenantName,
        public string $tenantDomainUrl,
        public string $contactEmail,
        public ?array $plan = null,
        public ?string $passwordResetUrl = null,
        public ?string $contactName = null,
    ) {
        config(['app.name' => $tenantName]);
        app()->setLocale('ar');
    }

    public function envelope(): Envelope
    {
        $fromAddress = (string) config('mail.from.address', 'no-reply@etoscoach.com');

        return new Envelope(
            from: new Address($fromAddress, $this->tenantName),
            subject: 'مرحباً بك في '.$this->tenantName.' — منصتك جاهزة',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.welcome-tenant',
            with: [
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
