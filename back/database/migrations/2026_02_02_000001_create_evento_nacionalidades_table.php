<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('evento_nacionalidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->cascadeOnDelete();

            $table->string('nombre', 80);     // Ej: Nacional, Extranjero
            $table->string('slug', 120);      // ej: nacional, extranjero
            $table->boolean('activo')->default(true);
            $table->integer('orden')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['evento_id', 'slug'], 'uniq_evento_nacionalidad_slug');
            $table->index(['evento_id', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evento_nacionalidades');
    }
};
