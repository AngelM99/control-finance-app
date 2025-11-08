<?php

namespace App\Services;

use App\Models\Installment;
use App\Models\FinancialProduct;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class InstallmentPaymentService
{
    /**
     * Registrar un pago para un plan de cuotas
     *
     * @param Installment $installment
     * @param int $amount Monto en centavos
     * @param string $paymentDate Fecha del pago
     * @return array Resultado del pago con detalles
     * @throws Exception
     */
    public function registerPayment(Installment $installment, int $amount, string $paymentDate): array
    {
        return DB::transaction(function () use ($installment, $amount, $paymentDate) {
            // Validar que el monto sea positivo
            if ($amount <= 0) {
                throw new Exception('El monto del pago debe ser mayor a cero.');
            }

            // Validar que no se haya completado el plan
            if ($installment->status === Installment::STATUS_COMPLETED) {
                throw new Exception('Este plan de cuotas ya está completado.');
            }

            // Calcular el saldo pendiente total
            $totalDebt = $installment->total_amount;
            $totalPaid = $installment->total_paid ?? 0;
            $remaining = $totalDebt - $totalPaid;

            // Validar que no se pague más de lo debido
            if ($amount > $remaining) {
                throw new Exception(
                    "El monto a pagar (S/ " . number_format($amount / 100, 2) . ") " .
                    "excede el saldo pendiente (S/ " . number_format($remaining / 100, 2) . ")."
                );
            }

            // Actualizar total pagado
            $newTotalPaid = $totalPaid + $amount;
            $newRemaining = $totalDebt - $newTotalPaid;

            // Obtener el schedule actual o crear uno nuevo
            $schedule = $installment->payment_schedule ?? ['payments' => []];

            // Agregar el nuevo pago al historial
            $schedule['payments'][] = [
                'payment_date' => $paymentDate,
                'amount' => $amount,
                'amount_dollars' => $amount / 100,
                'remaining_after' => $newRemaining,
                'remaining_after_dollars' => $newRemaining / 100,
                'registered_at' => now()->toDateTimeString(),
            ];

            // Calcular cuántas cuotas completas se han pagado
            $installmentAmount = $installment->installment_amount;
            $completedInstallments = floor($newTotalPaid / $installmentAmount);

            // Actualizar el modelo
            $installment->update([
                'total_paid' => $newTotalPaid,
                'last_payment_date' => $paymentDate,
                'current_installment' => $completedInstallments,
                'payment_schedule' => $schedule,
                'status' => $newRemaining <= 0 ? Installment::STATUS_COMPLETED : Installment::STATUS_ACTIVE,
            ]);

            // Actualizar el saldo del producto financiero (reducir la deuda)
            $financialProduct = $installment->financialProduct;
            if ($financialProduct) {
                $newProductBalance = max(0, $financialProduct->current_balance - $amount);
                $financialProduct->update([
                    'current_balance' => $newProductBalance,
                ]);
            }

            // Preparar respuesta con detalles del pago
            return [
                'success' => true,
                'payment_amount' => $amount,
                'payment_amount_dollars' => $amount / 100,
                'total_paid' => $newTotalPaid,
                'total_paid_dollars' => $newTotalPaid / 100,
                'remaining' => $newRemaining,
                'remaining_dollars' => $newRemaining / 100,
                'completed_installments' => $completedInstallments,
                'total_installments' => $installment->installment_count,
                'is_completed' => $newRemaining <= 0,
                'payment_type' => $this->getPaymentType($amount, $installmentAmount, $remaining),
            ];
        });
    }

    /**
     * Determinar el tipo de pago realizado
     *
     * @param int $amount Monto pagado
     * @param int $installmentAmount Monto de una cuota
     * @param int $remaining Saldo restante
     * @return string
     */
    protected function getPaymentType(int $amount, int $installmentAmount, int $remaining): string
    {
        if ($amount >= $remaining) {
            return 'complete'; // Pago completo del plan
        } elseif ($amount >= $installmentAmount) {
            return 'full_installment'; // Pago de cuota(s) completa(s)
        } else {
            return 'partial'; // Pago parcial
        }
    }

    /**
     * Obtener el resumen de pagos de un plan de cuotas
     *
     * @param Installment $installment
     * @return array
     */
    public function getPaymentSummary(Installment $installment): array
    {
        $totalDebt = $installment->total_amount;
        $totalPaid = $installment->total_paid ?? 0;
        $remaining = $totalDebt - $totalPaid;

        $installmentAmount = $installment->installment_amount;
        $completedInstallments = floor($totalPaid / $installmentAmount);
        $partialAmount = $totalPaid % $installmentAmount;

        return [
            'total_debt' => $totalDebt,
            'total_debt_dollars' => $totalDebt / 100,
            'total_paid' => $totalPaid,
            'total_paid_dollars' => $totalPaid / 100,
            'remaining' => $remaining,
            'remaining_dollars' => $remaining / 100,
            'payment_percentage' => $totalDebt > 0 ? ($totalPaid / $totalDebt) * 100 : 0,
            'completed_installments' => $completedInstallments,
            'total_installments' => $installment->installment_count,
            'partial_amount' => $partialAmount,
            'partial_amount_dollars' => $partialAmount / 100,
            'next_installment_amount' => $installmentAmount - $partialAmount,
            'next_installment_amount_dollars' => ($installmentAmount - $partialAmount) / 100,
            'payment_history' => $installment->payment_schedule['payments'] ?? [],
        ];
    }

    /**
     * Calcular el monto sugerido para el próximo pago
     *
     * @param Installment $installment
     * @return int Monto en centavos
     */
    public function getSuggestedPaymentAmount(Installment $installment): int
    {
        $totalDebt = $installment->total_amount;
        $totalPaid = $installment->total_paid ?? 0;
        $remaining = $totalDebt - $totalPaid;

        $installmentAmount = $installment->installment_amount;

        // Si queda menos de una cuota, sugerir el saldo completo
        if ($remaining <= $installmentAmount) {
            return $remaining;
        }

        // Si hay pagos parciales previos, verificar si es significativo
        $partialAmount = $totalPaid % $installmentAmount;

        if ($partialAmount > 0) {
            $partialPercentage = ($partialAmount / $installmentAmount) * 100;
            $remainingForCurrentInstallment = $installmentAmount - $partialAmount;

            // Si el pago parcial es mayor al 90%, probablemente es un error de redondeo
            // En ese caso, ignorarlo y sugerir una cuota completa nueva
            if ($partialPercentage >= 90) {
                // Sugerir una cuota completa, ignorando el pequeño error de centavos
                return $installmentAmount;
            }

            // Si el pago parcial está entre 10% y 90%, sugerir completar la cuota actual
            if ($partialPercentage >= 10) {
                // Si el monto restante para completar la cuota es mayor al saldo total, usar el saldo
                return min($remainingForCurrentInstallment, $remaining);
            }
        }

        // Caso normal: sugerir el monto de una cuota completa
        return $installmentAmount;
    }

    /**
     * Eliminar un pago específico del historial
     *
     * @param Installment $installment
     * @param int $paymentIndex Índice del pago a eliminar en el array
     * @return array Resultado de la operación
     * @throws Exception
     */
    public function deletePayment(Installment $installment, int $paymentIndex): array
    {
        return DB::transaction(function () use ($installment, $paymentIndex) {
            // Obtener el historial de pagos
            $schedule = $installment->payment_schedule ?? ['payments' => []];
            $payments = $schedule['payments'] ?? [];

            // Validar que el índice existe
            if (!isset($payments[$paymentIndex])) {
                throw new Exception('El pago especificado no existe.');
            }

            // Obtener el pago a eliminar
            $paymentToDelete = $payments[$paymentIndex];
            $amountToReverse = $paymentToDelete['amount'];

            // Eliminar el pago del array
            array_splice($payments, $paymentIndex, 1);

            // Recalcular el total pagado
            $newTotalPaid = ($installment->total_paid ?? 0) - $amountToReverse;
            $newRemaining = $installment->total_amount - $newTotalPaid;

            // Recalcular cuotas completas pagadas
            $installmentAmount = $installment->installment_amount;
            $completedInstallments = floor($newTotalPaid / $installmentAmount);

            // Actualizar el schedule con los pagos restantes
            $schedule['payments'] = array_values($payments); // Re-indexar el array

            // Actualizar el modelo
            $installment->update([
                'total_paid' => $newTotalPaid,
                'current_installment' => $completedInstallments,
                'payment_schedule' => $schedule,
                'status' => $newRemaining >= $installment->total_amount ? Installment::STATUS_ACTIVE :
                           ($newRemaining <= 0 ? Installment::STATUS_COMPLETED : Installment::STATUS_ACTIVE),
            ]);

            // Actualizar el saldo del producto financiero (aumentar la deuda)
            $financialProduct = $installment->financialProduct;
            if ($financialProduct) {
                $newProductBalance = $financialProduct->current_balance + $amountToReverse;
                $financialProduct->update([
                    'current_balance' => $newProductBalance,
                ]);
            }

            return [
                'success' => true,
                'reversed_amount' => $amountToReverse,
                'reversed_amount_dollars' => $amountToReverse / 100,
                'new_total_paid' => $newTotalPaid,
                'new_total_paid_dollars' => $newTotalPaid / 100,
                'new_remaining' => $newRemaining,
                'new_remaining_dollars' => $newRemaining / 100,
            ];
        });
    }
}
