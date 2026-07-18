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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectUsersTo(function () {
            $user = auth()->user();
            return $user && $user->isSuperAdmin() ? '/admin' : '/dashboard';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                return response()->json(['message' => 'Your session has expired. Please try again.'], 419);
            }
            return redirect()->back()->withInput($request->except('_token', 'password', 'password_confirmation'))->with('error', 'Your session has expired. Please try again.');
        });
    })->create();
