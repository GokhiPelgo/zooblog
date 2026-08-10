<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bitácora de publicaciones/builds: quién publicó, cuándo y con qué resultado.
     */
    public function up(): void
    {
        Schema::create('build_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // quién lo disparó
            $table->string('user_name')->nullable();           // nombre (por si se borra el usuario)
            $table->string('mode')->default('local');          // local | hook
            $table->string('status')->default('running');      // running | success | error
            $table->text('message')->nullable();               // detalle / error
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('build_logs');
    }
};
