<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('evento_precios', function (Blueprint $table) {
            $table->id();

            $table->foreignId('evento_id')->constrained('eventos')->cascadeOnDelete();
            $table->foreignId('nacionalidad_id')->constrained('evento_nacionalidades')->cascadeOnDelete();
            $table->foreignId('tipo_entrada_id')->constrained('evento_tipos_entrada')->cascadeOnDelete();

            $table->foreignId('segmento_id')->constrained('evento_segmentos')->cascadeOnDelete();
            $table->foreignId('moneda_id')->constrained('monedas')->cascadeOnDelete();

            $table->decimal('compra', 12, 2)->default(0);
            $table->decimal('venta',  12, 2)->default(0);
            $table->boolean('activo')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['evento_id','nacionalidad_id','tipo_entrada_id','segmento_id','moneda_id'],
                'uniq_evento_precio_combo_v2'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evento_precios');
    }
};
