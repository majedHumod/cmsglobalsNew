<?php

namespace App\Filament\Resources\ConversationResource\Pages;

use App\Filament\Resources\ConversationResource;
use App\Models\ConversationParticipant;
use App\Models\MessageTemplate;
use App\Services\MessageTemplateService;
use App\Services\MessagingService;
use App\Services\NotificationFeedService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditConversation extends EditRecord
{
    protected static string $resource = ConversationResource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);

        ConversationParticipant::query()
            ->where('conversation_id', $this->record->id)
            ->where('user_id', auth()->id())
            ->update(['last_read_at' => now()]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('reply')
                ->label('رد')
                ->icon('heroicon-o-paper-airplane')
                ->form([
                    Forms\Components\Select::make('template_id')
                        ->label('قالب جاهز (اختياري)')
                        ->options(fn () => MessageTemplate::query()
                            ->where('is_active', true)
                            ->where(function ($query) {
                                $query->where('created_by_user_id', auth()->id())
                                    ->orWhere('category', 'global');
                            })
                            ->orderBy('name')
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->native(false)
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set): void {
                            if (! $state) {
                                return;
                            }
                            $template = MessageTemplate::query()->find($state);
                            if ($template) {
                                $set('body', $template->body);
                            }
                        }),
                    Forms\Components\Textarea::make('body')
                        ->label('الرسالة')
                        ->required()
                        ->rows(4)
                        ->maxLength(5000)
                        ->helperText('يدعم {{client_name}} {{coach_name}} {{org_name}} {{date}}'),
                ])
                ->action(function (array $data): void {
                    $sender = auth()->user();
                    $recipient = $this->record->users()->where('users.id', '!=', $sender->id)->first();
                    $body = (string) $data['body'];
                    $templates = app(MessageTemplateService::class);

                    if (! empty($data['template_id'])) {
                        $template = MessageTemplate::query()->find($data['template_id']);
                        if ($template) {
                            $body = $templates->renderTemplate($template, $recipient, $sender);
                        }
                    } elseif (str_contains($body, '{{')) {
                        $body = $templates->render($body, $templates->contextFor($recipient, $sender));
                    }

                    $message = app(MessagingService::class)->sendMessage(
                        $this->record,
                        $sender,
                        $body
                    );

                    if ($recipient) {
                        app(NotificationFeedService::class)->pushToUser(
                            (int) $recipient->id,
                            'message.received',
                            'رسالة جديدة',
                            'لديك رسالة جديدة من '.$sender->name,
                            ['conversation_id' => $this->record->id]
                        );
                    }

                    $this->record->refresh();
                    $this->record->load('users');
                    $this->fillForm();

                    ConversationParticipant::query()
                        ->where('conversation_id', $this->record->id)
                        ->where('user_id', auth()->id())
                        ->update(['last_read_at' => now()]);

                    $this->dispatch('conversation-messages-updated');

                    Notification::make()
                        ->title('تم إرسال الرد')
                        ->success()
                        ->send();
                }),
            Actions\DeleteAction::make()->label('حذف'),
        ];
    }

    protected function getFormActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): ?string
    {
        return null;
    }
}
