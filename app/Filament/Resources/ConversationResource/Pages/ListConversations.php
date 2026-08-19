<?php

namespace App\Filament\Resources\ConversationResource\Pages;

use App\Filament\Resources\ConversationResource;
use App\Filament\Pages\SendBroadcast;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListConversations extends ListRecords
{
    protected static string $resource = ConversationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('محادثة جديدة'),
            Actions\Action::make('broadcast')
                ->label('بث جماعي')
                ->icon('heroicon-o-megaphone')
                ->url(SendBroadcast::getUrl())
                ->visible(fn () => auth()->user()?->hasAnyRole(['admin', 'coach']) ?? false),
        ];
    }
}
