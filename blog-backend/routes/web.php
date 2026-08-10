<?php

use App\Jobs\PublishSiteJob;
use App\Models\BuildLog;
use Illuminate\Support\Facades\Auth;
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
    // Bitácora: registramos quién dispara la publicación y cuándo.
    $log = BuildLog::create([
        'user_id'    => Auth::id(),
        'user_name'  => Auth::user()?->name,
        'mode'       => config('services.astro.publish_mode') === 'local' ? 'local' : 'hook',
        'status'     => 'running',
        'started_at' => now(),
    ]);

    // ---- Modo LOCAL: build en SEGUNDO PLANO (no congela la página) ----
    if (config('services.astro.publish_mode') === 'local') {
        // Marca "en proceso" de inmediato y encola el trabajo (el Job cerrará la bitácora).
        PublishSiteJob::setStatus('building', 'Compilando el sitio…');
        PublishSiteJob::dispatch($log->id);

        return response()->json(['state' => 'building', 'message' => 'Compilando el sitio…']);
    }

    // ---- Modo PRODUCCIÓN: dispara el deploy hook de Vercel (rápido) ----
    $hook = config('services.prismic.deploy_hook_url');

    if (! $hook) {
        $log->update(['status' => 'error', 'message' => 'Falta configurar el deploy hook.', 'finished_at' => now()]);

        return response()->json([
            'state'   => 'error',
            'message' => 'Falta configurar PUBLISH_MODE=local (build en tu máquina) o DEPLOY_HOOK_URL (producción).',
        ]);
    }

    try {
        $response = Http::timeout(15)->post($hook);
        $ok = $response->successful();
        $log->update([
            'status'      => $ok ? 'success' : 'error',
            'message'     => $ok ? 'Deploy hook disparado.' : 'Código '.$response->status().'.',
            'finished_at' => now(),
        ]);

        return response()->json($ok
            ? ['state' => 'done',  'message' => '✓ Publicación iniciada: el sitio se está reconstruyendo.']
            : ['state' => 'error', 'message' => 'Error al publicar (código '.$response->status().').']);
    } catch (\Throwable $e) {
        $log->update(['status' => 'error', 'message' => $e->getMessage(), 'finished_at' => now()]);

        return response()->json(['state' => 'error', 'message' => 'Error al publicar: '.$e->getMessage()]);
    }
})->middleware('auth')->name('publish');

// Estado del build (lo consulta el botón cada pocos segundos).
Route::get('/publish/status', function () {
    return response()->json(
        Cache::get(PublishSiteJob::STATUS_KEY, ['state' => 'idle', 'message' => ''])
    );
})->middleware('auth')->name('publish.status');
