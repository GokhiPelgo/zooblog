<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * GET /api/posts?lang=es&category=slug&tag=slug
     * Lista los artículos publicados (más recientes primero).
     */
    public function index(Request $request): JsonResponse
    {
        $posts = Post::query()
            ->where('is_published', true)
            ->with(['category:id,name,slug', 'tags:id,name,slug'])
            ->when($request->query('lang'), fn ($q, $lang) => $q->where('lang', $lang))
            ->when($request->query('category'), function ($q, $slug) {
                $q->whereHas('category', fn ($c) => $c->where('slug', $slug));
            })
            ->when($request->query('tag'), function ($q, $slug) {
                $q->whereHas('tags', fn ($t) => $t->where('slug', $slug));
            })
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->get([
                'id', 'category_id', 'title', 'slug', 'lang', 'excerpt',
                'cover_image', 'image_alt', 'published_at',
            ])
            ->each(fn (Post $p) => $p->cover_image = $this->imageUrl($p->cover_image));

        return response()->json($posts);
    }

    /**
     * GET /api/posts/{slug}?lang=es
     * Devuelve un artículo publicado por su slug.
     */
    public function show(string $slug, Request $request): JsonResponse
    {
        $post = Post::query()
            ->where('is_published', true)
            ->where('slug', $slug)
            ->with(['category:id,name,slug', 'tags:id,name,slug'])
            ->when($request->query('lang'), fn ($q, $lang) => $q->where('lang', $lang))
            ->first();

        if (! $post) {
            return response()->json(['message' => 'Artículo no encontrado.'], 404);
        }

        // Slug de la versión en el otro idioma (mismo translation_key), si existe.
        $alternateSlug = null;
        if ($post->translation_key) {
            $alternateSlug = Post::query()
                ->where('is_published', true)
                ->where('translation_key', $post->translation_key)
                ->where('lang', '!=', $post->lang)
                ->value('slug');
        }

        $post->cover_image = $this->imageUrl($post->cover_image);
        $post->alternate_slug = $alternateSlug;
        // SEO con respaldo al título/extracto si están vacíos.
        $post->meta_title = $post->meta_title ?: $post->title;
        $post->meta_description = $post->meta_description ?: $post->excerpt;

        return response()->json($post);
    }

    /**
     * GET /api/categories?lang=es
     * Lista las categorías (con su número de artículos publicados).
     */
    public function categories(Request $request): JsonResponse
    {
        $categories = Category::query()
            ->when($request->query('lang'), fn ($q, $lang) => $q->where('lang', $lang))
            ->withCount(['posts' => fn ($q) => $q->where('is_published', true)])
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'lang']);

        return response()->json($categories);
    }

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
