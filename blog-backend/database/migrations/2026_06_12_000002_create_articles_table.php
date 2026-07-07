<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('prismic_id')->unique();   // ID del documento en Prismic
            $table->string('uid');                     // Slug del artículo (ej: perros-inteligentes)
            $table->string('lang', 10);                // Locale: es-mx o en-us
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->string('image_alt')->nullable();
            $table->string('tags')->nullable();        // Comma-separated
            $table->longText('content')->nullable();   // HTML renderizado
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['uid', 'lang']);           // Un uid por idioma
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
