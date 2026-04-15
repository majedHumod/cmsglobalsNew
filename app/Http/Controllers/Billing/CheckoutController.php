<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Billing\Event;
use App\Models\Billing\Invoice;
use App\Models\Billing\Payment;
use App\Models\Billing\Plan;
use App\Models\Tenant;
use App\Services\Billing\PaylinkService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly PaylinkService $paylink,
    ) {
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan_code' => 'required|string|exists:system.plans,code',
            'subdomain' => 'required|string|min:3|max:30|regex:/^[a-z0-9-]+$/',
            'email' => 'required|email',
            'name' => 'nullable|string|max:120',
            'mobile' => 'required|string|min:8|max:20',
        ], [
            'subdomain.regex' => 'Subdomain must contain lowercase letters, numbers, and dashes only.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $slug = strtolower($data['subdomain']);

        // Prevent reserved subdomains
        $reserved = ['www','admin','api','demo','test','pay','billing','support'];
        if (in_array($slug, $reserved, true)) {
            return response()->json(['error' => 'This subdomain is reserved.'], 422);
        }

        // Check availability against system.tenants
        $exists = Tenant::on('system')->where('subdomain', $slug)->orWhere('domain', $slug.'.'.config('app.domain', 'yourdomain.com'))->exists();
        if ($exists) {
            return response()->json(['error' => 'This subdomain is already taken.'], 422);
        }

        $plan = Plan::where('code', $data['plan_code'])->where('active', true)->firstOrFail();
        $amount = (float) $plan->price;

        if ($amount < 5) {
            return response()->json(['error' => 'Paylink requires a minimum invoice amount of 5 SAR.'], 422);
        }

        $orderNumber = 'signup-' . Str::lower((string) Str::ulid());
        $callbackUrl = config('services.paylink.callback_url') ?: route('billing.paylink.callback');
        $cancelUrl = config('services.paylink.cancel_url') ?: route('subscribe');
        $products = [[
            'title' => $plan->name,
            'price' => $amount,
            'qty' => 1,
            'description' => 'Tenant subscription plan ' . $plan->code,
            'isDigital' => true,
        ]];

        try {
            $paylinkResponse = $this->paylink->createInvoice([
                'orderNumber' => $orderNumber,
                'amount' => $amount,
                'callBackUrl' => $callbackUrl,
                'cancelUrl' => $cancelUrl,
                'clientName' => $data['name'] ?: Str::title(str_replace('-', ' ', $slug)),
                'clientEmail' => $data['email'],
                'clientMobile' => $data['mobile'],
                'currency' => $plan->currency ?: config('services.paylink.currency', 'SAR'),
                'products' => $products,
                'supportedCardBrands' => config('services.paylink.supported_card_brands', []),
                'displayPending' => true,
                'note' => 'Tenant signup for ' . $slug,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        $transactionNo = (string) ($paylinkResponse['transactionNo'] ?? '');

        DB::connection('system')->transaction(function () use ($plan, $data, $slug, $amount, $orderNumber, $products, $paylinkResponse, $transactionNo) {
            Invoice::create([
                'tenant_id' => null,
                'number' => $orderNumber,
                'provider_invoice_id' => $transactionNo,
                'amount_due' => $amount,
                'amount_paid' => 0,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'currency' => $plan->currency ?: config('services.paylink.currency', 'SAR'),
                'status' => 'pending',
                'hosted_invoice_url' => $paylinkResponse['url'] ?? null,
                'invoice_pdf_url' => null,
                'period_start' => null,
                'period_end' => null,
                'line_items' => [
                    'products' => $products,
                    'signup' => [
                        'slug' => $slug,
                        'plan_code' => $plan->code,
                        'email' => $data['email'],
                        'name' => $data['name'] ?? null,
                        'mobile' => $data['mobile'],
                    ],
                ],
            ]);

            Payment::create([
                'tenant_id' => null,
                'provider_payment_intent_id' => $transactionNo,
                'amount' => $amount,
                'currency' => $plan->currency ?: config('services.paylink.currency', 'SAR'),
                'status' => 'pending',
                'paid_at' => null,
                'method_details' => [
                    'provider' => 'paylink',
                    'order_number' => $orderNumber,
                    'mobile_url' => $paylinkResponse['mobileUrl'] ?? null,
                    'check_url' => $paylinkResponse['checkUrl'] ?? null,
                    'signup' => [
                        'slug' => $slug,
                        'plan_code' => $plan->code,
                        'email' => $data['email'],
                        'name' => $data['name'] ?? null,
                        'mobile' => $data['mobile'],
                    ],
                ],
                'receipt_url' => null,
            ]);

            Event::create([
                'tenant_id' => null,
                'provider_event_id' => 'checkout:' . $orderNumber,
                'type' => 'paylink.invoice.created',
                'payload' => [
                    'provider' => 'paylink',
                    'order_number' => $orderNumber,
                    'transaction_no' => $transactionNo,
                    'plan_code' => $plan->code,
                    'slug' => $slug,
                    'email' => $data['email'],
                    'name' => $data['name'] ?? null,
                    'mobile' => $data['mobile'],
                    'paylink_response' => $paylinkResponse,
                ],
                'processed_at' => now(),
            ]);
        });

        if ($request->expectsJson() || str_contains($request->header('Accept', ''), 'application/json')) {
            return response()->json([
                'message' => 'Invoice created successfully. Redirecting to Paylink.',
                'subdomain' => $slug,
                'plan' => $plan->only(['code','name','price','interval','currency']),
                'redirect_url' => $paylinkResponse['url'] ?? null,
                'mobile_url' => $paylinkResponse['mobileUrl'] ?? null,
                'transaction_no' => $transactionNo,
                'order_number' => $orderNumber,
            ]);
        }

        return redirect()->away($paylinkResponse['url'] ?? route('subscribe'));
    }
}

