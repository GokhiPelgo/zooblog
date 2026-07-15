<?php

namespace App\Http\Controllers;

use App\Models\HomeContent;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    /**
     * GET /api/home/{lang}
     * Devuelve el contenido de la portada para el idioma pedido.
     * Internamente hay un solo registro con columnas _es/_en; aquí lo
     * "aplanamos" al idioma correspondiente para que el frontend no cambie.
     */
    public function show(string $lang): JsonResponse
    {
        $lang = in_array($lang, ['es', 'en'], true) ? $lang : 'es';

        $home = HomeContent::first();

        if (! $home) {
            return response()->json(['message' => 'Contenido de inicio no encontrado.'], 404);
        }

        $data = [
            'badge'           => $home->{"badge_$lang"},
            'title'           => $home->{"title_$lang"},
            'subtitle'        => $home->{"subtitle_$lang"},
            'primary_label'   => $home->{"primary_label_$lang"},
            'primary_url'     => $home->{"primary_url_$lang"},
            'secondary_label' => $home->{"secondary_label_$lang"},
            'secondary_url'   => $home->{"secondary_url_$lang"},
            'image1'          => $this->imageUrl($home->image1),
            'image2'          => $this->imageUrl($home->image2),
            'image3'          => $this->imageUrl($home->image3),
            'image4'          => $this->imageUrl($home->image4),
            'image1_alt'      => $home->{"image1_alt_$lang"},
            'image2_alt'      => $home->{"image2_alt_$lang"},
            'image3_alt'      => $home->{"image3_alt_$lang"},
            'image4_alt'      => $home->{"image4_alt_$lang"},
        ];

        return response()->json($data);
    }

    /**
     * Devuelve la URL de la imagen.
     *  · Si ya es una URL (http) o una ruta del frontend (/images/...), la deja igual.
     *  · Si es un archivo subido en el panel, arma la URL del disco de subidas.
     */
    private function imageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }
        if (str_starts_with($path, 'http') || str_starts_with($path, '/')) {
            return $path;
        }

        return Storage::disk(config('filesystems.tutorials_disk'))->url($path);
    }
}
