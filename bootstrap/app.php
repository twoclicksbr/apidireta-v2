<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: '',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // 404 - Not Found
        $exceptions->render(function (Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            if ($request->is('*')) {
                return response()->json([
                    'message' => 'Endpoint não encontrado',
                    'endpoint' => $request->url(),
                    'status_code' => 404,
                    'error' => 'Not Found',
                    'timestamp' => now()->format('d/m/Y H:i:s'),
                ], 404, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
        });

        // 405 - Method Not Allowed
        $exceptions->render(function (Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e, $request) {
            if ($request->is('*')) {
                return response()->json([
                    'message' => 'Método HTTP não permitido',
                    'endpoint' => $request->url(),
                    'method' => $request->method(),
                    'status_code' => 405,
                    'error' => 'Method Not Allowed',
                    'timestamp' => now()->format('d/m/Y H:i:s'),
                ], 405, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
        });

        // 401 - Unauthorized
        $exceptions->render(function (Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException $e, $request) {
            if ($request->is('*')) {
                return response()->json([
                    'message' => 'Não autorizado',
                    'endpoint' => $request->url(),
                    'status_code' => 401,
                    'error' => 'Unauthorized',
                    'timestamp' => now()->format('d/m/Y H:i:s'),
                ], 401, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
        });

        // 403 - Forbidden
        $exceptions->render(function (Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e, $request) {
            if ($request->is('*')) {
                return response()->json([
                    'message' => 'Acesso negado',
                    'endpoint' => $request->url(),
                    'status_code' => 403,
                    'error' => 'Forbidden',
                    'timestamp' => now()->format('d/m/Y H:i:s'),
                ], 403, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
        });

        // 500 - Internal Server Error
        $exceptions->render(function (Throwable $e, $request) {
            if ($request->is('*') && $e instanceof \ErrorException) {
                return response()->json([
                    'message' => 'Erro interno do servidor',
                    'endpoint' => $request->url(),
                    'status_code' => 500,
                    'error' => 'Internal Server Error',
                    'timestamp' => now()->format('d/m/Y H:i:s'),
                ], 500, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
        });
    })->create();
