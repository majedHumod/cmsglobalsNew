<?php

namespace App\Filament\Resources\UserPermissionOverrideResource\Pages;

use App\Filament\Resources\UserPermissionOverrideResource;
use App\Models\User;
use App\Services\AdvancedPermissionService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateUserPermissionOverride extends CreateRecord
{
    protected static string $resource = UserPermissionOverrideResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $user = User::query()->findOrFail($data['user_id']);
        $expiresAt = ! empty($data['expires_at']) ? new \DateTime($data['expires_at']) : null;

        return app(AdvancedPermissionService::class)->grantPermissionOverride(
            $user,
            $data['permission_name'],
            $data['type'] ?? 'grant',
            $data['reason'] ?? null,
            $expiresAt
        );
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
