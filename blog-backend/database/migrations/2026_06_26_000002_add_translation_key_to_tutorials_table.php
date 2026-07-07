<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tutorials', function (Blueprint $table) {
            // Identificador compartido entre las versiones de idioma de un mismo
            // tutorial. Permite enlazarlas aunque cada una tenga su propio slug.
            $table->string('translation_key')->nullable()->index()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('tutorials', function (Blueprint $table) {
            $table->dropColumn('translation_key');
        });
    }
};
