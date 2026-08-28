<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Billing\Event;
use App\Models\Billing\Invoice;
use App\Models\Billing\Payment;
use App\Models\Billing\Plan;
use App\Models\Tenant;
use App\Services\Billing\PaylinkService;
use App\Services\Platform\PlatformAccountCookie;
use App\Services\Platform\TenantAccessService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly PaylinkService $paylink,
        private readonly PlatformAccountCookie $cookie,
        private readonly TenantAccessService $access,
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
        $renewTenant = $this->renewalTenant($request);
        if ($renewTenant) {
            $this->access->sync($renewTenant);
            if ($this->access->canUseWorkspace($renewTenant)) {
                return response()->json([
                    'error' => 'اشتراكك ساري حالياً. لا حاجة للتجديد الآن — يمكنك الدخول إلى لوحة التحكم مباشرة.',
                ], 422);
            }

            $slug = strtolower((string) ($renewTenant->subdomain ?: explode('.', (string) $renewTenant->domain)[0]));
            $data['subdomain'] = $slug;
            if (empty($data['email'])) {
                $data['email'] = (string) $renewTenant->email;
            }
            if (empty($data['name'])) {
                $data['name'] = (string) $renewTenant->name;
            }
        }

        // Prevent reserved subdomains
        $reserved = ['www','admin','api','demo','test','pay','billing','support'];
        if (in_array($slug, $reserved, true)) {
            return response()->json(['error' => 'This subdomain is reserved.'], 422);
        }

        // Check availability against system.tenants
        $exists = Tenant::on('system')->where(function ($query) use ($slug) {
            $query->where('subdomain', $slug)
                ->orWhere('domain', $slug.'.'.config('app.domain', 'yourdomain.com'));
        });
        if ($renewTenant) {
            $exists->where('id', '!=', $renewTenant->id);
        }
        if ($exists->exists()) {
            return response()->json(['error' => 'This subdomain is already taken.'], 422);
        }

        $plan = Plan::where('code', $data['plan_code'])->where('active', true)->firstOrFail();
        $amount = (float) $plan->price;

        if ($amount < 5) {
            return response()->json(['error' => 'Paylink requires a minimum invoice amount of 5 SAR.'], 422);
        }

        $orderNumber = ($renewTenant ? 'renew-' : 'signup-') . Str::lower((string) Str::ulid());
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
                'note' => ($renewTenant ? 'Tenant renewal for ' : 'Tenant signup for ') . $slug,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        $transactionNo = (string) ($paylinkResponse['transactionNo'] ?? '');

        DB::connection('system')->transaction(function () use ($plan, $data, $slug, $amount, $orderNumber, $products, $paylinkResponse, $transactionNo, $renewTenant) {
            $signup = [
                'slug' => $slug,
                'plan_code' => $plan->code,
                'email' => $data['email'],
                'name' => $data['name'] ?? null,
                'mobile' => $data['mobile'],
                'renew' => (bool) $renewTenant,
                'tenant_id' => $renewTenant?->id,
            ];

            Invoice::create([
                'tenant_id' => $renewTenant?->id,
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
                    'signup' => $signup,
                ],
            ]);

            Payment::create([
                'tenant_id' => $renewTenant?->id,
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
                    'signup' => $signup,
                ],
                'receipt_url' => null,
            ]);

            Event::create([
                'tenant_id' => $renewTenant?->id,
                'provider_event_id' => 'checkout:' . $orderNumber,
                'type' => $renewTenant ? 'paylink.invoice.renewal_created' : 'paylink.invoice.created',
                'payload' => [
                    'provider' => 'paylink',
                    'order_number' => $orderNumber,
                    'transaction_no' => $transactionNo,
                    'plan_code' => $plan->code,
                    'slug' => $slug,
                    'email' => $data['email'],
                    'name' => $data['name'] ?? null,
                    'mobile' => $data['mobile'],
                    'renew' => (bool) $renewTenant,
                    'tenant_id' => $renewTenant?->id,
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

    private function renewalTenant(Request $request): ?Tenant
    {
        $session = $this->cookie->read($request);
        if (! $session || $session['is_owner'] || empty($session['tenant_id'])) {
            return null;
        }

        return Tenant::on('system')->find($session['tenant_id']);
    }
}

