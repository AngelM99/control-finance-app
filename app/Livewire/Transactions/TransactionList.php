<?php

namespace App\Livewire\Transactions;

use App\Models\Transaction;
use App\Services\TransactionService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Transacciones - Control Finance')]
class TransactionList extends Component
{
    public function delete($transactionId)
    {
        try {
            $transaction = Transaction::where('id', $transactionId)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            $transactionService = new TransactionService();
            $transactionService->deleteTransaction($transaction);

            session()->flash('success', 'Transacción eliminada exitosamente.');

            // Refrescar el componente
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar la transacción: ' . $e->getMessage());
        }
    }

    public function render()
    {
        // DataTables maneja la paginación, búsqueda y filtrado
        // Solo cargamos todos los registros del usuario
        // Usamos whereHas para filtrar solo transacciones con productos existentes
        $transactions = Transaction::where('user_id', auth()->id())
            ->whereHas('financialProduct') // Solo transacciones con productos válidos
            ->with(['financialProduct', 'lender'])
            ->latest('transaction_date')
            ->get();

        return view('livewire.transactions.transaction-list', [
            'transactions' => $transactions
        ]);
    }
}
