<?php

namespace App\Filament\Resources\ConversationResource\RelationManagers;

use App\Services\MessagingService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Livewire\Attributes\On;

class MessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';

    protected static ?string $title = 'سجل الرسائل';

    #[On('conversation-messages-updated')]
    public function refreshMessages(): void
    {
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sender.name')
                    ->label('المرسل'),
                Tables\Columns\TextColumn::make('body')
                    ->label('المحتوى')
                    ->wrap()
                    ->limit(120),
                Tables\Columns\TextColumn::make('sent_at')
                    ->label('الوقت')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('sent_at', 'desc')
            ->poll('15s')
            ->headerActions([
                Tables\Actions\Action::make('reply')
                    ->label('إضافة رد')
                    ->icon('heroicon-o-paper-airplane')
                    ->form([
                        Forms\Components\Textarea::make('body')
                            ->label('الرسالة')
                            ->required()
                            ->rows(4)
                            ->maxLength(5000),
                    ])
                    ->action(function (array $data): void {
                        app(MessagingService::class)->sendMessage(
                            $this->getOwnerRecord(),
                            auth()->user(),
                            $data['body']
                        );

                        $this->getOwnerRecord()->refresh();
                        $this->resetTable();

                        Notification::make()
                            ->title('تم إرسال الرد')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([])
            ->bulkActions([]);
    }
}
