<?php

namespace App\Services\Payments;

use App\Models\UserMembership;
use App\Services\MembershipRenewalService;
use App\Support\TenantPaymentCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class CoachSubscriptionCheckout
{
    public function __construct(
        private readonly TenantPaymentSettings $settings,
        private readonly TenantPaymentGateway $gateway,
        private readonly MembershipRenewalService $renewals,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function methods(): array
    {
        return $this->settings->checkoutMethods();
    }

    /**
     * @return array{type: string, url?: string, view?: string, data?: array<string, mixed>, message?: string}
     */
    public function start(UserMembership $membership, string $channel): array
    {
        if ($membership->payment_status === 'paid' && $membership->is_active) {
            return ['type' => 'already_paid'];
        }

        if (! TenantPaymentCatalog::has($channel) || TenantPaymentCatalog::isManual($channel)) {
            return ['type' => 'error', 'message' => 'وسيلة الدفع المختارة غير متاحة للدفع المباشر.'];
        }

        $enabled = collect($this->settings->checkoutMethods())->contains(fn (array $method): bool => $method['key'] === $channel);
        if (! $enabled) {
            return ['type' => 'error', 'message' => 'هذه القناة غير مفعّلة في إعدادات النادي.'];
        }

        $returnUrl = route('subscription-plans.payment.return', $membership);
        $cancelUrl = route('subscription-plans.payment', $membership);

        $started = $this->gateway->start(
            $channel,
            $membership->loadMissing('user', 'subscriptionPlan'),
            $this->settings->credentials($channel),
            $returnUrl,
            $cancelUrl
        );

        $redirectUrl = $started['redirect_url'] ?: null;

        if ($redirectUrl === null && $started['widget'] === []) {
            return ['type' => 'error', 'message' => 'لم تُرجع بوابة الدفع رابط إتمام العملية.'];
        }

        $membership->update([
            'payment_channel' => $channel,
            'gateway_reference' => $started['reference'],
            'payment_status' => 'pending',
            'stripe_payment_intent_id' => $channel === 'stripe' ? $started['reference'] : $membership->stripe_payment_intent_id,
        ]);

        if ($redirectUrl) {
            return ['type' => 'redirect', 'url' => $redirectUrl];
        }

        return [
            'type' => 'view',
            'view' => 'subscription-plans.hyperpay',
            'data' => [
                'membership' => $membership->fresh()->load('subscriptionPlan.membershipType'),
                'widget' => $started['widget'],
                'returnUrl' => $returnUrl,
            ],
        ];
    }

    public function submitBankTransfer(UserMembership $membership, string $reference, ?string $receiptPath): void
    {
        if ($membership->payment_status === 'paid' && $membership->is_active) {
            throw new RuntimeException('الاشتراك مدفوع بالفعل.');
        }

        $enabled = collect($this->settings->checkoutMethods())->firstWhere('key', 'bank_transfer');
        if (! is_array($enabled)) {
            throw new RuntimeException('التحويل البنكي غير مفعّل.');
        }

        if ($membership->transfer_receipt && $receiptPath && $membership->transfer_receipt !== $receiptPath) {
            Storage::disk('public')->delete($membership->transfer_receipt);
        }

        $membership->update([
            'payment_channel' => 'bank_transfer',
            'payment_status' => 'pending',
            'transfer_reference' => $reference,
            'transfer_receipt' => $receiptPath ?: $membership->transfer_receipt,
            'payment_reference' => $reference,
        ]);
    }

    public function verifyAndSettle(UserMembership $membership, Request $request): bool
    {
        $membership->refresh();

        if ($membership->payment_status === 'paid' && $membership->is_active) {
            return true;
        }

        $channel = (string) $membership->payment_channel;
        if ($channel === '' || ! TenantPaymentCatalog::has($channel) || TenantPaymentCatalog::isManual($channel)) {
            return false;
        }

        $storedReference = (string) $membership->gateway_reference;
        if ($storedReference === '') {
            return false;
        }

        $presented = $this->presentedReference($channel, $request);
        if ($presented !== null && ! $this->sameReference($storedReference, $presented)) {
            return false;
        }

        $paid = $this->gateway->isPaid(
            $channel,
            $storedReference,
            $this->settings->credentials($channel),
            TenantPaymentCatalog::halalas($membership->payment_amount)
        );

        if (! $paid) {
            return false;
        }

        $this->activate($membership, $storedReference);

        return true;
    }

    public function confirmBankTransfer(UserMembership $membership): void
    {
        $membership->refresh();

        if ($membership->payment_channel !== 'bank_transfer' || $membership->payment_status !== 'pending') {
            throw new RuntimeException('لا يوجد تحويل بنكي بانتظار التأكيد.');
        }

        $this->activate($membership, $membership->transfer_reference ?: $membership->payment_reference);
    }

    public function rejectBankTransfer(UserMembership $membership): void
    {
        $membership->refresh();

        if ($membership->payment_channel !== 'bank_transfer' || $membership->payment_status !== 'pending') {
            throw new RuntimeException('لا يوجد تحويل بنكي بانتظار الرفض.');
        }

        $membership->update([
            'payment_status' => 'failed',
        ]);
    }

    private function activate(UserMembership $membership, ?string $reference): void
    {
        $membership->getConnection()->transaction(function () use ($membership, $reference): void {
            $locked = UserMembership::query()->lockForUpdate()->find($membership->id);

            if (! $locked || ($locked->payment_status === 'paid' && $locked->is_active)) {
                return;
            }

            $this->renewals->activate($locked, $reference, $locked->starts_at !== null);
        });
    }

    private function presentedReference(string $channel, Request $request): ?string
    {
        $value = match ($channel) {
            'stripe' => $request->query('session_id'),
            'moyasar' => $request->query('id'),
            'tap' => $request->query('tap_id'),
            'paytabs' => $request->input('tranRef') ?: $request->query('tranRef'),
            'hyperpay' => $request->query('id'),
            default => null,
        };

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return trim($value);
    }

    private function sameReference(string $stored, string $presented): bool
    {
        return strlen($stored) === strlen($presented) && hash_equals($stored, $presented);
    }
}
