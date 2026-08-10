<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class BlogContentSeeder extends Seeder
{
    /**
     * Datos de ejemplo del blog (categorías, etiquetas y un par de posts es/en).
     * Idempotente: usa updateOrCreate por slug.
     */
    public function run(): void
    {
        // Categorías
        $mamiferosEs = Category::updateOrCreate(['slug' => 'mamiferos'], ['name' => 'Mamíferos', 'lang' => 'es']);
        $mammalsEn   = Category::updateOrCreate(['slug' => 'mammals'], ['name' => 'Mammals', 'lang' => 'en']);

        // Etiquetas
        $consEs = Tag::updateOrCreate(['slug' => 'conservacion'], ['name' => 'Conservación', 'lang' => 'es']);
        $habEs  = Tag::updateOrCreate(['slug' => 'habitat'], ['name' => 'Hábitat', 'lang' => 'es']);
        $consEn = Tag::updateOrCreate(['slug' => 'conservation'], ['name' => 'Conservation', 'lang' => 'en']);
        $habEn  = Tag::updateOrCreate(['slug' => 'habitat-en'], ['name' => 'Habitat', 'lang' => 'en']);

        // Post en español
        $postEs = Post::updateOrCreate(['slug' => 'el-jaguar-rey-de-la-selva'], [
            'category_id'      => $mamiferosEs->id,
            'title'            => 'El jaguar, rey de la selva',
            'translation_key'  => 'jaguar',
            'lang'             => 'es',
            'excerpt'          => 'Conoce al felino más grande de América y por qué es clave para su ecosistema.',
            'content'          => '<p>El jaguar (<em>Panthera onca</em>) es el felino más grande del continente americano...</p>',
            'image_alt'        => 'Jaguar descansando en la selva',
            'meta_title'       => 'El jaguar, rey de la selva | ZooBlog',
            'meta_description' => 'Todo sobre el jaguar: hábitat, alimentación y conservación del gran felino americano.',
            'is_published'     => true,
            'published_at'     => now(),
        ]);
        $postEs->tags()->sync([$consEs->id, $habEs->id]);

        // Post en inglés
        $postEn = Post::updateOrCreate(['slug' => 'the-jaguar-king-of-the-jungle'], [
            'category_id'      => $mammalsEn->id,
            'title'            => 'The jaguar, king of the jungle',
            'translation_key'  => 'jaguar',
            'lang'             => 'en',
            'excerpt'          => 'Meet the largest cat in the Americas and why it is key to its ecosystem.',
            'content'          => '<p>The jaguar (<em>Panthera onca</em>) is the largest cat in the Americas...</p>',
            'image_alt'        => 'Jaguar resting in the jungle',
            'meta_title'       => 'The jaguar, king of the jungle | ZooBlog',
            'meta_description' => 'Everything about the jaguar: habitat, diet and conservation of the great American cat.',
            'is_published'     => true,
            'published_at'     => now(),
        ]);
        $postEn->tags()->sync([$consEn->id, $habEn->id]);
    }
}
