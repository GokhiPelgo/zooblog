<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Contenido editable de la portada (Home).
     * Un solo registro con columnas por idioma (_es / _en).
     * Las 4 imágenes son compartidas; el texto alternativo (alt) es por idioma.
     */
    public function up(): void
    {
        // dropIfExists por si existía una versión anterior de la tabla.
        Schema::dropIfExists('home_contents');

        Schema::create('home_contents', function (Blueprint $table) {
            $table->id();

            // Textos por idioma
            $table->string('badge_es')->nullable();
            $table->string('badge_en')->nullable();
            $table->string('title_es')->nullable();
            $table->string('title_en')->nullable();
            $table->text('subtitle_es')->nullable();
            $table->text('subtitle_en')->nullable();

            // Botones por idioma
            $table->string('primary_label_es')->nullable();
            $table->string('primary_label_en')->nullable();
            $table->string('primary_url_es')->nullable();
            $table->string('primary_url_en')->nullable();
            $table->string('secondary_label_es')->nullable();
            $table->string('secondary_label_en')->nullable();
            $table->string('secondary_url_es')->nullable();
            $table->string('secondary_url_en')->nullable();

            // Imágenes (compartidas entre idiomas)
            $table->string('image1')->nullable();
            $table->string('image2')->nullable();
            $table->string('image3')->nullable();
            $table->string('image4')->nullable();

            // Texto alternativo (alt) por idioma
            $table->string('image1_alt_es')->nullable();
            $table->string('image1_alt_en')->nullable();
            $table->string('image2_alt_es')->nullable();
            $table->string('image2_alt_en')->nullable();
            $table->string('image3_alt_es')->nullable();
            $table->string('image3_alt_en')->nullable();
            $table->string('image4_alt_es')->nullable();
            $table->string('image4_alt_en')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_contents');
    }
};
