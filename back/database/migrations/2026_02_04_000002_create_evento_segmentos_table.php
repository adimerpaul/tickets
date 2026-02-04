<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('evento_segmentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->cascadeOnDelete();

            $table->string('nombre', 80);   // General, Adulto, Estudiante, Niño
            $table->string('slug', 120);    // general, adulto, estudiante, nino
            $table->boolean('activo')->default(true);
            $table->integer('orden')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['evento_id','slug'], 'uniq_evento_segmento_slug');
            $table->index(['evento_id','orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evento_segmentos');
    }
};
