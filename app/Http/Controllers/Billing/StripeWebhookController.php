<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\UserMembership;
use App\Services\MembershipRenewalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request, MembershipRenewalService $renewalService)
    {
        return $this->handle($request, $renewalService);
    }

    public function handle(Request $request, MembershipRenewalService $renewalService)
    {
        $secret = env('STRIPE_WEBHOOK_SECRET');
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        try {
            if ($secret && $signature) {
                $event = Webhook::constructEvent($payload, $signature, $secret);
            } else {
                $event = json_decode($payload, false, 512, JSON_THROW_ON_ERROR);
            }
        } catch (\Throwable $exception) {
            Log::warning('Stripe webhook verification failed', ['error' => $exception->getMessage()]);

            return response()->json(['error' => 'invalid payload'], 400);
        }

        if (($event->type ?? null) === 'payment_intent.succeeded') {
            $this->handlePaymentIntentSucceeded($event->data->object ?? null, $renewalService);
        }

        return response()->json(['status' => 'ok']);
    }

    private function handlePaymentIntentSucceeded(?object $intent, MembershipRenewalService $renewalService): void
    {
        if (! $intent || empty($intent->metadata->membership_id)) {
            return;
        }

        $membership = UserMembership::query()->find((int) $intent->metadata->membership_id);
        if (! $membership || ($membership->payment_status === 'paid' && $membership->is_active)) {
            return;
        }

        $membership->update(['stripe_payment_intent_id' => (string) $intent->id]);

        if ($membership->starts_at !== null) {
            $renewalService->renew($membership);
        } else {
            $renewalService->activate($membership, (string) $intent->id);
        }
    }
}
