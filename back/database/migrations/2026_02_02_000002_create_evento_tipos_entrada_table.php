<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('evento_tipos_entrada', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->cascadeOnDelete();

            $table->string('nombre', 120);    // Ej: Acceso al recinto básico
            $table->string('slug', 160);      // ej: acceso-basico
            $table->boolean('activo')->default(true);
            $table->integer('orden')->default(0);

            $table->string('imagen', 255)->nullable(); // opcional (mini preview como tu screenshot)

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['evento_id', 'slug'], 'uniq_evento_tipoentrada_slug');
            $table->index(['evento_id', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evento_tipos_entrada');
    }
};
