<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\FinancialProduct;
use App\Models\Installment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class TransactionService
{
    protected $savingsAccountService;
    protected $loanService;

    public function __construct()
    {
        $this->savingsAccountService = new SavingsAccountService();
        $this->loanService = new LoanService();
    }
    /**
     * Crear una transacción y actualizar balances automáticamente
     *
     * @param array $data Datos de la transacción
     * @return Transaction
     * @throws Exception
     */
    public function createTransaction(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            $product = FinancialProduct::findOrFail($data['financial_product_id']);

            // Validaciones específicas por tipo de producto
            $this->validateTransactionByProductType($product, $data);

            // Para préstamos, usar el servicio especializado que crea su propia transacción
            if ($product->isLoan()) {
                return $this->loanService->processLoanPayment($product, $data['amount'], $data['description'] ?? null);
            }

            // Crear la transacción base para otros tipos de productos
            $transaction = Transaction::create([
                'user_id' => $data['user_id'],
                'financial_product_id' => $data['financial_product_id'],
                'transaction_type' => $data['transaction_type'],
                'amount' => $data['amount'],
                'transaction_date' => $data['transaction_date'],
                'description' => $data['description'] ?? null,
                'reference_number' => $data['reference_number'] ?? null,
                'merchant' => $data['merchant'] ?? null,
            ]);

            // Procesar según el tipo de producto
            $this->processTransactionByProductType($product, $transaction, $data);

            return $transaction->fresh();
        });
    }

    /**
     * Validar transacción según el tipo de producto
     *
     * @param FinancialProduct $product
     * @param array $data
     * @throws Exception
     */
    protected function validateTransactionByProductType(FinancialProduct $product, array $data): void
    {
        $transactionType = $data['transaction_type'];
        $amount = $data['amount'];

        // Validar según el tipo de producto
        if ($product->isCreditCard() || $product->isCreditLine()) {
            // Tarjetas de crédito: validar límite de crédito
            if ($transactionType === 'purchase') {
                $this->validateCreditLimit($product, $amount);
            }
        } elseif ($product->isDebitCard()) {
            // Tarjetas de débito: validar que haya saldo suficiente para compras/retiros
            if (in_array($transactionType, ['purchase', 'withdrawal'])) {
                if ($amount > $product->current_balance) {
                    throw new Exception(
                        "Saldo insuficiente. Disponible: $" . number_format($product->current_balance / 100, 2) .
                        ", Requerido: $" . number_format($amount / 100, 2)
                    );
                }
            }
        } elseif ($product->isSavingsAccount()) {
            // Cuentas de ahorro: validar límite de retiros mensuales
            if ($transactionType === 'withdrawal') {
                $this->savingsAccountService->validateWithdrawalLimit($product);

                // También validar saldo
                if ($amount > $product->current_balance) {
                    throw new Exception("Saldo insuficiente en cuenta de ahorros.");
                }
            }
        } elseif ($product->isLoan()) {
            // Préstamos: solo permitir pagos
            if ($transactionType !== 'payment') {
                throw new Exception("En préstamos solo se permiten pagos de cuotas.");
            }
        }
    }

    /**
     * Procesar transacción según el tipo de producto
     *
     * @param FinancialProduct $product
     * @param Transaction $transaction
     * @param array $data
     */
    protected function processTransactionByProductType(FinancialProduct $product, Transaction $transaction, array $data): void
    {
        if ($product->isCreditCard() || $product->isCreditLine()) {
            // Lógica de tarjetas de crédito (la que ya teníamos)
            $this->processCreditCardTransaction($product, $transaction, $data);
        } elseif ($product->isDebitCard()) {
            // Tarjetas de débito: solo actualizar balance
            $this->updateProductBalance($product, $data['transaction_type'], $data['amount']);
        } elseif ($product->isSavingsAccount()) {
            // Cuentas de ahorro
            $this->updateProductBalance($product, $data['transaction_type'], $data['amount']);

            // Incrementar contador de retiros si aplica
            if ($data['transaction_type'] === 'withdrawal') {
                $this->savingsAccountService->incrementWithdrawalCount($product);
            }
        } else {
            // Digital wallet u otros: solo actualizar balance
            $this->updateProductBalance($product, $data['transaction_type'], $data['amount']);
        }
    }

    /**
     * Procesar transacción específica de tarjeta de crédito
     *
     * @param FinancialProduct $product
     * @param Transaction $transaction
     * @param array $data
     */
    protected function processCreditCardTransaction(FinancialProduct $product, Transaction $transaction, array $data): void
    {
        // Calcular período de facturación y fecha de pago
        $billingInfo = $this->calculateBillingPeriod(
            $product,
            Carbon::parse($data['transaction_date'])
        );

        // Actualizar balance del producto financiero
        $this->updateProductBalance($product, $data['transaction_type'], $data['amount']);

        // Si es en cuotas, crear el plan de installments
        if (!empty($data['installments_count']) && $data['installments_count'] > 1 && $data['transaction_type'] === 'purchase') {
            $this->createInstallmentPlan($transaction, $data, $product);
        }
    }

    /**
     * Validar que haya límite de crédito disponible
     *
     * @param FinancialProduct $product
     * @param int $amount Monto en centavos
     * @throws Exception
     */
    protected function validateCreditLimit(FinancialProduct $product, int $amount): void
    {
        // Solo validar para productos con límite de crédito
        if ($product->credit_limit > 0) {
            $availableCredit = $product->credit_limit - $product->current_balance;

            if ($amount > $availableCredit) {
                throw new Exception(
                    "Límite de crédito insuficiente. Disponible: $" . number_format($availableCredit / 100, 2) .
                    ", Requerido: $" . number_format($amount / 100, 2)
                );
            }
        }
    }

    /**
     * Calcular período de facturación y fecha de pago
     *
     * Ejemplo: Corte día 20, Pago día 15
     * - Compra 15/10 → Período: 21/09-20/10 → Pago: 15/11
     * - Compra 25/10 → Período: 21/10-20/11 → Pago: 15/12
     *
     * @param FinancialProduct $product
     * @param Carbon $transactionDate
     * @return array
     */
    protected function calculateBillingPeriod(FinancialProduct $product, Carbon $transactionDate): array
    {
        $billingDay = $product->billing_day ?? 20;  // Default 20 si no está configurado
        $paymentDay = $product->payment_due_day ?? 15;  // Default 15 si no está configurado

        // Determinar el período de facturación al que pertenece la transacción
        $currentDay = $transactionDate->day;
        $currentMonth = $transactionDate->month;
        $currentYear = $transactionDate->year;

        if ($currentDay <= $billingDay) {
            // La transacción está ANTES del corte de este mes
            // Pertenece al período anterior (mes pasado al día de hoy)
            $periodStart = Carbon::create($currentYear, $currentMonth, $billingDay)->subMonth()->addDay();
            $periodEnd = Carbon::create($currentYear, $currentMonth, $billingDay);
        } else {
            // La transacción está DESPUÉS del corte de este mes
            // Pertenece al período actual (hoy al próximo corte)
            $periodStart = Carbon::create($currentYear, $currentMonth, $billingDay)->addDay();
            $periodEnd = Carbon::create($currentYear, $currentMonth, $billingDay)->addMonth();
        }

        // Calcular fecha de pago (día del pago del mes siguiente al cierre)
        $paymentDueDate = $periodEnd->copy()->addMonth()->day($paymentDay);

        return [
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'payment_due_date' => $paymentDueDate,
            'days_until_payment' => $transactionDate->diffInDays($paymentDueDate),
        ];
    }

    /**
     * Actualizar el balance del producto financiero
     *
     * @param FinancialProduct $product
     * @param string $transactionType
     * @param int $amount Monto en centavos
     */
    protected function updateProductBalance(FinancialProduct $product, string $transactionType, int $amount): void
    {
        switch ($transactionType) {
            case 'purchase':
                // Compra: aumenta el balance (deuda)
                $product->increment('current_balance', $amount);
                break;

            case 'payment':
                // Pago: disminuye el balance (deuda)
                $product->decrement('current_balance', $amount);
                break;

            case 'deposit':
                // Depósito: aumenta el balance (saldo a favor)
                $product->increment('current_balance', $amount);
                break;

            case 'withdrawal':
                // Retiro: disminuye el balance
                $product->decrement('current_balance', $amount);
                break;
        }
    }

    /**
     * Crear plan de cuotas automáticamente
     *
     * @param Transaction $transaction
     * @param array $data
     * @param FinancialProduct $product
     */
    protected function createInstallmentPlan(Transaction $transaction, array $data, FinancialProduct $product): void
    {
        $installmentCount = $data['installments_count'];
        $installmentAmount = (int) ceil($data['amount'] / $installmentCount);

        // Fecha del primer pago: usar la fecha de pago calculada del período actual
        $billingInfo = $this->calculateBillingPeriod($product, Carbon::parse($data['transaction_date']));
        $firstPaymentDate = $billingInfo['payment_due_date'];

        Installment::create([
            'user_id' => $data['user_id'],
            'financial_product_id' => $product->id,
            'total_amount' => $data['amount'],
            'installment_count' => $installmentCount,
            'installment_amount' => $installmentAmount,
            'current_installment' => 0,
            'description' => $data['description'] ?? 'Compra en cuotas',
            'merchant' => $data['merchant'] ?? null,
            'purchase_date' => $data['transaction_date'],
            'first_payment_date' => $firstPaymentDate,
            'status' => Installment::STATUS_ACTIVE,
        ]);
    }

    /**
     * Actualizar una transacción existente
     *
     * @param Transaction $transaction
     * @param array $data
     * @return Transaction
     * @throws Exception
     */
    public function updateTransaction(Transaction $transaction, array $data): Transaction
    {
        return DB::transaction(function () use ($transaction, $data) {
            // Revertir el balance anterior
            $this->revertProductBalance($transaction);

            // Actualizar la transacción
            $transaction->update([
                'financial_product_id' => $data['financial_product_id'],
                'transaction_type' => $data['transaction_type'],
                'amount' => $data['amount'],
                'transaction_date' => $data['transaction_date'],
                'description' => $data['description'] ?? null,
                'reference_number' => $data['reference_number'] ?? null,
                'merchant' => $data['merchant'] ?? null,
            ]);

            // Aplicar el nuevo balance
            $product = FinancialProduct::find($data['financial_product_id']);
            $this->validateCreditLimit($product, $data['amount']);
            $this->updateProductBalance($product, $data['transaction_type'], $data['amount']);

            return $transaction->fresh();
        });
    }

    /**
     * Revertir el balance de una transacción (para editar o eliminar)
     *
     * @param Transaction $transaction
     */
    protected function revertProductBalance(Transaction $transaction): void
    {
        $product = $transaction->financialProduct;

        switch ($transaction->transaction_type) {
            case 'purchase':
                $product->decrement('current_balance', $transaction->amount);
                break;

            case 'payment':
                $product->increment('current_balance', $transaction->amount);
                break;

            case 'deposit':
                $product->decrement('current_balance', $transaction->amount);
                break;

            case 'withdrawal':
                $product->increment('current_balance', $transaction->amount);
                break;
        }
    }

    /**
     * Eliminar una transacción y revertir balances
     *
     * @param Transaction $transaction
     * @throws Exception
     */
    public function deleteTransaction(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            // Revertir balance
            $this->revertProductBalance($transaction);

            // Eliminar la transacción
            $transaction->delete();
        });
    }

    /**
     * Obtener resumen del período actual para un producto
     *
     * @param FinancialProduct $product
     * @return array
     */
    public function getCurrentPeriodSummary(FinancialProduct $product): array
    {
        $today = Carbon::today();
        $billingInfo = $this->calculateBillingPeriod($product, $today);

        // Obtener transacciones del período actual
        $periodTransactions = Transaction::where('financial_product_id', $product->id)
            ->whereBetween('transaction_date', [
                $billingInfo['period_start'],
                $billingInfo['period_end']
            ])
            ->get();

        $periodTotal = $periodTransactions->where('transaction_type', 'purchase')->sum('amount');
        $periodPayments = $periodTransactions->where('transaction_type', 'payment')->sum('amount');

        return [
            'period_start' => $billingInfo['period_start'],
            'period_end' => $billingInfo['period_end'],
            'payment_due_date' => $billingInfo['payment_due_date'],
            'days_until_payment' => $today->diffInDays($billingInfo['payment_due_date']),
            'period_total' => $periodTotal,
            'period_payments' => $periodPayments,
            'period_balance' => $periodTotal - $periodPayments,
            'available_credit' => $product->credit_limit - $product->current_balance,
        ];
    }
}
