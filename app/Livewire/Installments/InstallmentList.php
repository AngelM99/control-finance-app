<?php

namespace App\Livewire\Installments;

use App\Models\Installment;
use App\Services\InstallmentPaymentService;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Cuotas - Control Finance')]
class InstallmentList extends Component
{
    public $showPaymentModal = false;
    public $showHistoryModal = false;
    public $selectedInstallment = null;
    public $paymentAmount = '';
    public $paymentDate = '';
    public $suggestedAmount = 0;

    public function openPaymentModal($installmentId)
    {
        try {
            $this->selectedInstallment = Installment::where('id', $installmentId)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            // Calcular monto sugerido
            $paymentService = new InstallmentPaymentService();
            $this->suggestedAmount = $paymentService->getSuggestedPaymentAmount($this->selectedInstallment);
            $this->paymentAmount = number_format($this->suggestedAmount / 100, 2, '.', '');
            $this->paymentDate = now()->format('Y-m-d');

            $this->showPaymentModal = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Error al abrir el modal de pago: ' . $e->getMessage());
        }
    }

    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
        $this->selectedInstallment = null;
        $this->paymentAmount = '';
        $this->paymentDate = '';
        $this->suggestedAmount = 0;
        $this->resetValidation();
    }

    public function processPayment()
    {
        $this->validate([
            'paymentAmount' => ['required', 'numeric', 'min:0.01'],
            'paymentDate' => ['required', 'date', 'before_or_equal:today'],
        ], [
            'paymentAmount.required' => 'El monto es obligatorio.',
            'paymentAmount.numeric' => 'El monto debe ser un número válido.',
            'paymentAmount.min' => 'El monto debe ser mayor a cero.',
            'paymentDate.required' => 'La fecha es obligatoria.',
            'paymentDate.date' => 'La fecha debe ser válida.',
            'paymentDate.before_or_equal' => 'La fecha no puede ser futura.',
        ]);

        try {
            $paymentService = new InstallmentPaymentService();

            // Convertir monto a centavos
            $amountInCents = (int)($this->paymentAmount * 100);

            // Registrar el pago
            $result = $paymentService->registerPayment(
                $this->selectedInstallment,
                $amountInCents,
                $this->paymentDate
            );

            // Mensaje de éxito personalizado según el tipo de pago
            $message = 'Pago registrado exitosamente. ';

            if ($result['is_completed']) {
                $message .= '¡Plan de cuotas completado!';
            } elseif ($result['payment_type'] === 'full_installment') {
                $message .= "Cuota(s) completa(s) pagada(s). Restante: S/ " . number_format($result['remaining_dollars'], 2);
            } elseif ($result['payment_type'] === 'partial') {
                $message .= "Pago parcial registrado. Restante: S/ " . number_format($result['remaining_dollars'], 2);
            }

            session()->flash('success', $message);

            $this->closePaymentModal();
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            $this->addError('paymentAmount', $e->getMessage());
        }
    }

    public function openHistoryModal($installmentId)
    {
        try {
            $this->selectedInstallment = Installment::where('id', $installmentId)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            $this->showHistoryModal = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Error al abrir el historial: ' . $e->getMessage());
        }
    }

    public function closeHistoryModal()
    {
        $this->showHistoryModal = false;
        $this->selectedInstallment = null;
    }

    public function deletePayment($paymentIndex)
    {
        try {
            $paymentService = new InstallmentPaymentService();
            $result = $paymentService->deletePayment($this->selectedInstallment, $paymentIndex);

            session()->flash('success', 'Pago eliminado exitosamente. Saldo actualizado.');

            // Recargar el installment
            $this->selectedInstallment = Installment::find($this->selectedInstallment->id);

            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar el pago: ' . $e->getMessage());
        }
    }

    public function render()
    {
        // DataTables maneja la paginación, búsqueda y filtrado
        // Solo mostrar cuotas con productos financieros válidos
        $installments = Installment::where('user_id', auth()->id())
            ->whereHas('financialProduct') // Solo cuotas con productos válidos
            ->with(['financialProduct'])
            ->orderBy('purchase_date', 'desc')
            ->get();

        // Calcular resumen de cuotas
        $summary = [
            'active_count' => $installments->where('status', Installment::STATUS_ACTIVE)->count(),
            'completed_count' => $installments->where('status', Installment::STATUS_COMPLETED)->count(),
            'active_amount' => $installments->where('status', Installment::STATUS_ACTIVE)->sum('total_amount'),
            'total_amount' => $installments->sum('total_amount'),
        ];

        return view('livewire.installments.installment-list', [
            'installments' => $installments,
            'summary' => $summary
        ]);
    }
}
