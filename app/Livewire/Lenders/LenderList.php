<?php

namespace App\Livewire\Lenders;

use App\Models\Lender;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Prestamistas - Control Finance')]
class LenderList extends Component
{
    public function deleteLender($lenderId)
    {
        try {
            $lender = Lender::where('id', $lenderId)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            $lender->delete();

            session()->flash('success', 'Prestamista eliminado exitosamente.');

            // Refrescar el componente
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar el prestamista: ' . $e->getMessage());
        }
    }

    public function toggleStatus($lenderId)
    {
        try {
            $lender = Lender::where('id', $lenderId)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            $lender->update(['is_active' => !$lender->is_active]);

            session()->flash('success', 'Estado del prestamista actualizado.');

            // Refrescar el componente
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al actualizar el estado: ' . $e->getMessage());
        }
    }

    public function render()
    {
        // DataTables maneja la paginación, búsqueda y filtrado
        $lenders = Lender::where('user_id', auth()->id())
            ->withCount('transactions')
            ->latest()
            ->get();

        return view('livewire.lenders.lender-list', [
            'lenders' => $lenders
        ]);
    }
}
