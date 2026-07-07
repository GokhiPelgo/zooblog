<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tutorials', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->string('lang', 5)->default('es');      // es / en
            $table->text('excerpt')->nullable();            // resumen corto
            $table->longText('content')->nullable();        // cuerpo del tutorial (HTML / Markdown)
            $table->string('cover_image')->nullable();      // URL de la imagen de portada
            $table->string('level')->nullable();            // principiante / intermedio / avanzado
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['slug', 'lang']);               // un slug por idioma
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tutorials');
    }
};
