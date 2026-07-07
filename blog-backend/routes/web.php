<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Botón "Publicar" del panel: dispara el rebuild/reexport del sitio estático
// (Astro) en Vercel mediante su deploy hook. Solo para usuarios autenticados.
Route::post('/publish', function () {
    $hook = config('services.prismic.deploy_hook_url');

    if (! $hook) {
        return back()->with('publish_status', 'Falta configurar DEPLOY_HOOK_URL.');
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
