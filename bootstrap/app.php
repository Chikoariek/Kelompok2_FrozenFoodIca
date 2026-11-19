<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        $middleware->redirectTo(
            guests: function ($request) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return null;
                }
                if ($request->is('admin*') || $request->is('dashboard*') || $request->is('profile*')) {
                    session()->flash('warning', 'Sesi Anda telah berakhir demi keamanan. Silakan masuk kembali.');
                }
                return route('login');
            },
            users: function () {
                if (auth()->check() && (auth()->user()->role === 'admin' || auth()->user()->isAdmin())) {
                    return route('admin.dashboard');
                }
                return route('home');
            }
        );
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'role'  => \App\Http\Middleware\RoleMiddleware::class,
            'prevent-back-history' => \App\Http\Middleware\PreventBackHistory::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Tangani Token CSRF Kedaluwarsa (HTTP 419 Page Expired / Sesi Habis)
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi Anda telah berakhir demi keamanan. Silakan muat ulang halaman atau login kembali.',
                ], 419);
            }
            return redirect()->route('login')->with('warning', 'Sesi Anda telah berakhir demi keamanan. Silakan masuk kembali.');
        });
    })->create();
