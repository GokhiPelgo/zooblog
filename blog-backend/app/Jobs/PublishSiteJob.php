<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

/**
 * Compila el sitio (astro build) EN SEGUNDO PLANO.
 * Guarda el avance en Cache bajo la clave 'publish.status' para que el panel
 * lo consulte sin quedarse congelado esperando el build.
 */
class PublishSiteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** El build puede tardar; damos hasta 10 minutos. */
    public int $timeout = 600;

    /** Clave donde se guarda el estado del build. */
    public const STATUS_KEY = 'publish.status';

    public static function setStatus(string $state, string $message): void
    {
        Cache::put(self::STATUS_KEY, [
            'state'   => $state,   // building | done | error
            'message' => $message,
            'at'      => now()->toDateTimeString(),
        ], now()->addMinutes(30));
    }

    public function handle(): void
    {
        $frontendPath = config('services.astro.frontend_path');
        $command      = config('services.astro.build_command', 'npm run build');

        self::setStatus('building', 'Compilando el sitio…');

        if (! $frontendPath || ! is_dir($frontendPath)) {
            self::setStatus('error', '✗ No se pudo publicar — no encuentro el frontend en: '.$frontendPath);
            return;
        }

        try {
            $result = Process::path($frontendPath)->timeout(590)->run($command);

            if ($result->successful()) {
                self::setStatus('done', '✓ Publicación exitosa: el sitio se compiló sin errores.');
                return;
            }

            $error = trim($result->errorOutput() ?: $result->output());
            Log::error('Fallo al publicar (build de Astro): '.$error);
            self::setStatus('error', '✗ No se pudo publicar: el build falló. '.Str::limit($error, 400));
        } catch (\Throwable $e) {
            Log::error('Fallo al publicar (excepción): '.$e->getMessage());
            self::setStatus('error', '✗ No se pudo publicar: '.$e->getMessage());
        }
    }
}
