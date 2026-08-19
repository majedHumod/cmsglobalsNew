<?php

namespace App\Services\Communication;

interface CommunicationGatewayInterface
{
    public function sendTemplate(int $userId, string $templateKey, array $context = []): array;
}
