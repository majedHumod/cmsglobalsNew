<?php

namespace Tests\Unit;

use App\Models\MembershipType;
use App\Models\SubscriptionPlan;
use Tests\TestCase;

class MembershipTypeSubscribableTest extends TestCase
{
    public function test_protected_admin_membership_is_not_subscribable(): void
    {
        $type = new MembershipType([
            'name' => 'مدير النظام',
            'is_active' => true,
            'is_protected' => true,
        ]);

        $this->assertFalse($type->isSubscribable());
    }

    public function test_active_unprotected_membership_is_subscribable(): void
    {
        $type = new MembershipType([
            'name' => 'عضوية شهرية',
            'is_active' => true,
            'is_protected' => false,
        ]);

        $this->assertTrue($type->isSubscribable());
    }

    public function test_plan_linked_to_protected_membership_is_not_sellable(): void
    {
        $plan = new SubscriptionPlan([
            'name' => 'خطة الإدارة',
            'is_active' => true,
        ]);
        $plan->setRelation('membershipType', new MembershipType([
            'name' => 'مدير النظام',
            'is_active' => true,
            'is_protected' => true,
        ]));

        $this->assertFalse($plan->isSellable());
    }

    public function test_active_plan_on_customer_membership_is_sellable(): void
    {
        $plan = new SubscriptionPlan([
            'name' => 'شهري أساسي',
            'is_active' => true,
        ]);
        $plan->setRelation('membershipType', new MembershipType([
            'name' => 'عضوية شهرية',
            'is_active' => true,
            'is_protected' => false,
        ]));

        $this->assertTrue($plan->isSellable());
    }
}
