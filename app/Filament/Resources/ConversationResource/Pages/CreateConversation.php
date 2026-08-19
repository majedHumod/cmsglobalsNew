<?php

namespace App\Filament\Resources\ConversationResource\Pages;

use App\Filament\Resources\ConversationResource;
use App\Models\User;
use App\Services\MessagingService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateConversation extends CreateRecord
{
    protected static string $resource = ConversationResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $messaging = app(MessagingService::class);
        $recipient = User::query()->findOrFail($data['recipient_user_id']);
        $conversation = $messaging->findOrCreateDirectConversation(
            auth()->user(),
            $recipient,
            $data['subject'] ?? null
        );
        $messaging->sendMessage($conversation, auth()->user(), $data['message']);

        return $conversation;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
