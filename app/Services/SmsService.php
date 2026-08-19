<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class SmsService
{
    protected $twilio;

    public function __construct()
    {
        if (class_exists(Client::class) && env('TWILIO_SID') && env('TWILIO_AUTH_TOKEN')) {
            $this->twilio = new Client(
                env('TWILIO_SID'),
                env('TWILIO_AUTH_TOKEN')
            );
        }
    }

    public function sendSms($to, $message)
    {
        if (! $this->twilio) {
            Log::warning('SMS send skipped because Twilio is not configured.');

            return false;
        }

        return $this->twilio->messages->create(
            $to,
            [
                'from' => env('TWILIO_PHONE_NUMBER'),
                'body' => $message
            ]
        );
    }
}
