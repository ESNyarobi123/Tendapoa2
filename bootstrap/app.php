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
    ->withMiddleware(function (Middleware $middleware): void {
        // Enable Sanctum's stateful API authentication for SPA/mobile apps
        $middleware->statefulApi();
        
        // Register custom middleware aliases
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'force.json' => \App\Http\Middleware\ForceJsonResponse::class,
            'locale' => \App\Http\Middleware\SetLocaleFromHeader::class,
        ]);

        // API: set locale from Accept-Language so responses use title/description + labels in requested language
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \App\Http\Middleware\SetLocaleFromHeader::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // For API routes (and any request expecting JSON), return localized
        // friendly error messages. Never leak raw SQL/server errors to clients.
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if (! ($request->is('api/*') || $request->wantsJson() || $request->expectsJson())) {
                return null; // let default handler manage web responses
            }

            // Validation errors — return field-level messages (already localized by Laravel)
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.errors.validation'),
                    'errors' => $e->errors(),
                ], 422);
            }

            // Authentication
            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.errors.unauthorized'),
                ], 401);
            }

            // Authorization
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: __('messages.errors.forbidden'),
                ], 403);
            }

            // Model not found / 404
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException
                || $e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.errors.not_found'),
                ], 404);
            }

            if ($e instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.errors.method_not_allowed'),
                ], 405);
            }

            if ($e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.errors.rate_limited'),
                ], 429);
            }

            // Database errors — log raw, return generic friendly message
            if ($e instanceof \Illuminate\Database\QueryException) {
                \Illuminate\Support\Facades\Log::error('API DB error', [
                    'sql' => $e->getSql() ?? null,
                    'message' => $e->getMessage(),
                    'url' => $request->fullUrl(),
                    'user_id' => optional($request->user())->id,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => __('messages.errors.database'),
                    // Include code in non-prod for debugging
                    'debug' => config('app.debug') ? $e->getMessage() : null,
                ], 500);
            }

            // HTTP exceptions with custom status — preserve status, localize message
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                $status = $e->getStatusCode();
                $msg = $e->getMessage();
                if ($status === 403) {
                    $msg = $msg ?: __('messages.errors.forbidden');
                } elseif ($status === 404) {
                    $msg = $msg ?: __('messages.errors.not_found');
                } elseif ($status >= 500) {
                    $msg = __('messages.errors.server');
                }

                return response()->json([
                    'success' => false,
                    'message' => $msg ?: __('messages.errors.generic'),
                ], $status);
            }

            // Unknown / 500: log raw, return generic localized message
            \Illuminate\Support\Facades\Log::error('API unhandled error', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'url' => $request->fullUrl(),
                'user_id' => optional($request->user())->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => __('messages.errors.server'),
                'debug' => config('app.debug') ? [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                ] : null,
            ], 500);
        });
    })->create();
