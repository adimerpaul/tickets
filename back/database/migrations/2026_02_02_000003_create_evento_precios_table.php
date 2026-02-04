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

            // si antes tenías segmento string:
            $table->string('segmento', 20)->default('ADULTO');

            // si antes tenías egp/eur/usd/usdt:
            $table->decimal('egp_compra', 12, 2)->default(0);
            $table->decimal('egp_venta',  12, 2)->default(0);
            $table->decimal('eur_compra', 12, 2)->default(0);
            $table->decimal('eur_venta',  12, 2)->default(0);
            $table->decimal('usd_compra', 12, 2)->default(0);
            $table->decimal('usd_venta',  12, 2)->default(0);
            $table->decimal('usdt_compra', 12, 2)->default(0);
            $table->decimal('usdt_venta',  12, 2)->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['evento_id','nacionalidad_id','tipo_entrada_id','segmento'],
                'uniq_evento_precio_combo'
            );
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('evento_precios');
    }
};
