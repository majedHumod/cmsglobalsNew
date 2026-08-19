<?php

namespace App\Services\Communication;

use App\Models\IntegrationWebhookLog;

class WebhookCommunicationGateway implements CommunicationGatewayInterface
{
    public function sendTemplate(int $userId, string $templateKey, array $context = []): array
    {
        $log = IntegrationWebhookLog::create([
            'provider' => 'webhook_bridge',
            'event_type' => 'outbound.template',
            'payload' => [
                'user_id' => $userId,
                'template_key' => $templateKey,
                'context' => $context,
            ],
            'status_code' => 202,
            'reference' => 'template:' . $templateKey,
        ]);

        return ['status' => 'queued', 'log_id' => $log->id];
    }
}
