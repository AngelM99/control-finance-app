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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('financial_product_id')->nullable()->constrained()->nullOnDelete();

            // Tipo de transacción: purchase, payment, transfer, withdrawal, deposit, refund, adjustment
            $table->enum('transaction_type', [
                'purchase',
                'payment',
                'transfer',
                'withdrawal',
                'deposit',
                'refund',
                'adjustment'
            ]);

            // Monto en centavos (positivo o negativo según el tipo)
            $table->bigInteger('amount');

            // Información de la transacción
            $table->string('description');
            $table->date('transaction_date');
            $table->string('category')->nullable(); // Categoría de gasto
            $table->string('merchant')->nullable(); // Comercio donde se realizó
            $table->string('reference_number')->nullable(); // Número de referencia/comprobante

            // Relación con cuotas
            $table->boolean('is_installment')->default(false);
            $table->foreignId('installment_id')->nullable()->constrained()->nullOnDelete();

            // Estado: completed, pending, canceled, refunded
            $table->enum('status', ['completed', 'pending', 'canceled', 'refunded'])->default('completed');

            // Metadata adicional
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // Para almacenar datos adicionales

            $table->timestamps();
            $table->softDeletes();

            // Índices optimizados
            $table->index('user_id');
            $table->index('financial_product_id');
            $table->index('transaction_type');
            $table->index('transaction_date');
            $table->index('status');
            $table->index('category');
            $table->index(['user_id', 'transaction_date']);
            $table->index(['user_id', 'status']);
            $table->index(['financial_product_id', 'transaction_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
