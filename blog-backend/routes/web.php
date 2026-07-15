<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/', function () {
    return view('welcome');
});

// Botón "Publicar" del panel. Dos modos (config/services.php → astro.publish_mode):
//   · 'local' → Laravel compila Astro aquí mismo (npm run build) y reporta errores.
//   · 'hook'  → dispara el deploy hook de Vercel (producción).
// Solo para usuarios autenticados.
Route::post('/publish', function () {
    // ---- Modo LOCAL: compila Astro en esta máquina y reporta el resultado ----
    if (config('services.astro.publish_mode') === 'local') {
        $frontendPath = config('services.astro.frontend_path');
        $command      = config('services.astro.build_command', 'npm run build');

        if (! $frontendPath || ! is_dir($frontendPath)) {
            return back()->with('publish_status', '✗ No se pudo publicar — no encuentro el frontend en: '.$frontendPath);
        }

        try {
            // El build puede tardar; damos hasta 10 minutos.
            $result = Process::path($frontendPath)->timeout(600)->run($command);

            if ($result->successful()) {
                return back()->with('publish_status', '✓ Publicación exitosa: el sitio se compiló sin errores.');
            }

            // Dejamos registro completo del error en el log para revisarlo con calma...
            $error = trim($result->errorOutput() ?: $result->output());
            \Illuminate\Support\Facades\Log::error('Fallo al publicar (build de Astro): '.$error);

            // ...y mostramos un resumen claro junto al botón.
            return back()->with('publish_status', '✗ No se pudo publicar: el build falló. '.Str::limit($error, 500));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Fallo al publicar (excepción): '.$e->getMessage());

            return back()->with('publish_status', '✗ No se pudo publicar: '.$e->getMessage());
        }
    }

    // ---- Modo PRODUCCIÓN: dispara el deploy hook de Vercel ----
    $hook = config('services.prismic.deploy_hook_url');

    if (! $hook) {
        return back()->with('publish_status', 'Falta configurar PUBLISH_MODE=local (build en tu máquina) o DEPLOY_HOOK_URL (producción).');
    }

    try {
        $response = Http::timeout(15)->post($hook);

        return back()->with('publish_status', $response->successful()
            ? '✓ Publicación iniciada: el sitio se está reconstruyendo.'
            : 'Error al publicar (código '.$response->status().').');
    } catch (\Throwable $e) {
        return back()->with('publish_status', 'Error al publicar: '.$e->getMessage());
    }
})->middleware('auth')->name('publish');
