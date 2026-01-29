<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->string('codigo_pedido')->nullable();

            // Stripe
            $table->string('session_id')->unique();
            $table->string('payment_intent_id')->nullable();

            // Cliente
            $table->string('email')->nullable();
            $table->string('localizador')->nullable();
            $table->integer('orden')->default(0);

            $table->decimal('amount_total', 10, 2)->default(0);
            $table->string('currency', 10)->default('eur');

            $table->string('status')->default('PENDING');
            $table->timestamp('paid_at')->nullable();

            $table->json('metadata')->nullable();
            $table->json('items')->nullable();

            $table->string('dni')->nullable();
            $table->string('nombre_completo')->nullable();
            $table->string('nacionalidad')->nullable();
            $table->string('entrada_tipo')->nullable();

            // slot elegido
            $table->dateTime('starts_at')->nullable();

            // si manejas 2 slots (adulto/niño), mantenemos:
            $table->unsignedBigInteger('horario_adulto_id')->nullable();
            $table->unsignedBigInteger('horario_nino_id')->nullable();
            $table->unsignedInteger('adults')->default(0);
            $table->unsignedInteger('kids')->default(0);

            $table->foreignId('evento_id')->constrained('eventos')->cascadeOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['evento_id', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
