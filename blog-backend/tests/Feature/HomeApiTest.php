<?php

namespace Tests\Feature;

use App\Models\HomeContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeApiTest extends TestCase
{
    use RefreshDatabase;

    private function makeHome(): HomeContent
    {
        return HomeContent::create([
            'badge_es' => 'Etiqueta ES',
            'badge_en' => 'Badge EN',
            'title_es' => 'Título en español',
            'title_en' => 'Title in english',
            'subtitle_es' => 'Subtítulo ES',
            'subtitle_en' => 'Subtitle EN',
            'primary_label_es' => 'Ver blog',
            'primary_label_en' => 'View blog',
            'image1_alt_es' => 'Alt ES',
            'image1_alt_en' => 'Alt EN',
        ]);
    }

    public function test_devuelve_el_contenido_en_espanol(): void
    {
        $this->makeHome();

        $response = $this->getJson('/api/home/es');

        $response->assertOk();
        $response->assertJsonFragment([
            'badge'      => 'Etiqueta ES',
            'title'      => 'Título en español',
            'image1_alt' => 'Alt ES',
        ]);
    }

    public function test_devuelve_el_contenido_en_ingles(): void
    {
        $this->makeHome();

        $response = $this->getJson('/api/home/en');

        $response->assertOk();
        $response->assertJsonFragment([
            'badge' => 'Badge EN',
            'title' => 'Title in english',
        ]);
    }

    public function test_devuelve_404_si_no_hay_contenido(): void
    {
        // Tabla vacía (RefreshDatabase no siembra datos).
        $this->getJson('/api/home/es')->assertNotFound();
    }
}
