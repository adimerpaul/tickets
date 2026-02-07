<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('evento_monedas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('evento_id')->constrained('eventos')->cascadeOnDelete();
            $table->foreignId('moneda_id')->constrained('monedas')->cascadeOnDelete();

            $table->boolean('activo')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['evento_id','moneda_id'], 'uniq_evento_moneda_combo');
            $table->index(['evento_id','activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evento_monedas');
    }
};
