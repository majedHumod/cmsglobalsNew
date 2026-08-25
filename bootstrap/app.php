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
                 'verify_webhook_signature' => \App\Http\Middleware\VerifyCommunicationWebhookSignature::class,
                 'trainee' => \App\Http\Middleware\EnsureTrainee::class,
                 'set_locale' => \App\Http\Middleware\SetRequestLocale::class,
                 'tenant.access' => \App\Http\Middleware\EnforceTenantAccess::class,
            ]);
            $middleware->validateCsrfTokens(except: [
                'webhooks/paylink',
            ]);
            $middleware->encryptCookies(except: [
                env('PLATFORM_COOKIE', 'etos_platform'),
            ]);

            // Allow same-origin browser sessions (cookies) to authenticate API routes.
            $middleware->statefulApi();

            // Ensure tenant switching runs BEFORE session/auth middlewares.
            // This prevents user loading from default (system) connection.
            $middleware->prependToGroup('web', \App\Http\Middleware\TenantsMiddleware::class);
            $middleware->prependToGroup('api', \App\Http\Middleware\TenantsMiddleware::class);
            $middleware->appendToGroup('web', \App\Http\Middleware\EnforceTenantAccess::class);
            $middleware->appendToGroup('api', \Illuminate\Cookie\Middleware\EncryptCookies::class);
            $middleware->appendToGroup('api', \App\Http\Middleware\EnforceTenantAccess::class);
            $middleware->appendToGroup('api', \App\Http\Middleware\SetRequestLocale::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();


    