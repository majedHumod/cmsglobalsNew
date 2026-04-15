<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
            // NOTE:
            // لا نستخدم TenantsMiddleware و DemoReadOnlyGuard بشكل عام على كل الطلبات.
            // سنطبقهما حيث نحتاج عبر alias في مجموعات المسارات الخاصة بالمستأجرين.
            // إذا رغبت في تفعيل الحماية التجريبية على نطاق محدد، استخدم alias 'demo_readonly' على مجموعة المسارات المناسبة.

            $middleware->alias([
                'role' => Spatie\Permission\Middleware\RoleMiddleware::class,
                'permission' => Spatie\Permission\Middleware\PermissionMiddleware::class,
                'role_or_permission' => Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
                 'tenants' => \App\Http\Middleware\TenantsMiddleware::class,
                 'demo_readonly' => \App\Http\Middleware\DemoReadOnlyGuard::class,
            ]);
            $middleware->validateCsrfTokens(except: [
                'webhooks/paylink',
            ]);
            // Ensure tenant switching runs BEFORE session/auth middlewares.
            // This prevents user loading from default (system) connection.
            $middleware->prependToGroup('web', \App\Http\Middleware\TenantsMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();


    