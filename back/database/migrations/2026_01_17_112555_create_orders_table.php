<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Stripe
            $table->string('session_id')->unique(); // cs_test_...
            $table->string('payment_intent_id')->nullable();

            // Cliente
            $table->string('email')->nullable();
            $table->string('localizador')->nullable();

            // Totales (en centavos)
            $table->decimal('amount_total', 10, 2)->default(0);
            $table->string('currency', 10)->default('eur');

            // Estado
            $table->string('status')->default('PENDING'); // PENDING | PAID | CANCELED | FAILED
            $table->timestamp('paid_at')->nullable();

            // Datos extra (tu metadata y items)
            $table->json('metadata')->nullable();
            $table->json('items')->nullable();

            $table->string('dni')->nullable();
            $table->string('nombre_completo')->nullable();

//            nacionalidada entrada
            $table->string('nacionalidad')->nullable();
            $table->string('entrada_tipo')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
