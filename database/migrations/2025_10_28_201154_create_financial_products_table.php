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
        Schema::create('financial_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Tipo de producto: credit_card, debit_card, digital_wallet, credit_line
            $table->enum('product_type', ['credit_card', 'debit_card', 'digital_wallet', 'credit_line']);

            // Información del producto
            $table->string('name'); // Nombre personalizado del producto
            $table->string('institution')->nullable(); // Banco o institución financiera
            $table->string('last_four_digits', 4)->nullable(); // Últimos 4 dígitos
            $table->string('card_brand')->nullable(); // Visa, Mastercard, etc.

            // Límites y saldos (en centavos para evitar problemas de precisión)
            $table->bigInteger('credit_limit')->nullable()->default(0); // Para tarjetas de crédito y líneas
            $table->bigInteger('current_balance')->default(0); // Saldo actual utilizado
            $table->bigInteger('available_balance')->default(0); // Saldo disponible

            // Fechas importantes
            $table->date('expiration_date')->nullable(); // Para tarjetas
            $table->integer('billing_day')->nullable(); // Día de corte/facturación
            $table->integer('payment_due_day')->nullable(); // Día de vencimiento de pago

            // Estado y configuración
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable(); // Notas adicionales

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('user_id');
            $table->index('product_type');
            $table->index('is_active');
            $table->index(['user_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_products');
    }
};
