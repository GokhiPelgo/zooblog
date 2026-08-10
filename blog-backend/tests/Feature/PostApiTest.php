<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostApiTest extends TestCase
{
    use RefreshDatabase;

    private function makePost(array $overrides = []): Post
    {
        return Post::create(array_merge([
            'title'        => 'Artículo de prueba',
            'slug'         => 'articulo-de-prueba',
            'lang'         => 'es',
            'excerpt'      => 'Resumen',
            'content'      => '<p>Contenido</p>',
            'is_published' => true,
            'published_at' => now(),
        ], $overrides));
    }

    public function test_lista_solo_posts_publicados(): void
    {
        $this->makePost(['slug' => 'publicado', 'is_published' => true]);
        $this->makePost(['slug' => 'borrador', 'is_published' => false]);

        $response = $this->getJson('/api/posts');

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['slug' => 'publicado']);
        $response->assertJsonMissing(['slug' => 'borrador']);
    }

    public function test_filtra_por_idioma(): void
    {
        $this->makePost(['slug' => 'es-uno', 'lang' => 'es']);
        $this->makePost(['slug' => 'en-uno', 'lang' => 'en']);

        $this->getJson('/api/posts?lang=en')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['slug' => 'en-uno']);
    }

    public function test_filtra_por_categoria(): void
    {
        $cat = Category::create(['name' => 'Mamíferos', 'slug' => 'mamiferos', 'lang' => 'es']);
        $this->makePost(['slug' => 'con-categoria', 'category_id' => $cat->id]);
        $this->makePost(['slug' => 'sin-categoria']);

        $this->getJson('/api/posts?category=mamiferos')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['slug' => 'con-categoria']);
    }

    public function test_muestra_un_post_con_categoria_y_etiquetas(): void
    {
        $cat = Category::create(['name' => 'Aves', 'slug' => 'aves', 'lang' => 'es']);
        $tag = Tag::create(['name' => 'Hábitat', 'slug' => 'habitat', 'lang' => 'es']);
        $post = $this->makePost(['slug' => 'guacamaya', 'title' => 'La guacamaya', 'category_id' => $cat->id]);
        $post->tags()->sync([$tag->id]);

        $response = $this->getJson('/api/posts/guacamaya');

        $response->assertOk();
        $response->assertJsonFragment(['title' => 'La guacamaya']);
        $response->assertJsonPath('category.slug', 'aves');
        $response->assertJsonFragment(['slug' => 'habitat']); // etiqueta
    }

    public function test_devuelve_404_si_el_post_no_esta_publicado(): void
    {
        $this->makePost(['slug' => 'oculto', 'is_published' => false]);

        $this->getJson('/api/posts/oculto')->assertNotFound();
        $this->getJson('/api/posts/no-existe')->assertNotFound();
    }
}
