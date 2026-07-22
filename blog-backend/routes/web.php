<?php

use App\Jobs\PublishSiteJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Ruta 'login' con nombre: el middleware 'auth' redirige aquí a los invitados
// que intentan entrar a una ruta protegida. Los mandamos al login del panel.
Route::get('/login', fn () => redirect('/admin/login'))->name('login');

// Botón "Publicar" del panel. Dos modos (config/services.php → astro.publish_mode):
//   · 'local' → encola el build de Astro (segundo plano) y responde al instante.
//   · 'hook'  → dispara el deploy hook de Vercel (producción, síncrono y rápido).
// Devuelve JSON: { state: building|done|error, message }.
Route::post('/publish', function () {
    // ---- Modo LOCAL: build en SEGUNDO PLANO (no congela la página) ----
    if (config('services.astro.publish_mode') === 'local') {
        // Marca "en proceso" de inmediato y encola el trabajo.
        PublishSiteJob::setStatus('building', 'Compilando el sitio…');
        PublishSiteJob::dispatch();

        return response()->json(['state' => 'building', 'message' => 'Compilando el sitio…']);
    }

    // ---- Modo PRODUCCIÓN: dispara el deploy hook de Vercel (rápido) ----
    $hook = config('services.prismic.deploy_hook_url');

    if (! $hook) {
        return response()->json([
            'state'   => 'error',
            'message' => 'Falta configurar PUBLISH_MODE=local (build en tu máquina) o DEPLOY_HOOK_URL (producción).',
        ]);
    }

    try {
        $response = Http::timeout(15)->post($hook);

        return response()->json($response->successful()
            ? ['state' => 'done',  'message' => '✓ Publicación iniciada: el sitio se está reconstruyendo.']
            : ['state' => 'error', 'message' => 'Error al publicar (código '.$response->status().').']);
    } catch (\Throwable $e) {
        return response()->json(['state' => 'error', 'message' => 'Error al publicar: '.$e->getMessage()]);
    }
})->middleware('auth')->name('publish');

// Estado del build (lo consulta el botón cada pocos segundos).
Route::get('/publish/status', function () {
    return response()->json(
        Cache::get(PublishSiteJob::STATUS_KEY, ['state' => 'idle', 'message' => ''])
    );
})->middleware('auth')->name('publish.status');
