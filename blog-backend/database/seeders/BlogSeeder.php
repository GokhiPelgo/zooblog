<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        // Usuario autor por defecto (para acceso al panel/local)
        User::firstOrCreate(
            ['email' => 'chelo@zooblog.com'],
            [
                'name'     => 'Chelo',
                'password' => Hash::make('password123'),
            ]
        );

        // Datos de ejemplo. En producción los artículos llegan desde Prismic;
        // esto solo siembra contenido local de demostración.
        $posts = [
            [
                'key'          => 'smartest-dogs',
                'image'        => '/images/perro.jpg',
                'image_alt_es' => 'Perro mirando hacia el frente',
                'image_alt_en' => 'Dog looking forward',
                'tags'         => 'perros,animales,mascotas',
                'published_at' => '2026-05-13',
                'es' => [
                    'uid'         => 'los-perros-mas-inteligentes-del-mundo',
                    'title'       => 'Los perros más inteligentes del mundo',
                    'description' => 'Conoce las razas de perros con mayor inteligencia.',
                ],
                'en' => [
                    'uid'         => 'the-smartest-dogs-in-the-world',
                    'title'       => 'The Smartest Dogs in the World',
                    'description' => 'Meet the dog breeds with the highest intelligence.',
                ],
            ],
            [
                'key'          => 'giraffes',
                'image'        => '/images/jirafa.jpg',
                'image_alt_es' => 'Jirafa en la sabana',
                'image_alt_en' => 'Giraffe on the savanna',
                'tags'         => 'jirafas,animales,safari',
                'published_at' => '2026-05-10',
                'es' => [
                    'uid'         => 'jirafas-el-animal-mas-alto-del-mundo',
                    'title'       => 'Jirafas: el animal más alto del mundo',
                    'description' => 'Todo sobre las jirafas y su increíble cuello.',
                ],
                'en' => [
                    'uid'         => 'giraffes-the-tallest-animal-in-the-world',
                    'title'       => 'Giraffes: The Tallest Animal in the World',
                    'description' => 'Everything about giraffes and their incredible neck.',
                ],
            ],
            [
                'key'          => 'rhino',
                'image'        => '/images/rinoceronte.jpg',
                'image_alt_es' => 'Rinoceronte en la naturaleza',
                'image_alt_en' => 'Rhino in the wild',
                'tags'         => 'rinocerontes,animales,conservacion',
                'published_at' => '2026-05-07',
                'es' => [
                    'uid'         => 'rinocerontes-gigantes-en-peligro',
                    'title'       => 'Rinocerontes: gigantes en peligro',
                    'description' => 'La situación de los rinocerontes y su conservación.',
                ],
                'en' => [
                    'uid'         => 'rhinos-giants-in-danger',
                    'title'       => 'Rhinos: Giants in Danger',
                    'description' => 'The situation of rhinos and their conservation.',
                ],
            ],
            [
                'key'          => 'roadrunner',
                'image'        => '/images/corre-caminos.jpg',
                'image_alt_es' => 'Corre caminos en el desierto',
                'image_alt_en' => 'Roadrunner in the desert',
                'tags'         => 'aves,animales,desierto',
                'published_at' => '2026-05-04',
                'es' => [
                    'uid'         => 'el-corre-caminos-el-ave-mas-rapida-del-desierto',
                    'title'       => 'El corre caminos: el ave más rápida del desierto',
                    'description' => 'Descubre al veloz corre caminos y sus adaptaciones.',
                ],
                'en' => [
                    'uid'         => 'the-roadrunner-fastest-bird-of-the-desert',
                    'title'       => 'The Roadrunner: Fastest Bird of the Desert',
                    'description' => 'Discover the speedy roadrunner and its adaptations.',
                ],
            ],
        ];

        $locales = ['es' => 'es-mx', 'en' => 'en-us'];

        foreach ($posts as $data) {
            foreach ($locales as $lang => $locale) {
                Article::updateOrCreate(
                    ['prismic_id' => "seed-{$data['key']}-{$lang}"],
                    [
                        'uid'          => $data[$lang]['uid'],
                        'lang'         => $locale,
                        'title'        => $data[$lang]['title'],
                        'description'  => $data[$lang]['description'],
                        'image_url'    => $data['image'],
                        'image_alt'    => $data["image_alt_{$lang}"],
                        'tags'         => $data['tags'],
                        'content'      => "<p>Contenido de ejemplo para {$data[$lang]['title']}.</p>",
                        'published_at' => $data['published_at'],
                    ]
                );
            }
        }
    }
}
