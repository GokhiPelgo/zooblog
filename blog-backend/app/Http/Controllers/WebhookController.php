<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * POST /api/webhook/prismic
     *
     * Prismic llama a este endpoint cada vez que se publica, actualiza o
     * elimina un documento. Como el frontend es un sitio estático que lee
     * Prismic en tiempo de build, lo único que necesitamos hacer aquí es
     * disparar el deploy hook de Vercel/Netlify para reconstruir el sitio.
     */
    public function prismic(Request $request): JsonResponse
    {
        // 1. Validar que el webhook viene de Prismic usando el secret
        $secret = config('services.prismic.webhook_secret');

        if ($secret && ! hash_equals($secret, (string) $request->input('secret'))) {
            Log::warning('Webhook rechazado: secret inválido.');
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $type = $request->input('type', 'unknown');
        Log::info('Webhook de Prismic recibido.', ['type' => $type]);

        // 2. Disparar el rebuild del sitio estático
        $deployHook = config('services.prismic.deploy_hook_url');

        if (! $deployHook) {
            Log::info('Webhook recibido pero DEPLOY_HOOK_URL no está configurado; no se dispara rebuild.');
            return response()->json([
                'message' => 'Webhook recibido. No hay deploy hook configurado.',
            ], 200);
        }

        try {
            $response = Http::timeout(10)->post($deployHook);

            if ($response->failed()) {
                Log::error('Deploy hook respondió con error.', ['status' => $response->status()]);
                return response()->json(['message' => 'El deploy hook falló.'], 502);
            }

            Log::info('Deploy hook disparado correctamente.');
            return response()->json(['message' => 'Rebuild disparado.'], 200);
        } catch (\Throwable $e) {
            Log::error('Error al disparar el deploy hook: ' . $e->getMessage());
            return response()->json(['message' => 'Error al disparar el rebuild.'], 500);
        }
    }
}
