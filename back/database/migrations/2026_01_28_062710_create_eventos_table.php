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
            $table->string('slug', 200)->unique();
            $table->text('descripcion')->nullable();

            // Ubicación
            $table->string('pais', 120)->default('Egypt');
            $table->string('ciudad', 120)->nullable();
            $table->string('ubicacion', 255)->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();

            // Estado y visual
            $table->boolean('activo')->default(true);
            $table->string('imagen', 255)->nullable();
            $table->string('categoria', 80)->nullable();
            $table->integer('orden')->default(0);

            // Reglas generales
            $table->string('regla_nacionalidad')->default('ALL');
            $table->string('moneda', 10)->default('EGP');

            // ✅ NUEVO: configuración de grilla semanal
            $table->unsignedSmallInteger('slot_interval_min')->default(30); // 30 min
            $table->time('semana_hora_inicio')->default('09:00:00');
            $table->time('semana_hora_fin')->default('17:00:00');
            $table->unsignedSmallInteger('generar_semanas')->default(52); // cuántas semanas hacia adelante generar

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};
