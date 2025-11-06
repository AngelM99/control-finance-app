<?php

namespace App\Livewire\Reports;

use App\Models\User;
use App\Models\Transaction;
use App\Models\Installment;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;

#[Layout('components.layouts.app')]
#[Title('Reportes de Prestamistas - Control Finance')]
class BorrowerReport extends Component
{
    public $showDetailModal = false;
    public $selectedBorrower = null;
    public $borrowerTransactions = [];
    public $borrowerInstallments = [];
    public $borrowerStats = [];

    public function openDetailModal($borrowerId)
    {
        try {
            $this->selectedBorrower = User::findOrFail($borrowerId);

            // Obtener todas las transacciones del prestamista
            $this->borrowerTransactions = Transaction::where('lender_id', $borrowerId)
                ->whereHas('financialProduct')
                ->with(['financialProduct'])
                ->orderBy('transaction_date', 'desc')
                ->get();

            // Obtener todas las cuotas del prestamista
            $this->borrowerInstallments = Installment::whereHas('financialProduct', function($query) use ($borrowerId) {
                $query->whereHas('transactions', function($q) use ($borrowerId) {
                    $q->where('lender_id', $borrowerId);
                });
            })
            ->with(['financialProduct'])
            ->orderBy('purchase_date', 'desc')
            ->get();

            // Calcular estadísticas del prestamista
            $totalLent = $this->borrowerTransactions->sum('amount');
            $totalPaid = $this->borrowerInstallments->sum('total_paid');
            $totalDebt = $this->borrowerInstallments->where('status', Installment::STATUS_ACTIVE)->sum('total_amount');
            $remainingDebt = $this->borrowerInstallments->where('status', Installment::STATUS_ACTIVE)->sum(function($inst) {
                return $inst->remaining_amount;
            });

            $this->borrowerStats = [
                'total_lent' => $totalLent,
                'total_paid' => $totalPaid,
                'total_debt' => $totalDebt,
                'remaining_debt' => $remainingDebt,
                'active_installments' => $this->borrowerInstallments->where('status', Installment::STATUS_ACTIVE)->count(),
                'completed_installments' => $this->borrowerInstallments->where('status', Installment::STATUS_COMPLETED)->count(),
                'progress_percentage' => $totalDebt > 0 ? ($totalPaid / $totalDebt) * 100 : 0,
            ];

            $this->showDetailModal = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Error al cargar el detalle: ' . $e->getMessage());
        }
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedBorrower = null;
        $this->borrowerTransactions = [];
        $this->borrowerInstallments = [];
        $this->borrowerStats = [];
    }

    public function render()
    {
        // Obtener todos los prestamistas que tienen transacciones
        $borrowers = User::whereHas('lentTransactions')
            ->withCount([
                'lentTransactions as total_transactions',
            ])
            ->with(['lentTransactions' => function($query) {
                $query->whereHas('financialProduct');
            }])
            ->get()
            ->map(function($borrower) {
                // Calcular estadísticas por prestamista
                $transactions = Transaction::where('lender_id', $borrower->id)
                    ->whereHas('financialProduct')
                    ->get();

                $totalLent = $transactions->sum('amount');

                // Obtener cuotas relacionadas a este prestamista
                $installments = Installment::whereHas('financialProduct', function($query) use ($borrower) {
                    $query->whereHas('transactions', function($q) use ($borrower) {
                        $q->where('lender_id', $borrower->id);
                    });
                })->get();

                $totalPaid = $installments->sum('total_paid');
                $activeInstallments = $installments->where('status', Installment::STATUS_ACTIVE);
                $completedInstallments = $installments->where('status', Installment::STATUS_COMPLETED);

                $remainingDebt = $activeInstallments->sum(function($inst) {
                    return $inst->remaining_amount;
                });

                // Próximo vencimiento
                $nextPayment = $activeInstallments->sortBy('first_payment_date')->first();

                // Determinar estado
                $status = 'completed';
                if ($activeInstallments->count() > 0) {
                    $status = 'active';
                }

                return [
                    'id' => $borrower->id,
                    'name' => $borrower->name,
                    'email' => $borrower->email,
                    'total_lent' => $totalLent,
                    'total_paid' => $totalPaid,
                    'remaining_debt' => $remainingDebt,
                    'active_installments_count' => $activeInstallments->count(),
                    'completed_installments_count' => $completedInstallments->count(),
                    'next_payment_date' => $nextPayment?->first_payment_date,
                    'status' => $status,
                ];
            });

        // Calcular métricas globales
        $globalStats = [
            'total_borrowers' => $borrowers->count(),
            'total_debt' => $borrowers->sum('remaining_debt'),
            'total_lent' => $borrowers->sum('total_lent'),
            'total_paid' => $borrowers->sum('total_paid'),
        ];

        return view('livewire.reports.borrower-report', [
            'borrowers' => $borrowers,
            'globalStats' => $globalStats,
        ]);
    }
}
