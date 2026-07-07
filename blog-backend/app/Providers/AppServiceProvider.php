<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // 10 intentos por minuto por IP (login + registro)
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip())
                ->response(fn () => apiError('Demasiados intentos. Espera un minuto.', 429));
        });

        // 5 mensajes por hora por IP
        RateLimiter::for('contact', function (Request $request) {
            return Limit::perHour(5)->by($request->ip())
                ->response(fn () => apiError('Límite de mensajes alcanzado. Intenta más tarde.', 429));
        });

        // 60 req/min para lectura pública
        RateLimiter::for('public-read', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });
    }
}
