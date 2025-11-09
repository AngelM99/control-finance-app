<?php

namespace App\Services;

use App\Models\FinancialProduct;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class LoanService
{
    /**
     * Calcular cuota mensual usando fórmula de amortización francesa
     *
     * M = P * [i(1 + i)^n] / [(1 + i)^n - 1]
     * Donde:
     * M = Cuota mensual
     * P = Monto del préstamo
     * i = Tasa de interés mensual (TEA / 12)
     * n = Número de cuotas
     *
     * @param int $loanAmount Monto del préstamo en centavos
     * @param float $annualInterestRate Tasa efectiva anual (%)
     * @param int $termMonths Plazo en meses
     * @return int Cuota mensual en centavos
     */
    public function calculateMonthlyPayment(int $loanAmount, float $annualInterestRate, int $termMonths): int
    {
        if ($termMonths <= 0 || $loanAmount <= 0) {
            return 0;
        }

        if ($annualInterestRate <= 0) {
            // Sin intereses, solo dividir el monto
            return (int) round($loanAmount / $termMonths);
        }

        // Convertir tasa anual a mensual
        $monthlyRate = ($annualInterestRate / 100) / 12;

        // Fórmula de amortización
        $numerator = $loanAmount * $monthlyRate * pow(1 + $monthlyRate, $termMonths);
        $denominator = pow(1 + $monthlyRate, $termMonths) - 1;

        return (int) round($numerator / $denominator);
    }

    /**
     * Generar cronograma completo de pagos del préstamo
     *
     * @param FinancialProduct $product
     * @return array
     * @throws Exception
     */
    public function generatePaymentSchedule(FinancialProduct $product): array
    {
        if (!$product->isLoan()) {
            throw new Exception('Este producto no es un préstamo.');
        }

        $schedule = [];
        $remainingBalance = $product->loan_amount;
        $monthlyRate = ($product->interest_rate / 100) / 12;
        $paymentDate = $product->start_date ? Carbon::parse($product->start_date) : now();

        for ($i = 1; $i <= $product->loan_term_months; $i++) {
            $paymentDate = $paymentDate->copy()->addMonth();

            // Calcular interés y capital
            $interestAmount = (int) round($remainingBalance * $monthlyRate);
            $principalAmount = $product->monthly_payment - $interestAmount;
            $remainingBalance -= $principalAmount;

            // Asegurar que el último pago ajuste cualquier diferencia por redondeo
            if ($i == $product->loan_term_months && $remainingBalance != 0) {
                $principalAmount += $remainingBalance;
                $remainingBalance = 0;
            }

            $isPaid = $i <= $product->payments_made;
            $isOverdue = !$isPaid && $paymentDate->lt(now());

            $schedule[] = [
                'payment_number' => $i,
                'payment_date' => $paymentDate->format('Y-m-d'),
                'payment_date_formatted' => $paymentDate->format('d/m/Y'),
                'monthly_payment' => $product->monthly_payment,
                'monthly_payment_dollars' => $product->monthly_payment / 100,
                'principal' => $principalAmount,
                'principal_dollars' => $principalAmount / 100,
                'interest' => $interestAmount,
                'interest_dollars' => $interestAmount / 100,
                'remaining_balance' => max(0, $remainingBalance),
                'remaining_balance_dollars' => max(0, $remainingBalance) / 100,
                'is_paid' => $isPaid,
                'is_overdue' => $isOverdue,
                'days_overdue' => $isOverdue ? now()->diffInDays($paymentDate) : 0,
            ];
        }

        return $schedule;
    }

    /**
     * Procesar pago de una cuota del préstamo
     *
     * @param FinancialProduct $product
     * @param int $amount Monto del pago en centavos
     * @param string|null $description
     * @return Transaction
     * @throws Exception
     */
    public function processLoanPayment(FinancialProduct $product, int $amount, ?string $description = null): Transaction
    {
        if (!$product->isLoan()) {
            throw new Exception('Este producto no es un préstamo.');
        }

        return DB::transaction(function () use ($product, $amount, $description) {
            // Crear transacción de pago
            $transaction = Transaction::create([
                'user_id' => $product->user_id,
                'financial_product_id' => $product->id,
                'transaction_type' => 'payment',
                'amount' => $amount,
                'transaction_date' => now(),
                'description' => $description ?? 'Pago de cuota préstamo',
            ]);

            // Incrementar contador de pagos realizados
            $product->increment('payments_made');

            // Actualizar balance (reducir deuda)
            $product->decrement('current_balance', $amount);

            // Actualizar fecha del próximo pago
            if ($product->next_payment_date) {
                $nextPaymentDate = Carbon::parse($product->next_payment_date)->addMonth();
                $product->update(['next_payment_date' => $nextPaymentDate]);
            }

            // Si ya se pagó todo el préstamo, marcar como completado
            if ($product->payments_made >= $product->loan_term_months) {
                $product->update([
                    'is_active' => false,
                    'notes' => ($product->notes ?? '') . "\nPréstamo completado: " . now()->format('d/m/Y'),
                ]);
            }

            return $transaction;
        });
    }

    /**
     * Calcular interés moratorio por pago atrasado
     *
     * @param FinancialProduct $product
     * @param int $daysOverdue Días de atraso
     * @param float $penaltyRate Tasa de interés moratorio anual (%)
     * @return int Interés moratorio en centavos
     */
    public function calculatePenaltyInterest(FinancialProduct $product, int $daysOverdue, float $penaltyRate = 50.0): int
    {
        if ($daysOverdue <= 0 || !$product->monthly_payment) {
            return 0;
        }

        // Calcular interés moratorio diario
        $dailyRate = ($penaltyRate / 100) / 365;
        $penaltyAmount = (int) round($product->monthly_payment * $dailyRate * $daysOverdue);

        return $penaltyAmount;
    }

    /**
     * Obtener resumen del préstamo
     *
     * @param FinancialProduct $product
     * @return array
     * @throws Exception
     */
    public function getLoanSummary(FinancialProduct $product): array
    {
        if (!$product->isLoan()) {
            throw new Exception('Este producto no es un préstamo.');
        }

        $totalPaid = $product->payments_made * $product->monthly_payment;
        $totalInterest = ($product->monthly_payment * $product->loan_term_months) - $product->loan_amount;
        $remainingPayments = $product->remaining_payments;
        $remainingAmount = $remainingPayments * $product->monthly_payment;

        // Verificar si hay pagos atrasados
        $isOverdue = false;
        $daysOverdue = 0;
        if ($product->next_payment_date) {
            $nextPaymentDate = Carbon::parse($product->next_payment_date);
            if ($nextPaymentDate->lt(now()) && $remainingPayments > 0) {
                $isOverdue = true;
                $daysOverdue = now()->diffInDays($nextPaymentDate);
            }
        }

        return [
            'loan_amount' => $product->loan_amount,
            'loan_amount_dollars' => $product->loan_amount_in_dollars,
            'interest_rate' => $product->interest_rate,
            'loan_term_months' => $product->loan_term_months,
            'monthly_payment' => $product->monthly_payment,
            'monthly_payment_dollars' => $product->monthly_payment_in_dollars,
            'start_date' => $product->start_date,
            'next_payment_date' => $product->next_payment_date,
            'payments_made' => $product->payments_made,
            'remaining_payments' => $remainingPayments,
            'progress_percentage' => $product->loan_progress_percentage,
            'total_paid' => $totalPaid,
            'total_paid_dollars' => $totalPaid / 100,
            'remaining_amount' => $remainingAmount,
            'remaining_amount_dollars' => $remainingAmount / 100,
            'total_interest' => $totalInterest,
            'total_interest_dollars' => $totalInterest / 100,
            'current_balance' => $product->current_balance,
            'current_balance_dollars' => $product->current_balance / 100,
            'available_credit' => 0, // Los préstamos no tienen crédito disponible
            'is_overdue' => $isOverdue,
            'days_overdue' => $daysOverdue,
            'penalty_interest' => $isOverdue ? $this->calculatePenaltyInterest($product, $daysOverdue) : 0,
        ];
    }

    /**
     * Obtener todos los préstamos con pagos vencidos
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getOverdueLoans()
    {
        return FinancialProduct::loans()
            ->where('is_active', true)
            ->whereNotNull('next_payment_date')
            ->where('next_payment_date', '<', now())
            ->where('payments_made', '<', DB::raw('loan_term_months'))
            ->get();
    }

    /**
     * Inicializar préstamo (calcular cuota y fecha del primer pago)
     *
     * @param array $data
     * @return array
     */
    public function initializeLoan(array $data): array
    {
        $loanAmount = $data['loan_amount'];
        $interestRate = $data['interest_rate'];
        $termMonths = $data['loan_term_months'];
        $startDate = isset($data['start_date']) ? Carbon::parse($data['start_date']) : now();

        // Calcular cuota mensual
        $monthlyPayment = $this->calculateMonthlyPayment($loanAmount, $interestRate, $termMonths);

        // Calcular fecha del primer pago (un mes después del inicio)
        $nextPaymentDate = $startDate->copy()->addMonth();

        return [
            'monthly_payment' => $monthlyPayment,
            'next_payment_date' => $nextPaymentDate->format('Y-m-d'),
            'start_date' => $startDate->format('Y-m-d'),
            'current_balance' => $loanAmount, // La deuda inicial es el monto total
        ];
    }
}
