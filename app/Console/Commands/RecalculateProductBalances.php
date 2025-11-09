<?php

namespace App\Console\Commands;

use App\Models\FinancialProduct;
use App\Models\Transaction;
use App\Models\Installment;
use Illuminate\Console\Command;

class RecalculateProductBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:recalculate-balances {--product-id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalcular los balances de productos financieros basándose en transacciones y cuotas pagadas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando recalculo de balances...');
        $this->newLine();

        // Si se especifica un producto específico
        if ($productId = $this->option('product-id')) {
            $products = FinancialProduct::where('id', $productId)->get();
            if ($products->isEmpty()) {
                $this->error("No se encontró el producto con ID: {$productId}");
                return 1;
            }
        } else {
            // Recalcular todos los productos
            $products = FinancialProduct::all();
        }

        $this->info("Procesando {$products->count()} productos...");
        $this->newLine();

        $corrected = 0;
        $unchanged = 0;

        foreach ($products as $product) {
            $this->line("Procesando: {$product->name} (ID: {$product->id}) - Tipo: {$product->product_type}");

            $oldBalance = $product->current_balance;
            $newBalance = $this->calculateCorrectBalance($product);

            if ($oldBalance !== $newBalance) {
                $this->warn("  Balance Anterior: S/ " . number_format($oldBalance / 100, 2));
                $this->info("  Balance Correcto: S/ " . number_format($newBalance / 100, 2));
                $this->warn("  Diferencia: S/ " . number_format(($newBalance - $oldBalance) / 100, 2));

                // Actualizar el balance
                $product->update(['current_balance' => $newBalance]);
                $corrected++;
            } else {
                $this->comment("  Balance correcto: S/ " . number_format($oldBalance / 100, 2) . " ✓");
                $unchanged++;
            }

            $this->newLine();
        }

        $this->newLine();
        $this->info("=== RESUMEN ===");
        $this->info("Productos corregidos: {$corrected}");
        $this->info("Productos sin cambios: {$unchanged}");
        $this->info("Total procesados: {$products->count()}");

        return 0;
    }

    /**
     * Calcular el balance correcto de un producto
     */
    protected function calculateCorrectBalance(FinancialProduct $product): int
    {
        // Obtener todas las transacciones
        $transactions = Transaction::where('financial_product_id', $product->id)->get();

        $totalPurchases = $transactions->where('transaction_type', 'purchase')->sum('amount');
        $totalPayments = $transactions->where('transaction_type', 'payment')->sum('amount');
        $totalDeposits = $transactions->where('transaction_type', 'deposit')->sum('amount');
        $totalWithdrawals = $transactions->where('transaction_type', 'withdrawal')->sum('amount');

        // Calcular cuotas pagadas (solo para tarjetas de crédito y líneas de crédito)
        $totalPaidInstallments = 0;
        if ($product->isCreditCard() || $product->isCreditLine()) {
            $installments = Installment::where('financial_product_id', $product->id)->get();

            foreach ($installments as $installment) {
                $paidAmount = $installment->current_installment * $installment->installment_amount;
                $totalPaidInstallments += $paidAmount;
            }
        }

        // Calcular balance según el tipo de producto
        $balance = 0;

        if ($product->isCreditCard() || $product->isCreditLine()) {
            // Para tarjetas de crédito: Compras - Pagos - Cuotas Pagadas
            $balance = $totalPurchases - $totalPayments - $totalPaidInstallments;
        } elseif ($product->isDebitCard() || $product->isSavingsAccount() || $product->isDigitalWallet()) {
            // Para productos de dinero a favor: Depósitos - Retiros
            $balance = $totalDeposits - $totalWithdrawals + $totalPurchases - $totalPayments;
        } elseif ($product->isLoan()) {
            // Para préstamos: Monto del préstamo - Pagos realizados
            $balance = $product->loan_amount - $totalPayments;
        }

        return max(0, $balance);
    }
}
