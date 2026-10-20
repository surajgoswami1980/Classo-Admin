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
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'super.admin' => \App\Http\Middleware\SuperAdminMiddleware::class,
            'school.tenant' => \App\Http\Middleware\SchoolTenantMiddleware::class,
            'ensure.role' => \App\Http\Middleware\EnsureRole::class,
            'resource.permission' => \App\Http\Middleware\CheckResourcePermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Pages that need a school context abort(422, 'Select a school …') when a
        // super admin opens them without "Switch School". Show a friendly in-panel
        // page with a school picker instead of the framework error screen.
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e, \Illuminate\Http\Request $request) {
            if ($e->getStatusCode() !== 422 || !str_contains($e->getMessage(), 'Select a school')) {
                return null;
            }
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage(), 'code' => 'school_required'], 422);
            }
            if (!auth()->check()) {
                return null;
            }
            // Form posts go back with a normal flash error; page visits get the picker.
            if (!$request->isMethod('GET')) {
                return back()->with('error', $e->getMessage());
            }
            return response()->view('errors.select-school', [
                'message' => $e->getMessage(),
                'returnTo' => '/' . ltrim($request->path(), '/') . ($request->getQueryString() ? '?' . $request->getQueryString() : ''),
            ], 422);
        });
    })->create();
