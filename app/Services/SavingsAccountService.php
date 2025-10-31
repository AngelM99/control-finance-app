<?php

namespace App\Services;

use App\Models\FinancialProduct;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class SavingsAccountService
{
    /**
     * Calcular y abonar intereses mensuales para una cuenta de ahorros
     *
     * @param FinancialProduct $product
     * @return Transaction|null
     * @throws Exception
     */
    public function calculateAndApplyMonthlyInterest(FinancialProduct $product): ?Transaction
    {
        if (!$product->isSavingsAccount()) {
            throw new Exception('Este producto no es una cuenta de ahorros.');
        }

        if (!$product->interest_rate || $product->interest_rate <= 0) {
            return null; // No tiene tasa de interés configurada
        }

        // Calcular interés mensual
        $annualRate = $product->interest_rate / 100; // Convertir porcentaje a decimal
        $monthlyRate = $annualRate / 12;
        $interestAmount = (int) round($product->current_balance * $monthlyRate);

        if ($interestAmount <= 0) {
            return null;
        }

        return DB::transaction(function () use ($product, $interestAmount) {
            // Crear transacción de intereses
            $transaction = Transaction::create([
                'user_id' => $product->user_id,
                'financial_product_id' => $product->id,
                'transaction_type' => 'deposit', // Los intereses son como un depósito
                'amount' => $interestAmount,
                'transaction_date' => now(),
                'description' => 'Interés mensual generado (' . $product->interest_rate . '% anual)',
            ]);

            // Actualizar balance
            $product->increment('current_balance', $interestAmount);

            // Actualizar fecha del último abono de intereses
            $product->update(['last_interest_date' => now()]);

            return $transaction;
        });
    }

    /**
     * Validar si se puede realizar un retiro (verificar límite mensual)
     *
     * @param FinancialProduct $product
     * @throws Exception
     */
    public function validateWithdrawalLimit(FinancialProduct $product): void
    {
        if (!$product->isSavingsAccount()) {
            return; // No aplica validación para otros tipos
        }

        if (!$product->monthly_withdrawal_limit) {
            return; // No tiene límite configurado
        }

        if ($product->current_month_withdrawals >= $product->monthly_withdrawal_limit) {
            throw new Exception(
                "Límite de retiros mensuales alcanzado. Permitidos: {$product->monthly_withdrawal_limit}, Realizados: {$product->current_month_withdrawals}"
            );
        }
    }

    /**
     * Incrementar contador de retiros del mes
     *
     * @param FinancialProduct $product
     */
    public function incrementWithdrawalCount(FinancialProduct $product): void
    {
        if ($product->isSavingsAccount()) {
            $product->increment('current_month_withdrawals');
        }
    }

    /**
     * Resetear contador de retiros mensuales (ejecutar cada inicio de mes)
     *
     * @param FinancialProduct $product
     */
    public function resetMonthlyWithdrawals(FinancialProduct $product): void
    {
        if ($product->isSavingsAccount()) {
            $product->update(['current_month_withdrawals' => 0]);
        }
    }

    /**
     * Resetear contadores de todos los usuarios (ejecutar con un cron job)
     */
    public function resetAllMonthlyWithdrawals(): int
    {
        return FinancialProduct::savingsAccounts()
            ->where('is_active', true)
            ->update(['current_month_withdrawals' => 0]);
    }

    /**
     * Aplicar intereses a todas las cuentas de ahorro activas
     * (ejecutar con un cron job mensualmente)
     *
     * @return int Número de cuentas procesadas
     */
    public function applyInterestToAllAccounts(): int
    {
        $accounts = FinancialProduct::savingsAccounts()
            ->where('is_active', true)
            ->where('interest_rate', '>', 0)
            ->get();

        $processed = 0;

        foreach ($accounts as $account) {
            try {
                // Solo aplicar si ha pasado al menos un mes desde el último abono
                $lastInterestDate = $account->last_interest_date ?? $account->created_at;
                $daysSinceLastInterest = now()->diffInDays($lastInterestDate);

                if ($daysSinceLastInterest >= 30) {
                    $this->calculateAndApplyMonthlyInterest($account);
                    $processed++;
                }
            } catch (Exception $e) {
                // Log error pero continuar con las demás cuentas
                \Log::error("Error aplicando intereses a cuenta {$account->id}: " . $e->getMessage());
            }
        }

        return $processed;
    }

    /**
     * Obtener proyección de intereses para los próximos meses
     *
     * @param FinancialProduct $product
     * @param int $months Número de meses a proyectar
     * @return array
     */
    public function getInterestProjection(FinancialProduct $product, int $months = 12): array
    {
        if (!$product->isSavingsAccount() || !$product->interest_rate) {
            return [];
        }

        $projection = [];
        $balance = $product->current_balance;
        $annualRate = $product->interest_rate / 100;
        $monthlyRate = $annualRate / 12;

        for ($i = 1; $i <= $months; $i++) {
            $interest = (int) round($balance * $monthlyRate);
            $balance += $interest;

            $projection[] = [
                'month' => $i,
                'interest' => $interest,
                'interest_dollars' => $interest / 100,
                'balance' => $balance,
                'balance_dollars' => $balance / 100,
            ];
        }

        return $projection;
    }

    /**
     * Obtener resumen de la cuenta de ahorros
     *
     * @param FinancialProduct $product
     * @return array
     */
    public function getAccountSummary(FinancialProduct $product): array
    {
        if (!$product->isSavingsAccount()) {
            throw new Exception('Este producto no es una cuenta de ahorros.');
        }

        $annualRate = $product->interest_rate ? $product->interest_rate / 100 : 0;
        $monthlyRate = $annualRate / 12;
        $estimatedMonthlyInterest = (int) round($product->current_balance * $monthlyRate);

        return [
            'current_balance' => $product->current_balance,
            'current_balance_dollars' => $product->current_balance / 100,
            'interest_rate' => $product->interest_rate,
            'estimated_monthly_interest' => $estimatedMonthlyInterest,
            'estimated_monthly_interest_dollars' => $estimatedMonthlyInterest / 100,
            'last_interest_date' => $product->last_interest_date,
            'monthly_withdrawal_limit' => $product->monthly_withdrawal_limit,
            'current_month_withdrawals' => $product->current_month_withdrawals,
            'remaining_withdrawals' => $product->remaining_withdrawals,
            'can_withdraw' => $product->canWithdraw(),
        ];
    }
}
