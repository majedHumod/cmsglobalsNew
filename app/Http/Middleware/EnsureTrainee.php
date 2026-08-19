<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTrainee
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('login'));
        }

        if ($user->hasTraineeRole()) {
            return $next($request);
        }

        if ($user->hasAnyRole(['admin', 'coach'])) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'صفحة العميل اليومية متاحة لحسابات المتدربين فقط. سجّل الدخول بحساب عميل أو عيّن دور user/client للمستخدم.');
        }

        abort(403, 'هذا الحساب لا يملك صلاحية الوصول كعميل. يرجى التواصل مع المدرب لتفعيل دور المتدرب (user أو client).');
    }
}
