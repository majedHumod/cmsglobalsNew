<?php

namespace App\Filament\Resources\UserMembershipResource\Pages;

use App\Filament\Resources\UserMembershipResource;
use App\Models\UserMembership;
use App\Services\TrainingProgramService;
use Carbon\Carbon;
use Filament\Resources\Pages\EditRecord;

class EditUserMembership extends EditRecord
{
    protected static string $resource = UserMembershipResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function afterSave(): void
    {
        /** @var UserMembership $membership */
        $membership = $this->record->fresh(['user.clientProfile']);

        if (! $membership->is_active || $membership->payment_status !== 'paid' || ! $membership->user) {
            return;
        }

        app(TrainingProgramService::class)->activateForUser(
            $membership->user,
            $membership->starts_at ? Carbon::parse($membership->starts_at) : now()
        );
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
