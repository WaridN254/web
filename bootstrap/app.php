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
        $middleware->trustProxies(at: '*');
        $middleware->web(append: [
            \App\Http\Middleware\SetBranch::class,
        ]);
        $middleware->alias([
            'platform.auth' => \App\Http\Middleware\EnsurePlatformAuthenticated::class,
            'platform.permission' => \App\Http\Middleware\EnsurePlatformPermission::class,
            'onboarding' => \App\Http\Middleware\EnsureOnboardingComplete::class,
        ]);
        $middleware->redirectGuestsTo(function ($request) {
            if ($request->is('platform/*')) {
                return route('platform.login');
            }
            if ($request->is('admin/*')) {
                return route('filament.admin.auth.login');
            }
            return route('filament.tenant.auth.login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
