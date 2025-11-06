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
        Schema::table('installments', function (Blueprint $table) {
            // Monto total pagado hasta ahora
            $table->bigInteger('total_paid')->default(0)->after('current_installment');

            // Fecha del último pago realizado
            $table->date('last_payment_date')->nullable()->after('first_payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('installments', function (Blueprint $table) {
            $table->dropColumn(['total_paid', 'last_payment_date']);
        });
    }
};
