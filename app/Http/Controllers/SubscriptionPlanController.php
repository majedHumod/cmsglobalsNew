<?php

namespace App\Http\Controllers;

use App\Models\MembershipType;
use App\Models\SubscriptionPlan;
use App\Models\UserMembership;
use App\Services\MembershipRenewalService;
use App\Services\Payments\CoachSubscriptionCheckout;
use App\Services\TenantCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SubscriptionPlanController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin'])->except(['publicIndex', 'subscribe', 'payment', 'startPayment', 'paymentReturn', 'submitBankTransfer', 'success', 'renew']);
        $this->middleware('auth')->only(['subscribe', 'payment', 'startPayment', 'submitBankTransfer', 'success', 'renew']);
    }

    public function index()
    {
        $plans = SubscriptionPlan::with('membershipType')->ordered()->get();

        return view('subscription-plans.index', compact('plans'));
    }

    public function create(Request $request)
    {
        $membershipTypes = MembershipType::active()->ordered()->get();
        $selectedMembershipTypeId = $request->integer('membership_type_id') ?: null;

        return view('subscription-plans.create', compact('membershipTypes', 'selectedMembershipTypeId'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePlan($request);

        $validated['slug'] = $this->uniqueSlug($validated['name']);
        $validated['features'] = $this->normalizeFeatures($validated['features'] ?? []);
        $validated['is_active'] = $request->boolean('is_active');

        SubscriptionPlan::create($validated);

        $this->forgetHomepagePlansCache();

        return redirect()->route('subscription-plans.index')->with('success', 'تم إنشاء خطة الاشتراك بنجاح.');
    }

    public function edit(SubscriptionPlan $subscriptionPlan)
    {
        $membershipTypes = MembershipType::active()->ordered()->get();

        return view('subscription-plans.edit', compact('subscriptionPlan', 'membershipTypes'));
    }

    public function update(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        $validated = $this->validatePlan($request);

        if ($subscriptionPlan->name !== $validated['name']) {
            $validated['slug'] = $this->uniqueSlug($validated['name'], $subscriptionPlan->id);
        }

        $validated['features'] = $this->normalizeFeatures($validated['features'] ?? []);
        $validated['is_active'] = $request->boolean('is_active');

        $subscriptionPlan->update($validated);

        $this->forgetHomepagePlansCache();

        return redirect()->route('subscription-plans.index')->with('success', 'تم تحديث خطة الاشتراك بنجاح.');
    }

    public function destroy(SubscriptionPlan $subscriptionPlan)
    {
        if ($subscriptionPlan->memberships()->exists()) {
            return back()->with('error', 'لا يمكن حذف الخطة لوجود اشتراكات مرتبطة بها.');
        }

        $subscriptionPlan->delete();

        $this->forgetHomepagePlansCache();

        return redirect()->route('subscription-plans.index')->with('success', 'تم حذف خطة الاشتراك بنجاح.');
    }

    public function publicIndex()
    {
        $plans = SubscriptionPlan::query()
            ->active()
            ->with('membershipType')
            ->ordered()
            ->get();

        return view('subscription-plans.public', compact('plans'));
    }

    public function subscribe(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        abort_unless($subscriptionPlan->is_active, 404);

        $membership = UserMembership::create([
            'user_id' => auth()->id(),
            'membership_type_id' => $subscriptionPlan->membership_type_id,
            'subscription_plan_id' => $subscriptionPlan->id,
            'starts_at' => null,
            'expires_at' => null,
            'is_active' => false,
            'payment_status' => $subscriptionPlan->price > 0 ? 'pending' : 'paid',
            'payment_amount' => $subscriptionPlan->price,
            'notes' => $request->input('notes'),
        ]);

        if ((float) $subscriptionPlan->price === 0.0) {
            app(MembershipRenewalService::class)->activate($membership);

            return redirect()->route('subscription-plans.success', $membership)
                ->with('success', 'تم تفعيل الاشتراك المجاني بنجاح.');
        }

        return redirect()->route('subscription-plans.payment', $membership);
    }

    public function renew(UserMembership $userMembership)
    {
        abort_unless($userMembership->user_id === auth()->id(), 403);
        abort_unless($userMembership->subscriptionPlan, 404);

        if ((float) $userMembership->subscriptionPlan->price === 0.0) {
            app(MembershipRenewalService::class)->renew($userMembership);

            return redirect()->route('subscription-plans.success', $userMembership)
                ->with('success', 'تم تجديد الاشتراك بنجاح.');
        }

        $userMembership->update([
            'payment_status' => 'pending',
            'payment_amount' => $userMembership->subscriptionPlan->price,
        ]);

        return redirect()->route('subscription-plans.payment', $userMembership);
    }

    public function payment(UserMembership $userMembership, CoachSubscriptionCheckout $checkout)
    {
        abort_unless($userMembership->user_id === auth()->id(), 403);
        abort_unless($userMembership->subscriptionPlan, 404);

        if ($userMembership->payment_status === 'paid' && $userMembership->is_active) {
            return redirect()->route('subscription-plans.success', $userMembership);
        }

        return view('subscription-plans.payment', [
            'membership' => $userMembership->load('subscriptionPlan.membershipType'),
            'methods' => $checkout->methods(),
        ]);
    }

    public function startPayment(Request $request, UserMembership $userMembership, CoachSubscriptionCheckout $checkout)
    {
        abort_unless($userMembership->user_id === auth()->id(), 403);
        abort_unless($userMembership->subscriptionPlan, 404);

        $validated = $request->validate([
            'channel' => 'required|string|max:50',
        ]);

        try {
            $result = $checkout->start($userMembership, $validated['channel']);
        } catch (\Throwable $exception) {
            Log::error('Coach subscription checkout failed', ['error' => $exception->getMessage()]);

            return back()->with('error', $this->checkoutErrorMessage($exception));
        }

        return $this->checkoutResponse($result, $userMembership);
    }

    public function submitBankTransfer(Request $request, UserMembership $userMembership, CoachSubscriptionCheckout $checkout)
    {
        abort_unless($userMembership->user_id === auth()->id(), 403);

        $validated = $request->validate([
            'transfer_reference' => 'required|string|max:100',
            'transfer_receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

        $receiptPath = $request->file('transfer_receipt')?->store('payment-receipts', 'public');

        try {
            $checkout->submitBankTransfer($userMembership, $validated['transfer_reference'], $receiptPath);
        } catch (\Throwable $exception) {
            if ($receiptPath) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($receiptPath);
            }

            return back()->with('error', $this->checkoutErrorMessage($exception));
        }

        return redirect()
            ->route('subscription-plans.payment', $userMembership)
            ->with('success', 'تم تسجيل التحويل. يبقى الاشتراك معلقاً حتى يؤكد النادي استلام المبلغ.');
    }

    public function paymentReturn(Request $request, UserMembership $userMembership, CoachSubscriptionCheckout $checkout)
    {
        try {
            $paid = $checkout->verifyAndSettle($userMembership, $request);
        } catch (\Throwable $exception) {
            Log::error('Coach subscription payment verification failed', ['error' => $exception->getMessage()]);

            return $this->paymentResultRedirect($userMembership, false, 'تعذر التحقق من الدفع.');
        }

        if (! $paid) {
            return $this->paymentResultRedirect($userMembership, false, 'لم يكتمل الدفع بعد. إذا اخترت تحويلاً بنكياً فسيبقى الطلب معلقاً حتى التأكيد.');
        }

        return $this->paymentResultRedirect($userMembership, true, null);
    }

    public function success(UserMembership $userMembership)
    {
        abort_unless($userMembership->user_id === auth()->id(), 403);

        if ($userMembership->payment_status !== 'paid') {
            return redirect()->route('subscription-plans.payment', $userMembership)
                ->with('error', 'لم يُفعَّل الاشتراك بعد.');
        }

        return view('subscription-plans.success', [
            'membership' => $userMembership->load('subscriptionPlan.membershipType'),
        ]);
    }

    /**
     * @param  array{type: string, url?: string, view?: string, data?: array<string, mixed>, message?: string}  $result
     */
    protected function checkoutResponse(array $result, UserMembership $userMembership)
    {
        return match ($result['type']) {
            'redirect' => redirect()->away($result['url']),
            'view' => view($result['view'], $result['data'] ?? []),
            'already_paid' => redirect()->route('subscription-plans.success', $userMembership),
            default => back()->with('error', $result['message'] ?? 'تعذر بدء الدفع.'),
        };
    }

    protected function checkoutErrorMessage(\Throwable $exception): string
    {
        // These exceptions carry curated Arabic messages meant for the customer
        // (missing credentials, missing mobile number, gateway rejection, etc.).
        if ($exception instanceof \RuntimeException || $exception instanceof \Illuminate\Validation\ValidationException) {
            return $exception->getMessage();
        }

        return 'تعذر بدء الدفع. تأكد من إعدادات قناة الدفع أو حاول لاحقاً.';
    }

    protected function paymentResultRedirect(UserMembership $userMembership, bool $paid, ?string $error)
    {
        if (! auth()->check() || auth()->id() !== $userMembership->user_id) {
            return redirect()->route('login');
        }

        if ($paid) {
            return redirect()->route('subscription-plans.success', $userMembership);
        }

        return redirect()->route('subscription-plans.payment', $userMembership)->with('error', $error);
    }

    protected function validatePlan(Request $request): array
    {
        $validated = $request->validate([
            'membership_type_id' => 'required|exists:membership_types,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_days' => 'required|integer|min:1|max:100000',
            'price' => 'required|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0',
            'gender_scope' => 'required|in:all,male,female',
            'features' => 'nullable|array',
            'features.*' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ], [
            'duration_days.max' => 'مدة الاشتراك كبيرة جداً.',
            'membership_type_id.exists' => 'مسار العضوية المحدد غير موجود.',
            'compare_at_price.min' => 'السعر قبل الخصم يجب أن يكون صفراً أو أكبر.',
        ]);

        if (
            array_key_exists('compare_at_price', $validated)
            && $validated['compare_at_price'] !== null
            && $validated['compare_at_price'] !== ''
            && (float) $validated['compare_at_price'] > 0
            && (float) $validated['compare_at_price'] <= (float) $validated['price']
        ) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'compare_at_price' => 'السعر قبل الخصم يجب أن يكون أعلى من سعر البيع ليظهر للعميل كقيمة موفّرة.',
            ]);
        }

        if (($validated['compare_at_price'] ?? null) === '' || ($validated['compare_at_price'] ?? null) === null) {
            $validated['compare_at_price'] = null;
        }

        return $validated;
    }

    /**
     * @param  array<int, string|null>  $features
     * @return array<int, string>
     */
    protected function normalizeFeatures(array $features): array
    {
        return array_values(array_filter(array_map(static fn ($feature) => trim((string) $feature), $features)));
    }

    protected function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'plan-'.Str::lower(Str::random(8));
        }

        $slug = $base;
        $counter = 1;

        while (
            SubscriptionPlan::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected function activateMembership(UserMembership $membership): void
    {
        app(MembershipRenewalService::class)->activate($membership, $membership->stripe_payment_intent_id);
    }

    protected function forgetHomepagePlansCache(): void
    {
        Cache::forget(TenantCache::key('homepage_subscription_plans'));
    }
}

