<?php

namespace App\Livewire\Reports;

use App\Models\User;
use App\Models\Transaction;
use App\Models\Installment;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Barryvdh\DomPDF\Facade\Pdf;

#[Layout('components.layouts.app')]
#[Title('Mis Prestamistas - Control Finance')]
class MyBorrowers extends Component
{
    public $showDetailModal = false;
    public $selectedBorrower = null;
    public $borrowerTransactions = [];
    public $borrowerInstallments = [];
    public $borrowerStats = [];
    public $borrowerPayments = [];

    public function openDetailModal($borrowerId)
    {
        try {
            $this->selectedBorrower = User::findOrFail($borrowerId);

            // Obtener transacciones del prestamista usando MIS productos financieros
            $this->borrowerTransactions = Transaction::where('lender_id', $borrowerId)
                ->whereHas('financialProduct', function($query) {
                    $query->where('user_id', auth()->id());
                })
                ->with(['financialProduct'])
                ->orderBy('transaction_date', 'desc')
                ->get();

            // Obtener cuotas relacionadas
            $this->borrowerInstallments = Installment::whereIn('id', function($query) use ($borrowerId) {
                $query->select('installments.id')
                    ->from('installments')
                    ->join('transactions', 'transactions.financial_product_id', '=', 'installments.financial_product_id')
                    ->where('transactions.lender_id', $borrowerId)
                    ->whereExists(function($subQuery) {
                        $subQuery->select('id')
                            ->from('financial_products')
                            ->whereColumn('financial_products.id', 'installments.financial_product_id')
                            ->where('financial_products.user_id', auth()->id());
                    });
            })
            ->with(['financialProduct'])
            ->orderBy('purchase_date', 'desc')
            ->get();

            // Calcular estadísticas
            $totalLent = $this->borrowerTransactions->sum('amount');
            $totalPaid = $this->borrowerInstallments->sum('total_paid');
            $activeInstallments = $this->borrowerInstallments->where('status', Installment::STATUS_ACTIVE);
            $remainingDebt = $activeInstallments->sum(function($inst) {
                return $inst->remaining_amount;
            });

            // Extraer historial de pagos de todas las cuotas
            $this->borrowerPayments = [];
            foreach ($this->borrowerInstallments as $installment) {
                if (isset($installment->payment_schedule['payments'])) {
                    foreach ($installment->payment_schedule['payments'] as $payment) {
                        $this->borrowerPayments[] = [
                            'installment' => $installment,
                            'payment_date' => $payment['payment_date'],
                            'amount' => $payment['amount'],
                            'amount_dollars' => $payment['amount_dollars'],
                            'remaining_after' => $payment['remaining_after_dollars'] ?? 0,
                            'registered_at' => $payment['registered_at'] ?? null,
                        ];
                    }
                }
            }

            // Ordenar pagos por fecha descendente
            usort($this->borrowerPayments, function($a, $b) {
                return strtotime($b['payment_date']) - strtotime($a['payment_date']);
            });

            $this->borrowerStats = [
                'total_lent' => $totalLent,
                'total_paid' => $totalPaid,
                'remaining_debt' => $remainingDebt,
                'active_installments' => $activeInstallments->count(),
                'completed_installments' => $this->borrowerInstallments->where('status', Installment::STATUS_COMPLETED)->count(),
                'progress_percentage' => $totalLent > 0 ? ($totalPaid / $totalLent) * 100 : 0,
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
        $this->borrowerPayments = [];
    }

    public function exportBorrowerPdf($borrowerId)
    {
        // Cargar datos del prestamista
        $this->openDetailModal($borrowerId);

        $data = [
            'borrower' => $this->selectedBorrower,
            'stats' => $this->borrowerStats,
            'transactions' => $this->borrowerTransactions,
            'installments' => $this->borrowerInstallments,
            'payments' => $this->borrowerPayments,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('reports.borrower-detail-pdf', $data);

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'prestamista-' . $this->selectedBorrower->name . '-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportFullReport()
    {
        $borrowers = $this->getBorrowersData();
        $globalStats = $this->getGlobalStats($borrowers);

        $data = [
            'borrowers' => $borrowers,
            'globalStats' => $globalStats,
            'user' => auth()->user(),
            'generatedAt' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('reports.my-borrowers-full-pdf', $data)
            ->setPaper('a4', 'landscape');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'reporte-mis-prestamistas-' . now()->format('Y-m-d') . '.pdf');
    }

    private function getBorrowersData()
    {
        // Obtener prestamistas que usaron MIS productos
        return User::whereHas('lentTransactions', function($query) {
            $query->whereHas('financialProduct', function($q) {
                $q->where('user_id', auth()->id());
            });
        })
        ->get()
        ->map(function($borrower) {
            // Transacciones usando MIS productos
            $transactions = Transaction::where('lender_id', $borrower->id)
                ->whereHas('financialProduct', function($query) {
                    $query->where('user_id', auth()->id());
                })
                ->get();

            $totalLent = $transactions->sum('amount');

            // Cuotas de transacciones que usaron MIS productos
            $installments = Installment::whereIn('id', function($query) use ($borrower) {
                $query->select('installments.id')
                    ->from('installments')
                    ->join('transactions', 'transactions.financial_product_id', '=', 'installments.financial_product_id')
                    ->where('transactions.lender_id', $borrower->id)
                    ->whereExists(function($subQuery) {
                        $subQuery->select('id')
                            ->from('financial_products')
                            ->whereColumn('financial_products.id', 'installments.financial_product_id')
                            ->where('financial_products.user_id', auth()->id());
                    });
            })->get();

            $totalPaid = $installments->sum('total_paid');
            $activeInstallments = $installments->where('status', Installment::STATUS_ACTIVE);
            $completedInstallments = $installments->where('status', Installment::STATUS_COMPLETED);

            $remainingDebt = $activeInstallments->sum(function($inst) {
                return $inst->remaining_amount;
            });

            $nextPayment = $activeInstallments->sortBy('first_payment_date')->first();

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
    }

    private function getGlobalStats($borrowers)
    {
        return [
            'total_borrowers' => $borrowers->count(),
            'total_debt' => $borrowers->sum('remaining_debt'),
            'total_lent' => $borrowers->sum('total_lent'),
            'total_paid' => $borrowers->sum('total_paid'),
        ];
    }

    public function render()
    {
        $borrowers = $this->getBorrowersData();
        $globalStats = $this->getGlobalStats($borrowers);

        return view('livewire.reports.my-borrowers', [
            'borrowers' => $borrowers,
            'globalStats' => $globalStats,
        ]);
    }
}
