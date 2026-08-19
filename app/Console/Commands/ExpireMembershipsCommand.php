<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserMembership;
use Illuminate\Console\Command;

class ExpireMembershipsCommand extends Command
{
    protected $signature = 'memberships:expire-stale';

    protected $description = 'Deactivate expired memberships and sync user access fields';

    public function handle(): int
    {
        $expired = UserMembership::query()
            ->where('is_active', true)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        $count = 0;
        foreach ($expired as $membership) {
            $membership->update(['is_active' => false]);

            $user = $membership->user;
            if (! $user) {
                continue;
            }

            $hasAnotherActive = UserMembership::query()
                ->where('user_id', $user->id)
                ->where('id', '!=', $membership->id)
                ->where('is_active', true)
                ->where('expires_at', '>', now())
                ->exists();

            if (! $hasAnotherActive) {
                $user->forceFill([
                    'membership_type_id' => null,
                    'membership_expires_at' => null,
                ])->save();
            }

            $count++;
        }

        $this->info("Expired memberships processed: {$count}");

        return self::SUCCESS;
    }
}
