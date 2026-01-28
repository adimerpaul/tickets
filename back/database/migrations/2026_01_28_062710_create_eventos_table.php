<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();

            // Identidad
            $table->string('nombre', 180);
            $table->string('slug', 200)->unique(); // ej: giza-plateau
            $table->text('descripcion')->nullable();

            // Ubicación
            $table->string('pais', 120)->default('Egypt');
            $table->string('ciudad', 120)->nullable();
            $table->string('ubicacion', 255)->nullable(); // dirección/nota
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();

            // Estado y visual
            $table->boolean('activo')->default(true);
            $table->string('imagen', 255)->nullable(); // portada/banner
            $table->string('categoria', 80)->nullable(); // museo/templo/ciudad/...
            $table->integer('orden')->default(0);

            // Reglas generales (nacionalidad / acceso)
            // ALL, EGYPTIAN_ONLY, FOREIGNERS_ONLY
            $table->enum('regla_nacionalidad', ['ALL', 'EGYPTIAN_ONLY', 'FOREIGNERS_ONLY'])->default('ALL');

            // Moneda por defecto
            $table->string('moneda', 10)->default('EGP');
            $table->softDeletes();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};
