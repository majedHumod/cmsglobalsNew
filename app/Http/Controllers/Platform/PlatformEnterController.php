<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Platform\PlatformAccountCookie;
use App\Services\Platform\PlatformHandoffService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlatformEnterController extends Controller
{
    public function __construct(
        private readonly PlatformAccountCookie $cookie,
        private readonly PlatformHandoffService $handoff,
    ) {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $tenant = $request->attributes->get('tenant');
        if (! $tenant) {
            return redirect()->away($this->handoff->handoffUrl());
        }

        $payload = $this->resolvePayload($request, $tenant);
        if (! $payload) {
            return redirect()->away($this->handoff->handoffUrl());
        }

        $user = User::query()->where('email', $payload['email'])->first();
        if (! $user || ! $user->hasAnyRole(['admin', 'coach'])) {
            return redirect()->guest(route('login'));
        }

        if (Auth::check()) {
            if ((int) Auth::id() === (int) $user->id) {
                return redirect()->intended(route('dashboard'));
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * @return array{tenant_id:int,email:string,is_owner:bool}|null
     */
    private function resolvePayload(Request $request, $tenant): ?array
    {
        $token = $request->query('handoff');
        if (is_string($token) && $token !== '') {
            return $this->handoff->resolve($token, $tenant);
        }

        $cookie = $this->cookie->read($request);
        if (! $cookie || ! empty($cookie['is_owner']) || (int) $cookie['tenant_id'] !== (int) $tenant->id) {
            return null;
        }

        return $cookie;
    }
}
