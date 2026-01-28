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
            $table->string('session_id')->unique(); // cs_test_...
            $table->string('payment_intent_id')->nullable();

            // Cliente
            $table->string('email')->nullable();
            $table->string('localizador')->nullable();
//            orders
            $table->integer('orden')->default(0);

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
            $table->foreignId('evento_id')->constrained('eventos')->cascadeOnDelete();
            $table->dateTime('starts_at')->nullable()->after('evento_id');

            $table->unsignedBigInteger('horario_adulto_id')->nullable()->after('starts_at');
            $table->unsignedBigInteger('horario_nino_id')->nullable()->after('horario_adulto_id');

            $table->unsignedInteger('adults')->default(0)->after('horario_nino_id');
            $table->unsignedInteger('kids')->default(0)->after('adults');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
