<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Billing\BillingContact;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Platform\PlatformAccountCookie;
use App\Services\Platform\TenantAccessService;
use App\Services\TenantService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;
use PDOException;

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
            'redirect' => $request->query('redirect', $this->marketingUrl()),
            'subscribeUrl' => $this->subscribeUrl(),
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

        try {
            TenantService::switchToTenant($tenant);
            $user = User::query()->where('email', $email)->first();
            $ok = $user && Hash::check($data['password'], $user->password);
        } catch (QueryException|PDOException $e) {
            Log::error('platform.account.login tenant db', [
                'tenant_id' => $tenant->id,
                'db_name' => $tenant->db_name,
                'message' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'email' => 'تعذر فتح قاعدة بيانات النادي ('.$tenant->db_name.'). اربط مستخدم MySQL بهذه القاعدة في cPanel ثم أعد المحاولة.',
            ])->withInput();
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
        $redirect = $this->safeRedirect($request->query('redirect')) ?: $this->marketingUrl();

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

        try {
            TenantService::switchToTenant($tenant);
            Password::broker()->sendResetLink(['email' => $email]);
        } catch (QueryException|PDOException $e) {
            Log::error('platform.account.forgot tenant db', [
                'tenant_id' => $tenant->id,
                'db_name' => $tenant->db_name,
                'message' => $e->getMessage(),
            ]);

            return back()->with('status', 'إن كان البريد مسجلاً فسيصلك رابط إعادة التعيين.');
        } finally {
            TenantService::switchToDefault();
        }

        return back()->with('status', 'إن كان البريد مسجلاً فسيصلك رابط إعادة التعيين على بريدك، ويفتح على موقع ناديك.');
    }

    public function expired(Request $request): View
    {
        $session = $this->cookie->read($request);
        $tenantId = $session['tenant_id'] ?? $request->session()->get('tenant_id');
        $tenant = $tenantId ? Tenant::on('system')->find($tenantId) : null;

        if ($tenant) {
            $expiredContext = $this->access->expiredPageContext($tenant);
        } else {
            $expiredContext = [
                'subscriptionEndsAt' => null,
                'graceEndsAt' => null,
                'accessStatus' => '',
            ];
        }

        return view('platform.account.expired', [
            'tenant' => $tenant,
            'accountName' => $session['name'] ?? null,
            'accountEmail' => $session['email'] ?? null,
            'message' => $tenant ? ($this->access->message($tenant) ?: 'الاشتراك غير نشط حالياً.') : 'انتهت صلاحية الوصول.',
            'subscribeUrl' => $this->subscribeUrl(),
            'marketingUrl' => $this->marketingUrl(),
            'subscriptionEndsAt' => $expiredContext['subscriptionEndsAt'],
            'graceEndsAt' => $expiredContext['graceEndsAt'],
            'accessStatus' => $expiredContext['accessStatus'],
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
        $fallback = $this->marketingUrl();
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

    private function marketingUrl(): string
    {
        $url = rtrim((string) config('platform.marketing_url'), '/');

        return $url !== '' ? $url : 'https://etoscoach.com';
    }

    private function subscribeUrl(): string
    {
        $app = rtrim((string) config('platform.app_url'), '/');
        if ($app === '') {
            $app = 'https://app.etoscoach.com';
        }

        return $app.'/subscribe';
    }
}
