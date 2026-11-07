<?php

namespace App\Services\Billing;

class SignupConflictException extends \RuntimeException
{
    public function __construct(
        public readonly string $reason,
        string $message,
    ) {
        parent::__construct($message);
    }
}
