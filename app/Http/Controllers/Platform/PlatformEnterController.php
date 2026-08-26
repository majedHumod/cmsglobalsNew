<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Platform\PlatformAccountCookie;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlatformEnterController extends Controller
{
    public function __construct(private readonly PlatformAccountCookie $cookie)
    {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $tenant = $request->attributes->get('tenant');
        if (! $tenant) {
            return redirect()->away(rtrim((string) config('platform.app_url'), '/').'/account/login');
        }

        $payload = $this->cookie->read($request);
        if (! $payload || ! empty($payload['is_owner']) || (int) $payload['tenant_id'] !== (int) $tenant->id) {
            return redirect()->guest(route('login'));
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
}
