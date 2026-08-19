<?php

namespace App\Http\Controllers;

use App\Models\MembershipType;
use App\Models\SubscriptionPlan;
use App\Models\UserMembership;
use App\Events\MembershipLifecycleChanged;
use App\Services\MembershipRenewalService;
use App\Services\TenantCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class SubscriptionPlanController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin'])->except(['publicIndex', 'subscribe', 'payment', 'success', 'renew']);
        $this->middleware('auth')->only(['subscribe', 'payment', 'success', 'renew']);
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

    public function payment(UserMembership $userMembership)
    {
        abort_unless($userMembership->user_id === auth()->id(), 403);
        abort_unless($userMembership->subscriptionPlan, 404);

        try {
            Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

            $paymentIntent = PaymentIntent::create([
                'amount' => (int) round($userMembership->payment_amount * 100),
                'currency' => 'sar',
                'metadata' => [
                    'membership_id' => $userMembership->id,
                    'user_id' => $userMembership->user_id,
                    'subscription_plan_id' => $userMembership->subscription_plan_id,
                ],
            ]);

            $userMembership->update([
                'stripe_payment_intent_id' => $paymentIntent->id,
            ]);

            return view('subscription-plans.payment', [
                'membership' => $userMembership->load('subscriptionPlan.membershipType'),
                'paymentIntent' => $paymentIntent,
            ]);
        } catch (\Exception $e) {
            Log::error('Subscription payment setup failed', ['error' => $e->getMessage()]);

            return back()->with('error', 'تعذر تجهيز عملية الدفع حالياً.');
        }
    }

    public function success(UserMembership $userMembership, MembershipRenewalService $renewalService)
    {
        abort_unless($userMembership->user_id === auth()->id(), 403);

        if ($userMembership->stripe_payment_intent_id) {
            try {
                Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
                $intent = PaymentIntent::retrieve($userMembership->stripe_payment_intent_id);
                if ($intent->status !== 'succeeded') {
                    return redirect()->route('subscription-plans.payment', $userMembership)
                        ->with('error', 'لم يتم تأكيد الدفع بعد. أكمل عملية الدفع أولاً.');
                }
            } catch (\Exception $e) {
                Log::error('Subscription payment verification failed', ['error' => $e->getMessage()]);

                return redirect()->route('subscription-plans.payment', $userMembership)
                    ->with('error', 'تعذر التحقق من الدفع.');
            }
        }

        $isRenewal = $userMembership->starts_at !== null;
        if ($isRenewal) {
            $renewalService->renew($userMembership);
        } else {
            $renewalService->activate($userMembership, $userMembership->stripe_payment_intent_id);
        }

        return view('subscription-plans.success', [
            'membership' => $userMembership->fresh()->load('subscriptionPlan.membershipType'),
        ]);
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

