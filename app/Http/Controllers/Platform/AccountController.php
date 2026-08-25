<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Billing\BillingContact;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Platform\PlatformAccountCookie;
use App\Services\Platform\TenantAccessService;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function __construct(
        private readonly PlatformAccountCookie $cookie,
        private readonly TenantAccessService $access,
    ) {
    }

    public function loginForm(Request $request): View
    {
        return view('platform.account.login', [
            'redirect' => $request->query('redirect', config('platform.marketing_url')),
            'subscribeUrl' => config('platform.app_url').'/subscribe',
        ]);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'redirect' => 'nullable|url',
        ]);

        $email = strtolower(trim($data['email']));
        $redirect = $this->safeRedirect($data['redirect'] ?? null);

        if ($this->isOwner($email) && $this->ownerPasswordOk($data['password'])) {
            $placeholder = new Tenant(['id' => 0, 'name' => 'EtosCoach', 'domain' => parse_url((string) config('platform.app_url'), PHP_URL_HOST)]);
            $placeholder->id = 0;

            return redirect()
                ->to($redirect)
                ->withCookie($this->cookie->put($placeholder, $email, 'مالك المنصة', true));
        }

        $tenant = $this->findTenantByEmail($email);
        if (! $tenant) {
            return back()->withErrors(['email' => 'لا يوجد حساب مرتبط بهذا البريد.'])->withInput();
        }

        $this->access->sync($tenant);

        if ($tenant->access_status === TenantAccessService::ARCHIVED) {
            return redirect()
                ->route('platform.account.expired')
                ->with('tenant_id', $tenant->id)
                ->withErrors(['email' => $this->access->message($tenant)]);
        }

        TenantService::switchToTenant($tenant);
        try {
            $user = User::query()->where('email', $email)->first();
            $ok = $user && Hash::check($data['password'], $user->password);
        } finally {
            TenantService::switchToDefault();
        }

        if (! $ok) {
            return back()->withErrors(['email' => 'بيانات الدخول غير صحيحة.'])->withInput();
        }

        $cookie = $this->cookie->put($tenant, $email, $user->name ?: $tenant->name);

        if (! $this->access->canUseWorkspace($tenant)) {
            return redirect()
                ->route('platform.account.expired')
                ->withCookie($cookie);
        }

        return redirect()->to($redirect)->withCookie($cookie);
    }

    public function logout(Request $request)
    {
        $redirect = $this->safeRedirect($request->query('redirect')) ?: config('platform.marketing_url');

        return redirect()->to($redirect)->withCookie($this->cookie->forget());
    }

    public function forgotForm(): View
    {
        return view('platform.account.forgot', [
            'loginUrl' => route('platform.account.login'),
        ]);
    }

    public function forgot(Request $request)
    {
        $data = $request->validate(['email' => 'required|email']);
        $email = strtolower(trim($data['email']));
        $tenant = $this->findTenantByEmail($email);

        if (! $tenant) {
            return back()->with('status', 'إن كان البريد مسجلاً فسيصلك رابط إعادة التعيين.');
        }

        TenantService::switchToTenant($tenant);
        try {
            Password::broker()->sendResetLink(['email' => $email]);
        } finally {
            TenantService::switchToDefault();
        }

        return back()->with('status', 'إن كان البريد مسجلاً فسيصلك رابط إعادة التعيين على بريدك، ويفتح على موقع ناديك.');
    }

    public function expired(Request $request): View
    {
        $session = $this->cookie->read($request);
        $tenant = $session && $session['tenant_id']
            ? Tenant::on('system')->find($session['tenant_id'])
            : null;

        if ($tenant) {
            $this->access->sync($tenant);
        }

        return view('platform.account.expired', [
            'tenant' => $tenant,
            'message' => $tenant ? $this->access->message($tenant) : 'انتهت صلاحية الوصول.',
            'subscribeUrl' => config('platform.app_url').'/subscribe',
        ]);
    }

    private function findTenantByEmail(string $email): ?Tenant
    {
        $tenant = Tenant::on('system')->whereRaw('LOWER(email) = ?', [$email])->first();
        if ($tenant) {
            return $tenant;
        }

        $contact = BillingContact::query()->whereRaw('LOWER(email) = ?', [$email])->first();
        if ($contact) {
            return Tenant::on('system')->find($contact->tenant_id);
        }

        return null;
    }

    private function isOwner(string $email): bool
    {
        return in_array($email, config('platform.owner_emails', []), true);
    }

    private function ownerPasswordOk(string $password): bool
    {
        $expected = (string) config('platform.owner_password');

        return $expected !== '' && hash_equals($expected, $password);
    }

    private function safeRedirect(?string $url): string
    {
        $fallback = (string) config('platform.marketing_url');
        if (! $url) {
            return $fallback;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $allowed = array_map('strtolower', config('platform.hosts', []));
        $root = strtolower((string) parse_url($fallback, PHP_URL_HOST));
        if ($host === $root || in_array($host, $allowed, true) || Str::endsWith($host, '.'.config('app.domain'))) {
            return $url;
        }

        return $fallback;
    }
}
