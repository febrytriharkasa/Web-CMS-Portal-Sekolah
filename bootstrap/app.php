<?php

use App\Http\Middleware\CompressResponse;
use App\Http\Middleware\EnsureActiveUser;
use App\Http\Middleware\EnsureAdminAccess;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Middleware\ForceHttps;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            RateLimiter::for('public', function (Request $request) {
                return Limit::perMinute(120)
                    ->by($request->ip())
                    ->response(function (Request $request, array $headers) {
                        return response('Terlalu banyak permintaan. Coba lagi sebentar.', 429, $headers);
                    });
            });

            RateLimiter::for('search', function (Request $request) {
                return Limit::perMinute(30)->by($request->ip());
            });
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            ForceHttps::class,
            CompressResponse::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'active' => EnsureActiveUser::class,
            'admin' => EnsureAdminAccess::class,
            'superadmin' => EnsureSuperAdmin::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->respond(function (Response $response, Throwable $e, Request $request) {
            if ($response->getStatusCode() !== 404 || $request->is('api/*') || $request->expectsJson()) {
                return $response;
            }

            $page = $request->is('admin', 'admin/*') ? 'Admin/NotFound' : 'Public/NotFound';

            return Inertia::render($page)->toResponse($request)->setStatusCode(404);
        });
    })->create();
