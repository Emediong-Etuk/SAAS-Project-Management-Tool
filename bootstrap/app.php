<?php

use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Sentry\Laravel\Integration;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
        $middleware->validateCsrfTokens(except: [
            'webhook/*',
            'api/*'
        ]);

        $middleware->statefulApi();
        $middleware->append([
            ForceJsonResponse::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->wantsJson()) {
                return response()->json(
                    [
                        'message' => 'Resource not found',
                        'error' => $e->getMessage(),
                    ],
                    Response::HTTP_NOT_FOUND
                );
            }
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->wantsJson() && $e->getCode() === Response::HTTP_INTERNAL_SERVER_ERROR) {
                return response()->json([
                    'message' => 'Server Error',
                    // 'error' => 'An error occured. Please try again later',
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'code' => $e->getCode(),
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        });
        // Integration::handles($exceptions);
    })->create();
