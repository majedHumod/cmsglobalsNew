<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class BroadcastSegmentService
{
    /**
     * @return Collection<int, User>
     */
    public function resolveRecipients(User $sender, string $segmentType, array $filters = []): Collection
    {
        $query = User::query()->clients();

        if ($segmentType === 'coach_clients' || ($sender->hasRole('coach') && ! $sender->hasRole('admin'))) {
            $query->where('coach_id', $sender->id);
        }

        if ($segmentType === 'inactive_clients') {
            $query->whereDoesntHave(
                'progressCheckIns',
                fn (Builder $subQuery) => $subQuery->where('checked_in_at', '>=', now()->subDays(14))
            );
        }

        if ($segmentType === 'membership_expiring') {
            $query->whereNotNull('membership_expires_at')
                ->where('membership_expires_at', '<=', now()->addDays(7))
                ->where('membership_expires_at', '>', now()->subDay());
        }

        if (! empty($filters['coach_id'])) {
            $query->where('coach_id', (int) $filters['coach_id']);
        }

        return $query->orderBy('id')->get(['id', 'name', 'email', 'coach_id', 'membership_expires_at']);
    }
}
