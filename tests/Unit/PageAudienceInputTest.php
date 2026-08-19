<?php

namespace Tests\Unit;

use App\Support\PageAudienceInput;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PageAudienceInputTest extends TestCase
{
    public static function nonMembershipLevelsProvider(): array
    {
        return [
            ['public'],
            ['authenticated'],
            ['admin'],
            ['user'],
            ['page_manager'],
        ];
    }

    #[DataProvider('nonMembershipLevelsProvider')]
    public function test_non_membership_access_level_always_yields_empty_membership_ids(string $accessLevel): void
    {
        $raw = [1, 2, 3];

        $this->assertSame(
            [],
            PageAudienceInput::membershipTypeIdsForAccessLevel($accessLevel, $raw)
        );
    }

    public function test_membership_access_level_normalizes_and_filters_ids(): void
    {
        $this->assertSame(
            [1, 2],
            PageAudienceInput::membershipTypeIdsForAccessLevel('membership', ['1', '', '2', '0', 'xx'])
        );
    }
}
