<?php

namespace Tests\Unit;

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserMembership;
use App\Services\Payments\TenantPaymentGateway;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TenantPaymentGatewayTest extends TestCase
{
    public function test_moyasar_start_returns_checkout_url_and_paid_invoice_verifies_amount(): void
    {
        Http::fake([
            'https://api.moyasar.com/v1/invoices' => Http::response([
                'id' => 'inv_123',
                'url' => 'https://checkout.moyasar.com/inv_123',
                'status' => 'initiated',
            ], 201),
            'https://api.moyasar.com/v1/invoices/inv_123' => Http::response([
                'id' => 'inv_123',
                'status' => 'paid',
                'amount' => 15000,
                'currency' => 'SAR',
            ]),
        ]);

        $gateway = new TenantPaymentGateway;
        $started = $gateway->start('moyasar', $this->membership(), ['secret_key' => 'sk_test'], 'https://club.test/return', 'https://club.test/cancel');

        $this->assertSame('inv_123', $started['reference']);
        $this->assertSame('https://checkout.moyasar.com/inv_123', $started['redirect_url']);
        $this->assertTrue($gateway->isPaid('moyasar', 'inv_123', ['secret_key' => 'sk_test'], 15000));
    }

    public function test_unpaid_or_mismatched_moyasar_invoice_does_not_count_as_paid(): void
    {
        Http::fake([
            'https://api.moyasar.com/v1/invoices/inv_open' => Http::response([
                'id' => 'inv_open',
                'status' => 'initiated',
                'amount' => 15000,
                'currency' => 'SAR',
            ]),
            'https://api.moyasar.com/v1/invoices/inv_wrong' => Http::response([
                'id' => 'inv_wrong',
                'status' => 'paid',
                'amount' => 100,
                'currency' => 'SAR',
            ]),
        ]);

        $gateway = new TenantPaymentGateway;

        $this->assertFalse($gateway->isPaid('moyasar', 'inv_open', ['secret_key' => 'sk_test'], 15000));
        $this->assertFalse($gateway->isPaid('moyasar', 'inv_wrong', ['secret_key' => 'sk_test'], 15000));
    }

    public function test_stripe_checkout_uses_the_club_secret_and_confirms_paid_session(): void
    {
        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions' => Http::response([
                'id' => 'cs_test_123',
                'url' => 'https://checkout.stripe.com/c/pay/cs_test_123',
            ]),
            'https://api.stripe.com/v1/checkout/sessions/cs_test_123' => Http::response([
                'id' => 'cs_test_123',
                'payment_status' => 'paid',
                'amount_total' => 15000,
                'currency' => 'sar',
            ]),
        ]);

        $gateway = new TenantPaymentGateway;
        $started = $gateway->start('stripe', $this->membership(), ['secret_key' => 'sk_club'], 'https://club.test/return', 'https://club.test/cancel');

        $this->assertSame('cs_test_123', $started['reference']);
        $this->assertTrue($gateway->isPaid('stripe', 'cs_test_123', ['secret_key' => 'sk_club'], 15000));

        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://api.stripe.com/v1/checkout/sessions'
                && $request->hasHeader('Authorization', 'Bearer sk_club');
        });
    }

    private function membership(): UserMembership
    {
        $membership = new UserMembership([
            'payment_amount' => 150,
        ]);
        $membership->id = 9;
        $membership->setRelation('user', new User([
            'name' => 'علي',
            'email' => 'ali@example.com',
            'phone' => '966512345678',
        ]));
        $membership->setRelation('subscriptionPlan', new SubscriptionPlan([
            'name' => 'شهري',
        ]));

        return $membership;
    }
}
