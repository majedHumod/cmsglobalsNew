<?php

namespace App\Listeners;

use App\Services\Mail\TenantMailBrandingService;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\App;
use Symfony\Component\Mime\Address;

class ApplyTenantMailBranding
{
    public function __construct(
        private readonly TenantMailBrandingService $branding,
    ) {
    }

    public function handle(MessageSending $event): void
    {
        App::setLocale('ar');

        if (! $this->branding->isTenantMailContext()) {
            return;
        }

        $from = config('mail.from.address');
        if (! is_string($from) || $from === '') {
            return;
        }

        $event->message->from(new Address($from, $this->branding->fromName()));
    }
}
