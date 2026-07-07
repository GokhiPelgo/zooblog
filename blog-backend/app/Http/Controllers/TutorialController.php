<?php

namespace App\Http\Controllers;

use App\Models\Tutorial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TutorialController extends Controller
{
    /**
     * GET /api/tutorials?lang=es
     * Lista los tutoriales publicados (más recientes primero).
     */
    public function index(Request $request): JsonResponse
    {
        $tutorials = Tutorial::query()
            ->where('is_published', true)
            ->when($request->query('lang'), fn ($q, $lang) => $q->where('lang', $lang))
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->get(['id', 'title', 'slug', 'lang', 'excerpt', 'cover_image', 'image_alt', 'level', 'published_at'])
            ->each(fn (Tutorial $t) => $t->cover_image = $this->imageUrl($t->cover_image));

        return response()->json($tutorials);
    }

    /**
     * GET /api/tutorials/{slug}?lang=es
     * Devuelve un tutorial publicado por su slug.
     */
    public function show(string $slug, Request $request): JsonResponse
    {
        $tutorial = Tutorial::query()
            ->where('is_published', true)
            ->where('slug', $slug)
            ->when($request->query('lang'), fn ($q, $lang) => $q->where('lang', $lang))
            ->first();

        if (! $tutorial) {
            return response()->json(['message' => 'Tutorial no encontrado.'], 404);
        }

        // Slug de la versión en el otro idioma (mismo translation_key), si existe.
        $alternateSlug = null;
        if ($tutorial->translation_key) {
            $alternateSlug = Tutorial::query()
                ->where('is_published', true)
                ->where('translation_key', $tutorial->translation_key)
                ->where('lang', '!=', $tutorial->lang)
                ->value('slug');
        }

        $tutorial->cover_image = $this->imageUrl($tutorial->cover_image);
        $tutorial->alternate_slug = $alternateSlug;

        return response()->json($tutorial);
    }

    /**
     * Convierte la ruta guardada de la imagen en una URL pública completa,
     * usando el disco por defecto (s3 en producción, public en local).
     */
    private function imageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return Storage::disk(config('filesystems.tutorials_disk'))->url($path);
    }
}
