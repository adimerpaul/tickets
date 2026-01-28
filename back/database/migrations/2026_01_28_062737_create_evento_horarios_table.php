<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('evento_horarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->cascadeOnDelete();

            // Slot / horario
            $table->date('fecha')->nullable(); // si es por día específico
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
//            modalidad
            $table->string('plan')->nullable();
            $table->decimal('precio', 8, 2)->default(0);

            // si quieres usar rango datetime:
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();

            $table->integer('capacidad')->default(0);  // cupos
            $table->integer('reservados')->default(0); // control

            $table->boolean('activo')->default(true);
            $table->string('nota', 255)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['evento_id', 'fecha']);
            $table->index(['evento_id', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evento_horarios');
    }
};
