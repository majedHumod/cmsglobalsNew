<?php

namespace App\Models\Concerns;

use App\Models\User;
use App\Services\MembershipAccessService;

trait HasAudience
{
    /**
     * @param  mixed  $value
     * @return array<int, int>
     */
    protected function normalizeAudienceMembershipTypes($value): array
    {
        return MembershipAccessService::normalizeMembershipTypeIds($value);
    }

    public function getRequiredMembershipTypesAttribute($value): array
    {
        return $this->normalizeAudienceMembershipTypes($value);
    }

    /**
     * @param  mixed  $value
     */
    public function setRequiredMembershipTypesAttribute($value): void
    {
        $this->attributes['required_membership_types'] = json_encode(
            $this->normalizeAudienceMembershipTypes($value)
        );
    }

    public function getAudienceGenderLabelAttribute(): string
    {
        return match ($this->audience_gender) {
            'male' => 'رجال',
            'female' => 'نساء',
            default => 'الجميع',
        };
    }

    public function matchesAudience(?User $user = null): bool
    {
        return MembershipAccessService::matchesGender($user, $this->audience_gender)
            && MembershipAccessService::matchesMembershipTypes($user, $this->required_membership_types ?? []);
    }

    public function scopeVisibleTo($query, ?User $user = null)
    {
        $membershipTypeIds = MembershipAccessService::currentMembershipTypeIds($user);

        $query->where(function ($audienceQuery) use ($user) {
            $audienceQuery->whereNull('audience_gender')
                ->orWhere('audience_gender', 'all');

            if ($user?->gender) {
                $audienceQuery->orWhere('audience_gender', $user->gender);
            }
        });

        return $query->where(function ($membershipQuery) use ($membershipTypeIds) {
            $membershipQuery->whereNull('required_membership_types')
                ->orWhere('required_membership_types', '[]');

            foreach ($membershipTypeIds as $membershipTypeId) {
                $membershipQuery->orWhereJsonContains('required_membership_types', $membershipTypeId);
            }
        });
    }
}
