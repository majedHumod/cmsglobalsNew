<?php

namespace App\Filament\Resources\SessionBookingResource\Pages;

use App\Filament\Resources\SessionBookingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSessionBooking extends EditRecord
{
    protected static string $resource = SessionBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('حذف'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
