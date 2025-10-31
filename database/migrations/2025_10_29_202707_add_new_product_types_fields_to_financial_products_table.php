<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primero, modificar el enum para agregar los nuevos tipos
        DB::statement("ALTER TABLE financial_products MODIFY COLUMN product_type ENUM('credit_card', 'debit_card', 'digital_wallet', 'credit_line', 'savings_account', 'personal_loan', 'asset_loan')");

        Schema::table('financial_products', function (Blueprint $table) {
            // Campos para Cuentas de Ahorro (savings_account)
            $table->decimal('interest_rate', 5, 2)->nullable()->after('payment_due_day')
                ->comment('Tasa de interés anual (%) para cuentas de ahorro y préstamos');
            $table->date('last_interest_date')->nullable()->after('interest_rate')
                ->comment('Última fecha de abono de intereses');
            $table->integer('monthly_withdrawal_limit')->nullable()->after('last_interest_date')
                ->comment('Límite de retiros por mes');
            $table->integer('current_month_withdrawals')->default(0)->after('monthly_withdrawal_limit')
                ->comment('Número de retiros realizados en el mes actual');

            // Campos para Préstamos (personal_loan, asset_loan)
            $table->bigInteger('loan_amount')->nullable()->after('current_month_withdrawals')
                ->comment('Monto total del préstamo en centavos');
            $table->integer('loan_term_months')->nullable()->after('loan_amount')
                ->comment('Plazo del préstamo en meses');
            $table->bigInteger('monthly_payment')->nullable()->after('loan_term_months')
                ->comment('Cuota mensual en centavos');
            $table->date('start_date')->nullable()->after('monthly_payment')
                ->comment('Fecha de inicio del préstamo');
            $table->date('next_payment_date')->nullable()->after('start_date')
                ->comment('Fecha del próximo pago del préstamo');
            $table->integer('payments_made')->default(0)->after('next_payment_date')
                ->comment('Número de cuotas pagadas');

            // Campos para Crédito por Bien (asset_loan)
            $table->string('asset_type')->nullable()->after('payments_made')
                ->comment('Tipo de bien financiado (vehículo, electrodoméstico, etc.)');
            $table->string('supplier')->nullable()->after('asset_type')
                ->comment('Proveedor o tienda del bien financiado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_products', function (Blueprint $table) {
            $table->dropColumn([
                'interest_rate',
                'last_interest_date',
                'monthly_withdrawal_limit',
                'current_month_withdrawals',
                'loan_amount',
                'loan_term_months',
                'monthly_payment',
                'start_date',
                'next_payment_date',
                'payments_made',
                'asset_type',
                'supplier',
            ]);
        });

        // Revertir el enum a los valores originales
        DB::statement("ALTER TABLE financial_products MODIFY COLUMN product_type ENUM('credit_card', 'debit_card', 'digital_wallet', 'credit_line')");
    }
};
