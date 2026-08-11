<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->trustHosts(at: [
            'amigagracia.com',
            'railway.app',
            'localhost',
            '127.0.0.1',
        ]);
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'staff.permission' => \App\Http\Middleware\EnsureStaffPermission::class,
            'sensitive.actions' => \App\Http\Middleware\ThrottleSensitiveActions::class,
        ]);
        
        $middleware->api(append: [
            \App\Http\Middleware\UpdateUserActivity::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson() || $request->wantsJson(),
        );

        $exceptions->report(function (Throwable $e): void {
            Log::error('Unhandled exception', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'url' => request()->fullUrl(),
                'method' => request()->method(),
            ]);
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            if ($request->is('admin') || $request->is('admin/*') || $request->routeIs('filament.*')) {
                return redirect()->guest(route('filament.admin.auth.login'));
            }

            return redirect()->guest(route('login'))->withErrors([
                'message' => 'Please log in to continue.',
            ]);
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) {
            $status = 403;

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'You are not authorized to perform this action.',
                ], $status);
            }

            return response()->view('errors.generic', [
                'status' => $status,
                'title' => 'Forbidden',
                'message' => 'You do not have permission to access this resource.',
            ], $status);
        });

        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            $status = 403;

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Access denied.',
                ], $status);
            }

            return response()->view('errors.generic', [
                'status' => $status,
                'title' => 'Access denied',
                'message' => 'You do not have permission to access this resource.',
            ], $status);
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            $status = 404;

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'The requested resource was not found.',
                ], $status);
            }

            return response()->view('errors.generic', [
                'status' => $status,
                'title' => 'Page not found',
                'message' => 'The page or resource you requested could not be found.',
            ], $status);
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            $status = 404;

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'The requested route was not found.',
                ], $status);
            }

            return response()->view('errors.404', [
                'status' => $status,
                'title' => 'Page not found',
                'message' => 'The page or resource you requested could not be found.',
            ], $status);
        });

        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            $status = 405;

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Method not allowed.',
                ], $status);
            }

            return response()->view('errors.generic', [
                'status' => $status,
                'title' => 'Method not allowed',
                'message' => 'The requested method is not allowed for this route.',
            ], $status);
        });

        $exceptions->render(function (TooManyRequestsHttpException $e, Request $request) {
            $status = 429;

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Too many requests. Please try again later.',
                ], $status);
            }

            return response()->view('errors.generic', [
                'status' => $status,
                'title' => 'Too many requests',
                'message' => 'You are sending too many requests. Please wait a moment and try again.',
            ], $status);
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->validator->errors()->first() ?: 'The given data was invalid.',
                    'errors' => $e->errors(),
                ], 422);
            }

            return redirect()->back()->withInput()->withErrors($e->errors());
        });

        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your session expired. Please refresh the page and try again.',
                ], 419);
            }

            return redirect()->back()->withInput()->withErrors([
                'message' => 'Your session expired. Please refresh the page and try again.',
            ]);
        });

        /*
        $exceptions->render(function (QueryException $e, Request $request) {
            $status = 500;

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'We could not complete your request because a database error occurred.',
                ], $status);
            }

            return response()->view('errors.generic', [
                'status' => $status,
                'title' => 'Service unavailable',
                'message' => 'We could not complete your request because a database error occurred. Please try again shortly.',
            ], $status);
        });

        $exceptions->render(function (HttpException $e, Request $request) {
            $status = $e->getStatusCode();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage() ?: 'An HTTP error occurred.',
                ], $status);
            }

            return response()->view('errors.generic', [
                'status' => $status,
                'title' => 'Request error',
                'message' => $e->getMessage() ?: 'We could not process your request.',
            ], $status);
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            $status = 500;

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Something went wrong. Please try again later.',
                ], $status);
            }

            return response()->view('errors.500', [
                'status' => $status,
                'title' => 'Something went wrong',
                'message' => 'We could not complete your request right now. Please try again shortly.',
            ], $status);
        });
        */
    })->create();
