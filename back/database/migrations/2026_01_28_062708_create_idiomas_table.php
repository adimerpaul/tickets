<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('idiomas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 10)->unique(); // ES, EN, FR, etc.
            $table->string('nombre', 80);
            $table->boolean('activo')->default(true);
            $table->integer('orden')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['activo','orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idiomas');
    }
};
