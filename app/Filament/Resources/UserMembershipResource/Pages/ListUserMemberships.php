<?php

namespace App\Filament\Resources\UserMembershipResource\Pages;

use App\Filament\Resources\UserMembershipResource;
use Filament\Resources\Pages\ListRecords;

class ListUserMemberships extends ListRecords
{
    protected static string $resource = UserMembershipResource::class;
}
