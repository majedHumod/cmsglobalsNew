<?php

namespace App\Services\Payments;

use App\Models\UserMembership;
use App\Support\TenantPaymentCatalog;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class TenantPaymentGateway
{
    /**
     * @param  array<string, string>  $credentials
     * @return array{reference: string, redirect_url: ?string, widget: array<string, string>}
     */
    public function start(
        string $channel,
        UserMembership $membership,
        array $credentials,
        string $returnUrl,
        string $cancelUrl
    ): array {
        $amount = (float) $membership->payment_amount;
        $halalas = TenantPaymentCatalog::halalas($amount);
        $description = 'اشتراك '.($membership->subscriptionPlan?->name ?? '#'.$membership->id);

        $result = match ($channel) {
            'moyasar' => $this->startMoyasar($credentials, $halalas, $description, $returnUrl),
            'tap' => $this->startTap($membership, $credentials, $amount, $description, $returnUrl),
            'paytabs' => $this->startPaytabs($membership, $credentials, $amount, $description, $returnUrl),
            'hyperpay' => $this->startHyperpay($credentials, $amount),
            'myfatoorah' => $this->startMyfatoorah($membership, $credentials, $amount, $returnUrl, $cancelUrl),
            'stripe' => $this->startStripe($membership, $credentials, $halalas, $description, $returnUrl, $cancelUrl),
            default => throw new RuntimeException('قناة الدفع غير مدعومة للتحصيل المباشر.'),
        };

        $redirect = $result['redirect_url'] ?? null;
        $widget = $result['widget'] ?? [];

        if (($result['reference'] ?? '') === '' || (($redirect === null || $redirect === '') && $widget === [])) {
            throw new RuntimeException('لم تُرجع بوابة الدفع بيانات إتمام العملية.');
        }

        return [
            'reference' => (string) $result['reference'],
            'redirect_url' => $result['redirect_url'] ?? null,
            'widget' => $result['widget'] ?? [],
        ];
    }

    /**
     * @param  array<string, string>  $credentials
     */
    public function isPaid(string $channel, string $reference, array $credentials, int $expectedHalalas): bool
    {
        return match ($channel) {
            'moyasar' => $this->moyasarPaid($credentials, $reference, $expectedHalalas),
            'tap' => $this->tapPaid($credentials, $reference, $expectedHalalas),
            'paytabs' => $this->paytabsPaid($credentials, $reference, $expectedHalalas),
            'hyperpay' => $this->hyperpayPaid($credentials, $reference, $expectedHalalas),
            'myfatoorah' => $this->myfatoorahPaid($credentials, $reference, $expectedHalalas),
            'stripe' => $this->stripePaid($credentials, $reference, $expectedHalalas),
            default => false,
        };
    }

    /**
     * @param  array<string, string>  $credentials
     * @return array{reference: string, redirect_url: string}
     */
    private function startMoyasar(array $credentials, int $halalas, string $description, string $returnUrl): array
    {
        $response = Http::withBasicAuth($credentials['secret_key'] ?? '', '')
            ->acceptJson()
            ->asJson()
            ->timeout(25)
            ->post('https://api.moyasar.com/v1/invoices', [
                'amount' => $halalas,
                'currency' => 'SAR',
                'description' => $description,
                'callback_url' => $returnUrl,
                'success_url' => $returnUrl,
            ]);

        $this->ensureOk($response, 'moyasar');

        return [
            'reference' => (string) $response->json('id'),
            'redirect_url' => (string) $response->json('url'),
        ];
    }

    /**
     * @param  array<string, string>  $credentials
     */
    private function moyasarPaid(array $credentials, string $reference, int $expectedHalalas): bool
    {
        $response = Http::withBasicAuth($credentials['secret_key'] ?? '', '')
            ->acceptJson()
            ->timeout(25)
            ->get('https://api.moyasar.com/v1/invoices/'.urlencode($reference));

        $this->ensureOk($response, 'moyasar');

        return strtolower((string) $response->json('status')) === 'paid'
            && $this->currencyMatches($response->json('currency'))
            && $this->amountMatches((int) $response->json('amount'), $expectedHalalas);
    }

    /**
     * @param  array<string, string>  $credentials
     * @return array{reference: string, redirect_url: string}
     */
    private function startTap(UserMembership $membership, array $credentials, float $amount, string $description, string $returnUrl): array
    {
        $user = $membership->user;
        $response = Http::withToken($credentials['secret_key'] ?? '')
            ->acceptJson()
            ->asJson()
            ->timeout(25)
            ->post('https://api.tap.company/v2/charges', [
                'amount' => round($amount, 2),
                'currency' => 'SAR',
                'threeDSecure' => true,
                'description' => $description,
                'reference' => [
                    'transaction' => 'membership-'.$membership->id,
                    'order' => (string) $membership->id,
                ],
                'customer' => [
                    'first_name' => $user?->name ?: 'عميل',
                    'email' => $user?->email,
                    'phone' => $this->tapPhone($user?->phone),
                ],
                'source' => ['id' => 'src_all'],
                'redirect' => ['url' => $returnUrl],
            ]);

        $this->ensureOk($response, 'tap');

        return [
            'reference' => (string) $response->json('id'),
            'redirect_url' => (string) $response->json('transaction.url'),
        ];
    }

    /**
     * @param  array<string, string>  $credentials
     */
    private function tapPaid(array $credentials, string $reference, int $expectedHalalas): bool
    {
        $response = Http::withToken($credentials['secret_key'] ?? '')
            ->acceptJson()
            ->timeout(25)
            ->get('https://api.tap.company/v2/charges/'.urlencode($reference));

        $this->ensureOk($response, 'tap');

        return strtoupper((string) $response->json('status')) === 'CAPTURED'
            && $this->currencyMatches($response->json('currency'))
            && $this->amountMatches(TenantPaymentCatalog::halalas((string) $response->json('amount')), $expectedHalalas);
    }

    /**
     * @param  array<string, string>  $credentials
     * @return array{reference: string, redirect_url: string}
     */
    private function startPaytabs(UserMembership $membership, array $credentials, float $amount, string $description, string $returnUrl): array
    {
        $response = Http::withHeaders([
            'Authorization' => (string) ($credentials['server_key'] ?? ''),
        ])
            ->acceptJson()
            ->asJson()
            ->timeout(25)
            ->post('https://secure.paytabs.sa/payment/request', [
                'profile_id' => (int) ($credentials['profile_id'] ?? 0),
                'tran_type' => 'sale',
                'tran_class' => 'ecom',
                'cart_id' => 'membership-'.$membership->id.'-'.now()->timestamp,
                'cart_description' => $description,
                'cart_currency' => 'SAR',
                'cart_amount' => round($amount, 2),
                'callback' => $returnUrl,
                'return' => $returnUrl,
                'hide_shipping' => true,
                'customer_details' => [
                    'name' => $membership->user?->name ?: 'عميل',
                    'email' => $membership->user?->email ?: 'customer@example.com',
                    'phone' => $membership->user?->phone ?: '0500000000',
                    'street1' => 'NA',
                    'city' => 'Riyadh',
                    'state' => 'RD',
                    'country' => 'SA',
                    'zip' => '00000',
                ],
            ]);

        $this->ensureOk($response, 'paytabs');

        $reference = (string) ($response->json('tran_ref') ?: $response->json('tranRef'));

        return [
            'reference' => $reference,
            'redirect_url' => (string) $response->json('redirect_url'),
        ];
    }

    /**
     * @param  array<string, string>  $credentials
     */
    private function paytabsPaid(array $credentials, string $reference, int $expectedHalalas): bool
    {
        $response = Http::withHeaders([
            'Authorization' => (string) ($credentials['server_key'] ?? ''),
        ])
            ->acceptJson()
            ->asJson()
            ->timeout(25)
            ->post('https://secure.paytabs.sa/payment/query', [
                'profile_id' => (int) ($credentials['profile_id'] ?? 0),
                'tran_ref' => $reference,
            ]);

        $this->ensureOk($response, 'paytabs');

        $status = (string) ($response->json('payment_result.response_status') ?? '');

        return $status === 'A'
            && $this->currencyMatches($response->json('cart_currency'))
            && $this->amountMatches(TenantPaymentCatalog::halalas((string) $response->json('cart_amount')), $expectedHalalas);
    }

    /**
     * @param  array<string, string>  $credentials
     * @return array{reference: string, redirect_url: null, widget: array<string, string>}
     */
    private function startHyperpay(array $credentials, float $amount): array
    {
        $base = $this->hyperpayBase($credentials['mode'] ?? 'test');
        $response = Http::withToken($credentials['access_token'] ?? '')
            ->asForm()
            ->acceptJson()
            ->timeout(25)
            ->post($base.'/v1/checkouts', [
                'entityId' => $credentials['entity_id'] ?? '',
                'amount' => number_format($amount, 2, '.', ''),
                'currency' => 'SAR',
                'paymentType' => 'DB',
            ]);

        $this->ensureOk($response, 'hyperpay');

        $checkoutId = (string) $response->json('id');

        return [
            'reference' => $checkoutId,
            'redirect_url' => null,
            'widget' => [
                'script_url' => $base.'/v1/paymentWidgets.js?checkoutId='.urlencode($checkoutId),
                'integrity' => (string) $response->json('integrity'),
                'brands' => 'VISA MASTER MADA',
            ],
        ];
    }

    /**
     * @param  array<string, string>  $credentials
     */
    private function hyperpayPaid(array $credentials, string $reference, int $expectedHalalas): bool
    {
        $base = $this->hyperpayBase($credentials['mode'] ?? 'test');
        $response = Http::withToken($credentials['access_token'] ?? '')
            ->acceptJson()
            ->timeout(25)
            ->get($base.'/v1/checkouts/'.urlencode($reference).'/payment', [
                'entityId' => $credentials['entity_id'] ?? '',
            ]);

        $this->ensureOk($response, 'hyperpay');

        $code = (string) $response->json('result.code');
        $successful = preg_match('/^(000\.000\.|000\.100\.1|000\.[36])/', $code) === 1;

        return $successful
            && $this->currencyMatches($response->json('currency'))
            && $this->amountMatches(TenantPaymentCatalog::halalas((string) $response->json('amount')), $expectedHalalas);
    }

    /**
     * @param  array<string, string>  $credentials
     * @return array{reference: string, redirect_url: string}
     */
    private function startMyfatoorah(UserMembership $membership, array $credentials, float $amount, string $returnUrl, string $cancelUrl): array
    {
        $response = Http::withToken($credentials['api_key'] ?? '')
            ->acceptJson()
            ->asJson()
            ->timeout(25)
            ->post($this->myfatoorahBase($credentials['mode'] ?? 'live').'/v2/SendPayment', [
                'CustomerName' => $membership->user?->name ?: 'عميل',
                'CustomerEmail' => $membership->user?->email,
                'NotificationOption' => 'LNK',
                'InvoiceValue' => round($amount, 2),
                'DisplayCurrencyIso' => 'SAR',
                'CallBackUrl' => $returnUrl,
                'ErrorUrl' => $cancelUrl,
                'Language' => 'ar',
                'CustomerReference' => (string) $membership->id,
            ]);

        $this->ensureOk($response, 'myfatoorah');

        if ($response->json('IsSuccess') !== true) {
            $this->fail('myfatoorah');
        }

        return [
            'reference' => (string) $response->json('Data.InvoiceId'),
            'redirect_url' => (string) $response->json('Data.InvoiceURL'),
        ];
    }

    /**
     * @param  array<string, string>  $credentials
     */
    private function myfatoorahPaid(array $credentials, string $reference, int $expectedHalalas): bool
    {
        $response = Http::withToken($credentials['api_key'] ?? '')
            ->acceptJson()
            ->asJson()
            ->timeout(25)
            ->post($this->myfatoorahBase($credentials['mode'] ?? 'live').'/v2/GetPaymentStatus', [
                'Key' => $reference,
                'KeyType' => 'InvoiceId',
            ]);

        $this->ensureOk($response, 'myfatoorah');

        if ($response->json('IsSuccess') !== true) {
            return false;
        }

        return strcasecmp((string) $response->json('Data.InvoiceStatus'), 'Paid') === 0
            && $this->amountMatches(TenantPaymentCatalog::halalas((string) $response->json('Data.InvoiceValue')), $expectedHalalas);
    }

    /**
     * @param  array<string, string>  $credentials
     * @return array{reference: string, redirect_url: string}
     */
    private function startStripe(
        UserMembership $membership,
        array $credentials,
        int $halalas,
        string $description,
        string $returnUrl,
        string $cancelUrl
    ): array {
        $separator = str_contains($returnUrl, '?') ? '&' : '?';
        $response = Http::withToken($credentials['secret_key'] ?? '')
            ->asForm()
            ->acceptJson()
            ->timeout(25)
            ->post('https://api.stripe.com/v1/checkout/sessions', [
                'mode' => 'payment',
                'success_url' => $returnUrl.$separator.'session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $cancelUrl,
                'client_reference_id' => (string) $membership->id,
                'line_items' => [[
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => 'sar',
                        'unit_amount' => $halalas,
                        'product_data' => [
                            'name' => $description,
                        ],
                    ],
                ]],
                'metadata' => [
                    'membership_id' => (string) $membership->id,
                    'scope' => 'coach_subscription',
                ],
            ]);

        $this->ensureOk($response, 'stripe');

        return [
            'reference' => (string) $response->json('id'),
            'redirect_url' => (string) $response->json('url'),
        ];
    }

    /**
     * @param  array<string, string>  $credentials
     */
    private function stripePaid(array $credentials, string $reference, int $expectedHalalas): bool
    {
        $response = Http::withToken($credentials['secret_key'] ?? '')
            ->acceptJson()
            ->timeout(25)
            ->get('https://api.stripe.com/v1/checkout/sessions/'.urlencode($reference));

        $this->ensureOk($response, 'stripe');

        return (string) $response->json('payment_status') === 'paid'
            && $this->currencyMatches($response->json('currency'))
            && $this->amountMatches((int) $response->json('amount_total'), $expectedHalalas);
    }

    private function hyperpayBase(string $mode): string
    {
        return $mode === 'live' ? 'https://eu-prod.oppwa.com' : 'https://eu-test.oppwa.com';
    }

    private function myfatoorahBase(string $mode): string
    {
        return $mode === 'test' ? 'https://apitest.myfatoorah.com' : 'https://api-sa.myfatoorah.com';
    }

    /**
     * @return array{country_code: string, number: string}
     */
    private function tapPhone(?string $phone): array
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?? '';

        if (str_starts_with($digits, '966')) {
            $digits = substr($digits, 3);
        }

        $digits = ltrim($digits, '0');

        return [
            'country_code' => '966',
            'number' => $digits !== '' ? $digits : '500000000',
        ];
    }

    private function currencyMatches(mixed $currency): bool
    {
        if ($currency === null || $currency === '') {
            return true;
        }

        return strcasecmp((string) $currency, 'SAR') === 0;
    }

    private function amountMatches(int $actualHalalas, int $expectedHalalas): bool
    {
        return abs($actualHalalas - $expectedHalalas) <= 1;
    }

    private function ensureOk(Response $response, string $channel): void
    {
        if ($response->failed()) {
            $this->fail($channel, $response->status());
        }
    }

    private function fail(string $channel, ?int $status = null): never
    {
        Log::warning('Coach payment gateway request failed', [
            'channel' => $channel,
            'status' => $status,
        ]);

        throw new RuntimeException('تعذر إتمام العملية مع بوابة الدفع. راجع بيانات الربط في إعدادات الدفع.');
    }
}
