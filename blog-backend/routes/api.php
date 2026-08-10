<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TutorialController;
use Illuminate\Support\Facades\Route;

// Contacto: 5 mensajes/hora por IP
Route::middleware('throttle:contact')->group(function () {
    Route::post('/contact', [ContactController::class, 'store']);
});

// Panel de mensajes recibidos (solo admin, requiere header X-Admin-Token)
Route::middleware('throttle:public-read')->group(function () {
    Route::get('/contact-messages', [ContactController::class, 'index']);
});

// Tutoriales (lectura pública) — administrados desde Filament
Route::middleware('throttle:public-read')->group(function () {
    Route::get('/tutorials', [TutorialController::class, 'index']);
    Route::get('/tutorials/{slug}', [TutorialController::class, 'show']);
});

// Contenido de la portada (Home) por idioma — administrado desde Filament
Route::middleware('throttle:public-read')->group(function () {
    Route::get('/home/{lang}', [HomeController::class, 'show']);
});

// Blog (lectura pública) — administrado desde Filament (reemplaza a Prismic)
Route::middleware('throttle:public-read')->group(function () {
    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/categories', [PostController::class, 'categories']);
    Route::get('/posts/{slug}', [PostController::class, 'show']);
});
