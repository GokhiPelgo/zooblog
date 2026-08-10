<?php

namespace App\Jobs;

use App\Models\BuildLog;
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
 * lo consulte sin quedarse congelado esperando el build, y cierra la bitácora
 * (BuildLog) con el resultado.
 */
class PublishSiteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** El build puede tardar; damos hasta 10 minutos. */
    public int $timeout = 600;

    /** Clave donde se guarda el estado del build. */
    public const STATUS_KEY = 'publish.status';

    public function __construct(public ?int $buildLogId = null)
    {
    }

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
            $msg = 'No encuentro el frontend en: '.$frontendPath;
            self::setStatus('error', '✗ No se pudo publicar — '.$msg);
            $this->finishLog('error', $msg);
            return;
        }

        try {
            $result = Process::path($frontendPath)->timeout(590)->run($command);

            if ($result->successful()) {
                self::setStatus('done', '✓ Publicación exitosa: el sitio se compiló sin errores.');
                $this->finishLog('success', 'Compilado sin errores.');
                return;
            }

            $error = trim($result->errorOutput() ?: $result->output());
            Log::error('Fallo al publicar (build de Astro): '.$error);
            self::setStatus('error', '✗ No se pudo publicar: el build falló. '.Str::limit($error, 400));
            $this->finishLog('error', $error);
        } catch (\Throwable $e) {
            Log::error('Fallo al publicar (excepción): '.$e->getMessage());
            self::setStatus('error', '✗ No se pudo publicar: '.$e->getMessage());
            $this->finishLog('error', $e->getMessage());
        }
    }

    /** Cierra la bitácora del build con su resultado. */
    protected function finishLog(string $status, string $message): void
    {
        if (! $this->buildLogId) {
            return;
        }

        BuildLog::where('id', $this->buildLogId)->update([
            'status'      => $status,
            'message'     => Str::limit($message, 1000),
            'finished_at' => now(),
        ]);
    }
}
