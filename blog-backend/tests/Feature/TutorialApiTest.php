<?php

namespace Tests\Feature;

use App\Models\Tutorial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TutorialApiTest extends TestCase
{
    use RefreshDatabase;

    private function makeTutorial(array $overrides = []): Tutorial
    {
        return Tutorial::create(array_merge([
            'title'        => 'Tutorial de prueba',
            'slug'         => 'tutorial-de-prueba',
            'lang'         => 'es',
            'excerpt'      => 'Resumen',
            'content'      => 'Contenido',
            'is_published' => true,
            'published_at' => now(),
        ], $overrides));
    }

    public function test_lista_solo_devuelve_tutoriales_publicados(): void
    {
        $this->makeTutorial(['slug' => 'publicado', 'is_published' => true]);
        $this->makeTutorial(['slug' => 'borrador', 'is_published' => false]);

        $response = $this->getJson('/api/tutorials');

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['slug' => 'publicado']);
        $response->assertJsonMissing(['slug' => 'borrador']);
    }

    public function test_lista_filtra_por_idioma(): void
    {
        $this->makeTutorial(['slug' => 'es-uno', 'lang' => 'es']);
        $this->makeTutorial(['slug' => 'en-uno', 'lang' => 'en']);

        $response = $this->getJson('/api/tutorials?lang=en');

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['slug' => 'en-uno']);
    }

    public function test_muestra_un_tutorial_publicado_por_slug(): void
    {
        $this->makeTutorial(['slug' => 'como-cuidar-un-perro', 'title' => 'Cómo cuidar un perro']);

        $response = $this->getJson('/api/tutorials/como-cuidar-un-perro');

        $response->assertOk();
        $response->assertJsonFragment(['title' => 'Cómo cuidar un perro']);
    }

    public function test_devuelve_404_si_el_tutorial_no_esta_publicado(): void
    {
        $this->makeTutorial(['slug' => 'oculto', 'is_published' => false]);

        $this->getJson('/api/tutorials/oculto')->assertNotFound();
        $this->getJson('/api/tutorials/no-existe')->assertNotFound();
    }
}
