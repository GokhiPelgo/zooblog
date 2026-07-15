<?php

namespace Database\Seeders;

use App\Models\HomeContent;
use Illuminate\Database\Seeder;

class HomeContentSeeder extends Seeder
{
    /**
     * Carga el contenido inicial del Home (un registro con ambos idiomas).
     * Idempotente: siempre actualiza/crea la fila id=1.
     * Imágenes vacías = se usan las de por defecto del sitio.
     */
    public function run(): void
    {
        HomeContent::updateOrCreate(['id' => 1], [
            'badge_es'    => 'Todo sobre el reino animal',
            'badge_en'    => 'All about the animal kingdom',
            'title_es'    => 'ZOOBLOGÍA DE LOS ANIMALES',
            'title_en'    => 'ANIMAL ZOOLOGY',
            'subtitle_es' => 'Explora datos, especies, hábitats y curiosidades sobre los animales que habitan nuestro planeta.',
            'subtitle_en' => 'Explore facts, species, habitats and curiosities about the animals that inhabit our planet.',

            'primary_label_es'   => 'Ver blog',
            'primary_label_en'   => 'View blog',
            'primary_url_es'     => '/es/blog',
            'primary_url_en'     => '/en/blog',
            'secondary_label_es' => 'Explorar especies',
            'secondary_label_en' => 'Explore species',
            'secondary_url_es'   => '/es/blog',
            'secondary_url_en'   => '/en/blog',

            // Imágenes vacías = imagen por defecto. Súbelas en el panel para cambiarlas.
            'image1' => null,
            'image2' => null,
            'image3' => null,
            'image4' => null,

            'image1_alt_es' => 'Guacamaya en su hábitat natural',
            'image1_alt_en' => 'Macaw in its natural habitat',
            'image2_alt_es' => 'Jaguar en la selva',
            'image2_alt_en' => 'Jaguar in the jungle',
            'image3_alt_es' => 'Tlacuache en su hábitat',
            'image3_alt_en' => 'Opossum in its habitat',
            'image4_alt_es' => 'Leopardo de las nieves',
            'image4_alt_en' => 'Snow leopard',
        ]);
    }
}
