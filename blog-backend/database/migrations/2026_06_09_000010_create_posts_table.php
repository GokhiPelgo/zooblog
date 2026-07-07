<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tags normalizados (sin duplicar en cada post)
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 80)->unique();
            $table->timestamps();
        });

        // Posts: solo datos invariantes al idioma
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 160)->unique();          // URL-friendly global
            $table->string('translation_key', 160)->unique(); // vincula versiones en distintos idiomas
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->string('image', 500)->nullable();
            $table->string('image_alt', 300)->nullable();
            $table->boolean('draft')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        // Traducciones por idioma (título, descripción, contenido)
        Schema::create('post_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->char('lang', 2);                        // 'es' | 'en'
            $table->string('title', 255);
            $table->string('description', 500);
            $table->longText('content');
            $table->unique(['post_id', 'lang']);
            $table->timestamps();
        });

        // Pivot posts ↔ tags
        Schema::create('post_tag', function (Blueprint $table) {
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['post_id', 'tag_id']);
        });

        // Comentarios
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('content');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
        Schema::dropIfExists('post_tag');
        Schema::dropIfExists('post_translations');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('tags');
    }
};
