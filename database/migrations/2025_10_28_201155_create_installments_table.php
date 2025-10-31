<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('financial_product_id')->constrained()->cascadeOnDelete();

            // Información del plan de cuotas
            $table->bigInteger('total_amount'); // Monto total en centavos
            $table->integer('installment_count'); // Número total de cuotas
            $table->bigInteger('installment_amount'); // Monto por cuota en centavos
            $table->integer('current_installment')->default(0); // Cuota actual pagada

            // Descripción de la compra
            $table->string('description');
            $table->string('merchant')->nullable(); // Comercio
            $table->date('purchase_date'); // Fecha de compra
            $table->date('first_payment_date'); // Fecha del primer pago

            // Estado: active, completed, canceled
            $table->enum('status', ['active', 'completed', 'canceled'])->default('active');

            // Información adicional
            $table->text('notes')->nullable();
            $table->json('payment_schedule')->nullable(); // Cronograma de pagos con fechas

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('user_id');
            $table->index('financial_product_id');
            $table->index('status');
            $table->index('purchase_date');
            $table->index(['user_id', 'status']);
            $table->index(['financial_product_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installments');
    }
};
