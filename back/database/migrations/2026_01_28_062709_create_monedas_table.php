<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('monedas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 10)->unique();     // EGP, EUR, USD, USDT
            $table->string('nombre', 80);              // Egipto Libra, Euro, Dólar...
            $table->string('simbolo', 10)->nullable(); // £, €, $, etc.
            $table->boolean('activo')->default(true);
            $table->integer('orden')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['activo','orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monedas');
    }
};
