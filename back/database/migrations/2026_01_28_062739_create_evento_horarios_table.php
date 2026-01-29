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

            // referencia a la plantilla (opcional pero útil)
            $table->unsignedBigInteger('template_id')->nullable();

            // Slot por fecha real (lo que se vende/reserva)
            $table->date('fecha')->nullable();
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();

            $table->string('plan')->nullable(); // Adulto / Niño
            $table->decimal('precio', 8, 2)->default(0);

            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();

            $table->integer('capacidad')->default(0);
            $table->integer('reservados')->default(0);

            $table->boolean('activo')->default(true);
            $table->string('nota', 255)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['evento_id', 'starts_at']);
            $table->index(['evento_id', 'fecha']);
            $table->unique(['evento_id', 'starts_at', 'plan'], 'uniq_evento_slot_datetime');

            $table->foreign('template_id')->references('id')->on('evento_semana_templates')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evento_horarios');
    }
};
