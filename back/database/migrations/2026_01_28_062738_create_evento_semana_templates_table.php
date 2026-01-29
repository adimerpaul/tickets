<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('evento_semana_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->cascadeOnDelete();

            // 1=Lun ... 7=Dom
            $table->unsignedTinyInteger('dow');

            $table->time('hora_inicio');
            $table->time('hora_fin');

            $table->string('plan')->nullable();          // Adulto / Niño (o null)
            $table->decimal('precio', 8, 2)->default(0);
            $table->integer('capacidad')->default(0);

            $table->boolean('activo')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['evento_id', 'dow']);
            $table->unique(['evento_id', 'dow', 'hora_inicio', 'plan'], 'uniq_template_slot');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evento_semana_templates');
    }
};
