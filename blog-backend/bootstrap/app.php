<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Render (y otros hosts) ponen la app detrás de un proxy. Hay que
        // confiar en él para que Laravel detecte HTTPS correctamente; si no,
        // las cookies de sesión fallan y el login "no entra" (rebota al login).
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        // Validación → { message, errors: { campo: [msg] } }
        $exceptions->render(function (ValidationException $e, Request $request) {
            if (! $request->is('api/*')) return null;
            return response()->json([
                'message' => 'Los datos enviados no son válidos.',
                'errors'  => $e->errors(),
            ], 422);
        });

        // No autenticado → { message }
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if (! $request->is('api/*')) return null;
            return response()->json([
                'message' => 'No autenticado. Inicia sesión para continuar.',
            ], 401);
        });

        // 404 (modelo no encontrado, ruta inexistente)
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if (! $request->is('api/*')) return null;
            return response()->json([
                'message' => 'Recurso no encontrado.',
            ], 404);
        });

        // Resto de errores HTTP (403, 429, etc.)
        $exceptions->render(function (HttpException $e, Request $request) {
            if (! $request->is('api/*')) return null;
            return response()->json([
                'message' => $e->getMessage() ?: 'Error en la solicitud.',
            ], $e->getStatusCode());
        });
    })->create();
