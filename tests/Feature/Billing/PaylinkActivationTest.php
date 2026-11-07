<?php

namespace Tests\Feature\Billing;

use App\Jobs\Tenant\ProvisionTenantJob;
use App\Models\Billing\Invoice;
use App\Models\Billing\SignupReservation;
use App\Models\Tenant;
use App\Services\Billing\PaylinkService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Mockery\MockInterface;
use ReflectionProperty;
use Tests\Support\BillingTestCase;

class PaylinkActivationTest extends BillingTestCase
{
    private string $verifiedStatus = 'PAID';
    private float $verifiedAmount = 99.0;
    private int $txCounter = 0;

    /** @var array<string, string> order number => Paylink transaction number */
    private array $transactions = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        Queue::fake();
        Mail::fake();

        $this->partialMock(PaylinkService::class, function (MockInterface $mock) {
            $mock->shouldReceive('createInvoice')->andReturnUsing(function () {
                $tx = 'TX-'.(++$this->txCounter);

                return ['transactionNo' => $tx, 'url' => 'https://paylink.test/pay/'.$tx];
            });
            $mock->shouldReceive('getInvoice')->andReturnUsing(fn (string $tx) => [
                'transactionNo' => $tx,
                'orderStatus' => $this->verifiedStatus,
                'amount' => $this->verifiedAmount,
            ]);
        });
    }

    /**
     * @return string order number
     */
    private function startCheckout(array $overrides = []): string
    {
        $response = $this->postJson('/checkout/session', array_merge([
            'plan_code' => 'pro_monthly',
            'subdomain' => 'mjd-club',
            'email' => 'owner@example.com',
            'mobile' => '0501234567',
            'intent' => 'new',
        ], $overrides));

        $this->assertSame(200, $response->getStatusCode(), 'Checkout failed: '.$response->getContent());

        $this->transactions[$response->json('order_number')] = $response->json('transaction_no');

        return $response->json('order_number');
    }

    private function webhook(string $order): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/webhooks/paylink', [
            'transactionNo' => $this->transactions[$order],
            'merchantOrderNumber' => $order,
            'orderStatus' => 'Paid',
        ]);
    }

    private function queuedJobProperty(ProvisionTenantJob $job, string $property): mixed
    {
        $ref = new ReflectionProperty($job, $property);

        return $ref->getValue($job);
    }

    public function test_paid_webhook_queues_provisioning_with_the_normalised_phone(): void
    {
        $order = $this->startCheckout();

        $this->webhook($order)->assertOk()->assertJsonPath('status', 'queued');

        Queue::assertPushed(ProvisionTenantJob::class, function (ProvisionTenantJob $job) {
            return $this->queuedJobProperty($job, 'slug') === 'mjd-club'
                && $this->queuedJobProperty($job, 'contactPhone') === '+966501234567';
        });

        $this->assertSame(SignupReservation::PAID, SignupReservation::where('order_number', $order)->value('status'));
    }

    public function test_callback_page_tells_a_new_customer_what_happens_next(): void
    {
        $order = $this->startCheckout();

        $this->get('/billing/paylink/callback?transactionNo='.$this->transactions[$order].'&orderNumber='.$order)
            ->assertOk()
            ->assertSee('أهلاً بك في EtosCoach')
            ->assertSee('سيصلك بريد إلكتروني')
            ->assertSee('mjd-club.etoscoach.com')
            ->assertSee('owner@example.com')
            ->assertSee('تعيين كلمة المرور');
    }

    public function test_amount_mismatch_is_ignored_and_never_provisions(): void
    {
        $order = $this->startCheckout();
        $this->verifiedAmount = 1.0;

        $this->webhook($order)->assertOk()->assertJsonPath('status', 'ignored');

        Queue::assertNothingPushed();
        $this->assertSame('open', Invoice::where('number', $order)->value('status'));
    }

    public function test_cancelled_payment_releases_the_reserved_name_and_email(): void
    {
        $order = $this->startCheckout();
        $this->verifiedStatus = 'CANCELED';

        $this->webhook($order)->assertOk()->assertJsonPath('status', 'ignored');

        $reservation = SignupReservation::where('order_number', $order)->first();
        $this->assertSame(SignupReservation::RELEASED, $reservation->status);
        $this->assertNull($reservation->active_slug);
        $this->assertNull($reservation->active_email);
    }

    public function test_duplicate_webhook_does_not_queue_provisioning_twice(): void
    {
        $order = $this->startCheckout();

        $this->webhook($order)->assertOk();
        $this->webhook($order)->assertOk()->assertJsonPath('status', 'duplicate');

        Queue::assertPushed(ProvisionTenantJob::class, 1);
    }

    public function test_late_payment_reclaims_an_expired_reservation_when_the_name_is_still_free(): void
    {
        $order = $this->startCheckout();
        $this->travel(3)->hours();

        // Someone else checks out meanwhile, which releases the expired reservation.
        $this->postJson('/checkout/session', [
            'plan_code' => 'pro_monthly',
            'subdomain' => 'other-club',
            'email' => 'other@example.com',
            'mobile' => '0551234567',
            'intent' => 'new',
        ])->assertOk();
        $this->assertSame(SignupReservation::RELEASED, SignupReservation::where('order_number', $order)->value('status'));

        $this->webhook($order)->assertOk()->assertJsonPath('status', 'queued');

        $reservation = SignupReservation::where('order_number', $order)->first();
        $this->assertSame(SignupReservation::PAID, $reservation->status);
        $this->assertSame('mjd-club', $reservation->active_slug);
        Queue::assertPushed(ProvisionTenantJob::class, 1);
    }

    public function test_late_payment_for_a_name_someone_else_took_becomes_a_conflict_and_is_not_provisioned(): void
    {
        $order = $this->startCheckout();
        $this->travel(3)->hours();

        // Another buyer takes the same subdomain after the first reservation expired, and gets provisioned.
        Tenant::create([
            'name' => 'Other Club',
            'slug' => 'mjd-club',
            'subdomain' => 'mjd-club',
            'domain' => 'mjd-club.etoscoach.com',
            'email' => 'other@example.com',
            'db_name' => 'tenant_other',
        ]);

        $this->webhook($order)->assertOk()->assertJsonPath('status', 'signup_conflict');

        Queue::assertNothingPushed();
        $this->assertSame(SignupReservation::CONFLICT, SignupReservation::where('order_number', $order)->value('status'));
        // The other club's tenant must not be linked to this customer's invoice.
        $this->assertNull(Invoice::where('number', $order)->value('tenant_id'));

        // A second notification (callback / reconcile) must stay a conflict as well.
        $this->webhook($order)->assertOk();
        Queue::assertNothingPushed();
    }
}
