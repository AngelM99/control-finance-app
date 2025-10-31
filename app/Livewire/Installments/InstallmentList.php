<?php

namespace App\Livewire\Installments;

use App\Models\Installment;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Cuotas - Control Finance')]
class InstallmentList extends Component
{
    public function markAsPaid($installmentId)
    {
        try {
            $installment = Installment::where('id', $installmentId)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            // Incrementar la cuota actual pagada
            $installment->increment('current_installment');

            // Si completó todas las cuotas, marcar como completado
            if ($installment->current_installment >= $installment->installment_count) {
                $installment->update(['status' => Installment::STATUS_COMPLETED]);
            }

            session()->flash('success', 'Cuota marcada como pagada exitosamente.');

            // Refrescar el componente
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al marcar la cuota como pagada: ' . $e->getMessage());
        }
    }

    public function render()
    {
        // DataTables maneja la paginación, búsqueda y filtrado
        $installments = Installment::where('user_id', auth()->id())
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
