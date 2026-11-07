<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Billing\Event;
use App\Models\Billing\Invoice;
use App\Models\Billing\Payment;
use App\Models\Billing\Plan;
use App\Models\Billing\SignupReservation;
use App\Models\Tenant;
use App\Models\TenantDatabasePool;
use App\Services\Billing\PaylinkService;
use App\Services\Billing\SignupConflictException;
use App\Services\Billing\SignupReservationService;
use App\Services\Platform\OpsAlert;
use App\Services\Platform\PlatformAccountCookie;
use App\Services\Platform\TenantAccessService;
use App\Services\Platform\TurnstileVerifier;
use App\Support\PhoneNormalizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly PaylinkService $paylink,
        private readonly PlatformAccountCookie $cookie,
        private readonly TenantAccessService $access,
        private readonly SignupReservationService $reservations,
        private readonly TurnstileVerifier $turnstile,
    ) {
    }

    public function create(Request $request)
    {
        // Honeypot: real users never see or fill this field.
        if (filled($request->input('website'))) {
            return response()->json(['error' => 'تعذر إتمام الطلب.'], 422);
        }

        $validator = Validator::make($request->all(), [
            'plan_code' => 'required|string|exists:system.plans,code',
            'subdomain' => 'required|string|min:3|max:30|regex:/^[a-z0-9-]+$/',
            'email' => 'required|email|max:190',
            'name' => 'nullable|string|max:120',
            'mobile' => 'required|string|min:8|max:20',
            'intent' => 'nullable|in:new,renew',
        ], [
            'plan_code.required' => 'يرجى اختيار خطة الاشتراك.',
            'plan_code.exists' => 'الخطة المختارة غير متاحة.',
            'subdomain.required' => 'سب-دومين النادي مطلوب.',
            'subdomain.min' => 'يجب أن يكون السب-دومين 3 أحرف على الأقل.',
            'subdomain.max' => 'السب-دومين طويل جداً (30 حرفاً كحد أقصى).',
            'subdomain.regex' => 'استخدم أحرفاً صغيرة وأرقاماً وشرطة (-) فقط.',
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email' => 'أدخل بريداً إلكترونياً صالحاً.',
            'mobile.required' => 'رقم الجوال مطلوب لإتمام الدفع عبر Paylink.',
            'mobile.min' => 'رقم الجوال قصير جداً.',
            'mobile.max' => 'رقم الجوال طويل جداً.',
        ], [
            'plan_code' => 'الخطة',
            'subdomain' => 'السب-دومين',
            'email' => 'البريد الإلكتروني',
            'name' => 'الاسم',
            'mobile' => 'رقم الجوال',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['email'] = SignupReservationService::normalizeEmail($data['email']);
        $slug = strtolower($data['subdomain']);

        $renewTenant = $this->renewalTenant($request);
        // Be explicit: a logged-in customer must never pay for a "new" club and silently renew the old one.
        $intent = $data['intent'] ?? ($renewTenant ? 'renew' : 'new');

        if ($intent === 'renew' && ! $renewTenant) {
            return $this->fail('سجّل الدخول أولاً لتجديد اشتراكك.', 'login_required', 401, ['login_url' => $this->loginUrl()]);
        }

        if ($intent === 'new' && $renewTenant) {
            return $this->fail(
                'لديك نادٍ مرتبط بحسابك بالفعل. يدعم النظام حالياً نادياً واحداً لكل بريد، يمكنك تجديد اشتراكك من هذه الصفحة.',
                'already_has_club'
            );
        }

        if ($renewTenant) {
            $this->access->sync($renewTenant);
            if ($this->access->canUseWorkspace($renewTenant)) {
                return response()->json([
                    'error' => 'اشتراكك ساري حالياً. لا حاجة للتجديد الآن — يمكنك الدخول إلى لوحة التحكم مباشرة.',
                ], 422);
            }

            $tenantSlug = strtolower((string) ($renewTenant->subdomain ?: explode('.', (string) $renewTenant->domain)[0]));
            if ($slug !== $tenantSlug) {
                return $this->fail('لا يمكن تغيير سب-دومين النادي عند التجديد.', 'slug_mismatch');
            }

            $slug = $tenantSlug;
            $data['subdomain'] = $slug;
            if (empty($data['email'])) {
                $data['email'] = SignupReservationService::normalizeEmail((string) $renewTenant->email);
            }
            if (empty($data['name'])) {
                $data['name'] = (string) $renewTenant->name;
            }
        }

        $reserved = ['www', 'admin', 'api', 'demo', 'test', 'pay', 'billing', 'support', 'app', 'mail', 'ftp'];
        if (in_array($slug, $reserved, true)) {
            return response()->json(['error' => 'هذا السب-دومين محجوز ولا يمكن استخدامه.'], 422);
        }

        $exists = Tenant::on('system')->where(function ($query) use ($slug) {
            $query->where('subdomain', $slug)
                ->orWhere('domain', $slug.'.'.config('app.domain', 'yourdomain.com'));
        });
        if ($renewTenant) {
            $exists->where('id', '!=', $renewTenant->id);
        }
        if ($exists->exists()) {
            return response()->json(['error' => 'هذا السب-دومين مستخدم بالفعل. جرّب اسماً آخر.'], 422);
        }

        $mobileE164 = PhoneNormalizer::toE164($data['mobile']);

        if (! $renewTenant) {
            if (! $mobileE164) {
                return response()->json(['errors' => ['mobile' => ['أدخل رقم جوال صحيحاً، مثال: 05xxxxxxxx']]], 422);
            }

            if (config('platform.signup.verify_email')) {
                if (! SignupEmailVerificationController::isVerified($data['email'])) {
                    return $this->fail('يرجى التحقق من بريدك الإلكتروني أولاً عبر الرمز المرسل إليك.', 'email_unverified');
                }
            } elseif (! $this->turnstile->passes($request->input('cf-turnstile-response'), $request->ip())) {
                return $this->fail('فشل التحقق الأمني. حدّث الصفحة وأعد المحاولة.', 'captcha_failed');
            }

            if (! $this->hasCapacityForNewTenant()) {
                OpsAlert::critical(
                    'نفدت قواعد بيانات النوادي الجاهزة',
                    'تم رفض اشتراك جديد لأنه لا توجد قاعدة بيانات جاهزة في الـ pool. سجّل قواعد جديدة (php artisan tenants:pool-register <db>) ثم أعد فتح التسجيل.',
                    'pool-empty',
                    30
                );

                return $this->fail('التسجيل الجديد متوقف مؤقتاً لأسباب تقنية. يرجى المحاولة لاحقاً أو التواصل مع الدعم.', 'capacity', 503);
            }
        }

        $plan = Plan::where('code', $data['plan_code'])->where('active', true)->firstOrFail();
        $amount = (float) $plan->price;

        if ($amount < 5) {
            return response()->json(['error' => 'يتطلب Paylink حداً أدنى للفاتورة 5 ريال.'], 422);
        }

        $orderNumber = ($renewTenant ? 'renew-' : 'signup-').Str::lower((string) Str::ulid());

        // Hold the subdomain + email while the buyer is on the payment page.
        $reservation = null;
        if (! $renewTenant) {
            try {
                $reservation = $this->reservations->reserve($orderNumber, $slug, $data['email'], $mobileE164, $plan->code);
            } catch (SignupConflictException $e) {
                return $this->fail(
                    $e->getMessage(),
                    $e->reason,
                    422,
                    $e->reason === 'email_in_use' ? ['login_url' => $this->loginUrl()] : []
                );
            }
        }

        $callbackUrl = config('services.paylink.callback_url') ?: route('billing.paylink.callback');
        $cancelUrl = config('services.paylink.cancel_url') ?: route('subscribe');
        $products = [[
            'title' => $plan->name,
            'price' => $amount,
            'qty' => 1,
            'description' => 'Tenant subscription plan '.$plan->code,
            'isDigital' => true,
        ]];

        try {
            $paylinkResponse = $this->paylink->createInvoice([
                'orderNumber' => $orderNumber,
                'amount' => $amount,
                'callBackUrl' => $callbackUrl,
                'cancelUrl' => $cancelUrl,
                'clientName' => ($data['name'] ?? null) ?: Str::title(str_replace('-', ' ', $slug)),
                'clientEmail' => $data['email'],
                'clientMobile' => $data['mobile'],
                'currency' => $plan->currency ?: config('services.paylink.currency', 'SAR'),
                'products' => $products,
                'supportedCardBrands' => config('services.paylink.supported_card_brands', []),
                'displayPending' => true,
                'note' => ($renewTenant ? 'Tenant renewal for ' : 'Tenant signup for ').$slug,
            ]);
        } catch (\Throwable $e) {
            $this->reservations->release($orderNumber);
            report($e);

            return response()->json(['error' => 'تعذر إنشاء فاتورة الدفع. حاول لاحقاً أو تواصل مع الدعم.'], 422);
        }

        $transactionNo = (string) ($paylinkResponse['transactionNo'] ?? '');

        try {
            DB::connection('system')->transaction(function () use ($plan, $data, $slug, $amount, $orderNumber, $products, $paylinkResponse, $transactionNo, $renewTenant, $mobileE164) {
                $signup = [
                    'slug' => $slug,
                    'plan_code' => $plan->code,
                    'email' => $data['email'],
                    'name' => $data['name'] ?? null,
                    'mobile' => $data['mobile'],
                    'mobile_e164' => $mobileE164,
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
                    'provider_event_id' => 'checkout:'.$orderNumber,
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
                        'mobile_e164' => $mobileE164,
                        'renew' => (bool) $renewTenant,
                        'tenant_id' => $renewTenant?->id,
                        'paylink_response' => $paylinkResponse,
                    ],
                    'processed_at' => now(),
                ]);
            });
        } catch (\Throwable $e) {
            // The Paylink invoice exists but we could not record it: free the name so the buyer can retry.
            $this->reservations->release($orderNumber);
            report($e);

            return response()->json(['error' => 'تعذر تسجيل طلب الدفع. حاول لاحقاً أو تواصل مع الدعم.'], 422);
        }

        if ($request->expectsJson() || str_contains($request->header('Accept', ''), 'application/json')) {
            return response()->json([
                'message' => 'تم إنشاء الفاتورة بنجاح. سيتم تحويلك إلى Paylink.',
                'subdomain' => $slug,
                'plan' => $plan->only(['code', 'name', 'price', 'interval', 'currency']),
                'redirect_url' => $paylinkResponse['url'] ?? null,
                'mobile_url' => $paylinkResponse['mobileUrl'] ?? null,
                'transaction_no' => $transactionNo,
                'order_number' => $orderNumber,
            ]);
        }

        return redirect()->away($paylinkResponse['url'] ?? route('subscribe'));
    }

    private function hasCapacityForNewTenant(): bool
    {
        if (! config('platform.signup.require_free_database')) {
            return true;
        }

        $this->reservations->releaseExpired();

        $free = TenantDatabasePool::query()
            ->available()
            ->whereNotIn('db_name', function ($query) {
                $query->select('db_name')->from('tenants')->whereNotNull('db_name');
            })
            ->count();

        $held = SignupReservation::query()
            ->whereIn('status', [SignupReservation::PENDING, SignupReservation::PAID])
            ->count();

        return ($free - $held) > 0;
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function fail(string $message, string $code, int $status = 422, array $extra = []): JsonResponse
    {
        return response()->json(['error' => $message, 'code' => $code] + $extra, $status);
    }

    private function loginUrl(): string
    {
        $app = rtrim((string) config('platform.app_url'), '/') ?: 'https://app.etoscoach.com';

        return $app.'/account/login?redirect='.urlencode($app.'/subscribe');
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
