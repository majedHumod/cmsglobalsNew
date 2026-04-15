<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeTenantMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $tenantName,
        public string $tenantDomainUrl,
        public string $contactEmail,
        public ?array $plan = null,
        public ?string $passwordResetUrl = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'مرحباً بك في EtosCoach',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome-tenant',
            with: [
                'tenantName' => $this->tenantName,
                'tenantDomainUrl' => $this->tenantDomainUrl,
                'contactEmail' => $this->contactEmail,
                'plan' => $this->plan,
                'passwordResetUrl' => $this->passwordResetUrl,
            ],
        );
    }
}

