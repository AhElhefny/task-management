<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append([
            \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        ]);

        $middleware->alias([
            'role'      => \App\Http\Middleware\CheckRole::class,
            'abilities' => CheckAbilities::class,
            'ability'   => CheckForAnyAbility::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (QueryException $exception, Request $request) {
            if ($request->is('api/*')) {
                return responseJson(
                    msg: $exception->getMessage(),
                    code: Response::HTTP_BAD_REQUEST,
                    error: true,
                    errors: ['line' => $exception->getLine(), 'file' => $exception->getFile()]
                );
            }
        });
        $exceptions->render(function (TypeError $exception, Request $request) {
            if ($request->is('api/*')) {
                return responseJson(
                    msg: $exception->getMessage(),
                    code: $exception->status ?? Response::HTTP_BAD_REQUEST,
                    error: true,
                    errors: ['line' => $exception->getLine(), 'file' => $exception->getFile()]
                );
            }
        });

        $exceptions->render(function (ErrorException $exception, Request $request) {
            if ($request->is('api/*')) {
                return responseJson(
                    msg: $exception->getMessage(),
                    code: $exception->status ?? Response::HTTP_BAD_REQUEST,
                    error: true,
                    errors: ['line' => $exception->getLine(), 'file' => $exception->getFile()]
                );
            }
        });

        $exceptions->render(function (ModelNotFoundException $exception, Request $request) {
            if ($request->is('api/*')) {
                return responseJson(
                    msg: 'Model Not Found' ?? $exception->getMessage(),
                    code: Response::HTTP_NOT_FOUND,
                    error: true,
                    errors: ['line' => $exception->getLine(), 'file' => $exception->getFile()]
                );
            }
        });

        $exceptions->render(function (NotFoundHttpException $exception, Request $request) {
            if ($request->is('api/*')) {
                return responseJson(
                    msg: 'Not Found Http' ?? $exception->getMessage(),
                    code: Response::HTTP_NOT_FOUND,
                    error: true,
                    errors: ['line' => $exception->getLine(), 'file' => $exception->getFile()]
                );
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return responseJson(
                    msg: trans('auth.unauthenticated'),
                    code: Response::HTTP_UNAUTHORIZED,
                    error: true,
                    key: 'unauthenticated'
                );
            }
        });

        $exceptions->render(function (ParseError $exception, Request $request) {
            if ($request->is('api/*')) {
                return responseJson(
                    msg: $exception->getMessage(),
                    code: Response::HTTP_INTERNAL_SERVER_ERROR,
                    error: true,
                    errors: ['line' => $exception->getLine(), 'file' => $exception->getFile()]
                );
            }
        });

        $exceptions->render(function (Error $exception, Request $request) {
            if ($request->is('api/*')) {
                return responseJson(
                    msg: $exception->getMessage(),
                    code: Response::HTTP_INTERNAL_SERVER_ERROR,
                    error: true,
                    errors: ['line' => $exception->getLine(), 'file' => $exception->getFile()]
                );
            }
        });

        $exceptions->render(function (AccessDeniedHttpException $exception, Request $request) {
            if ($request->is('api/*')) {
                return responseJson(
                    msg: 'Access Denied' ?? $exception->getMessage(),
                    code: Response::HTTP_FORBIDDEN,
                    error: true,
                    errors: ['line' => $exception->getLine(), 'file' => $exception->getFile()]
                );
            }
        });

        $exceptions->render(function (Throwable $exception, Request $request) {
            if ($request->is('api/*')) {
                return responseJson(
                    msg: $exception->getMessage(),
                    code: Response::HTTP_UNPROCESSABLE_ENTITY,
                    error: true,
                    errors: ['line' => $exception->getLine(), 'file' => $exception->getFile()]
                );
            }
        });
    })->create();
