<?php

namespace App\Livewire\Reports;

use App\Models\User;
use App\Models\Lender;
use App\Models\Transaction;
use App\Models\Installment;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

#[Layout('components.layouts.app')]
#[Title('Reporte de Transacciones - Control Finance')]
class TransactionReport extends Component
{
    public $selectedBorrower = '';
    public $dateFrom = '';
    public $dateTo = '';

    public $showInstallmentModal = false;
    public $selectedTransaction = null;
    public $installmentDetails = null;
    public $installmentSchedule = [];

    public function mount()
    {
        // Por defecto, sin filtros (mostrar todo)
        $this->selectedBorrower = '';
        $this->dateFrom = '';
        $this->dateTo = '';
    }

    public function applyFilters()
    {
        // Los filtros se aplican automáticamente en el render
        $this->dispatch('filtersApplied');
    }

    public function clearFilters()
    {
        $this->selectedBorrower = '';
        $this->dateFrom = '';
        $this->dateTo = '';
    }

    private function loadTransactionData($transactionId)
    {
        $this->selectedTransaction = Transaction::with(['financialProduct', 'lender'])
            ->where('id', $transactionId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Obtener el plan de cuotas asociado
        $this->installmentDetails = Installment::where('financial_product_id', $this->selectedTransaction->financial_product_id)
            ->where('purchase_date', $this->selectedTransaction->transaction_date)
            ->where('total_amount', $this->selectedTransaction->amount)
            ->with('financialProduct')
            ->first();

        if ($this->installmentDetails) {
            // Generar el cronograma detallado cuota por cuota
            $this->installmentSchedule = $this->generateInstallmentSchedule($this->installmentDetails);
        }
    }

    public function openInstallmentDetail($transactionId)
    {
        try {
            $this->loadTransactionData($transactionId);
            $this->showInstallmentModal = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Error al cargar el detalle: ' . $e->getMessage());
        }
    }

    public function closeInstallmentModal()
    {
        $this->showInstallmentModal = false;
        $this->selectedTransaction = null;
        $this->installmentDetails = null;
        $this->installmentSchedule = [];
    }

    private function generateInstallmentSchedule($installment)
    {
        $schedule = [];
        $installmentAmount = $installment->installment_amount;
        $totalInstallments = $installment->installment_count;
        $firstPaymentDate = $installment->first_payment_date;

        // Obtener historial de pagos
        $payments = $installment->payment_schedule['payments'] ?? [];
        $totalPaid = 0;
        $currentInstallmentPaid = 0;

        for ($i = 1; $i <= $totalInstallments; $i++) {
            // Calcular fecha programada (cada 30 días desde la primera cuota)
            $scheduledDate = $firstPaymentDate ?
                Carbon::parse($firstPaymentDate)->addMonths($i - 1) :
                Carbon::parse($installment->purchase_date)->addMonths($i);

            // Determinar cuánto se ha pagado de esta cuota específica
            $paidAmount = 0;
            $paymentDate = null;
            $remainingAfter = 0;

            // Calcular pagos aplicados a esta cuota
            if ($currentInstallmentPaid < $installmentAmount && count($payments) > 0) {
                foreach ($payments as $payment) {
                    if ($totalPaid < ($i * $installmentAmount)) {
                        $amountToApply = min(
                            $payment['amount'],
                            $installmentAmount - $currentInstallmentPaid
                        );

                        $paidAmount += $amountToApply;
                        $currentInstallmentPaid += $amountToApply;
                        $totalPaid += $amountToApply;

                        if (!$paymentDate) {
                            $paymentDate = $payment['payment_date'];
                        }

                        if ($currentInstallmentPaid >= $installmentAmount) {
                            $currentInstallmentPaid = 0;
                            break;
                        }
                    }
                }
            }

            $remainingAfter = $installment->total_amount - ($i * $installmentAmount) + ($installmentAmount - $paidAmount);

            // Determinar estado
            $status = 'pending';
            if ($paidAmount >= $installmentAmount) {
                $status = 'paid';
            } elseif ($paidAmount > 0) {
                $status = 'partial';
            } elseif (Carbon::parse($scheduledDate)->isPast()) {
                $status = 'overdue';
            }

            $schedule[] = [
                'number' => $i,
                'total' => $totalInstallments,
                'scheduled_date' => $scheduledDate,
                'installment_amount' => $installmentAmount,
                'paid_amount' => $paidAmount,
                'payment_date' => $paymentDate,
                'remaining_balance' => max(0, $installment->total_amount - ($totalPaid)),
                'status' => $status,
            ];
        }

        return $schedule;
    }

    public function exportTransactionPdf($transactionId)
    {
        try {
            $this->loadTransactionData($transactionId);

            $data = [
                'transaction' => $this->selectedTransaction,
                'installment' => $this->installmentDetails,
                'schedule' => $this->installmentSchedule,
                'generatedAt' => now()->format('d/m/Y H:i'),
            ];

            $pdf = Pdf::loadView('reports.transaction-detail-pdf', $data);

            return response()->streamDownload(function() use ($pdf) {
                echo $pdf->output();
            }, 'transaccion-' . $this->selectedTransaction->id . '-' . now()->format('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al exportar PDF: ' . $e->getMessage());
            return redirect()->route('reports.transactions');
        }
    }

    public function exportFullReportPdf()
    {
        $transactions = $this->getFilteredTransactions();

        $data = [
            'transactions' => $transactions,
            'filters' => [
                'borrower' => $this->selectedBorrower ? Lender::find($this->selectedBorrower)->full_name : 'Todos',
                'dateFrom' => $this->dateFrom ?: 'Sin límite',
                'dateTo' => $this->dateTo ?: 'Sin límite',
            ],
            'user' => auth()->user(),
            'generatedAt' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('reports.transaction-report-pdf', $data)
            ->setPaper('a4', 'landscape');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'reporte-transacciones-' . now()->format('Y-m-d') . '.pdf');
    }

    private function getFilteredTransactions()
    {
        $query = Transaction::where('user_id', auth()->id())
            ->whereHas('financialProduct')
            ->with(['financialProduct', 'lender']);

        // Filtro por prestamista
        if ($this->selectedBorrower) {
            $query->where('lender_id', $this->selectedBorrower);
        }

        // Filtro por rango de fechas
        if ($this->dateFrom) {
            $query->whereDate('transaction_date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('transaction_date', '<=', $this->dateTo);
        }

        return $query->orderBy('transaction_date', 'desc')->get();
    }

    private function calculateInstallmentProgress($transaction)
    {
        // Buscar el plan de cuotas asociado
        $installment = Installment::where('financial_product_id', $transaction->financial_product_id)
            ->where('purchase_date', $transaction->transaction_date)
            ->where('total_amount', $transaction->amount)
            ->first();

        // Si no tiene plan de cuotas asociado, es una transacción sin cuotas
        if (!$installment) {
            return [
                'paid' => 0,
                'total' => 0,
                'percentage' => 0,
                'status' => 'sin_cuotas'
            ];
        }

        $totalPaid = $installment->total_paid ?? 0;
        $installmentAmount = $installment->installment_amount;
        $totalInstallments = $installment->installment_count;

        // Calcular cuántas cuotas completas se han pagado usando el método centralizado
        // Esto garantiza consistencia con el resto de la aplicación y tolera errores de redondeo
        $paidInstallments = Installment::calculateCompletedInstallments($totalPaid, $installmentAmount);

        $percentage = $totalInstallments > 0 ? round(($paidInstallments / $totalInstallments) * 100) : 0;

        // Determinar estado
        $status = 'pendiente';
        if ($paidInstallments >= $totalInstallments) {
            $status = 'pagado';
        } elseif ($paidInstallments > 0) {
            $status = 'en_progreso';
        }

        return [
            'paid' => $paidInstallments,
            'total' => $totalInstallments,
            'percentage' => $percentage,
            'status' => $status
        ];
    }

    public function render()
    {
        // Obtener lista de prestamistas registrados del usuario autenticado
        $borrowers = Lender::where('user_id', auth()->id())
            ->where('is_active', true)
            ->orderBy('full_name')
            ->get();

        // Obtener transacciones filtradas
        $transactions = $this->getFilteredTransactions();

        // Agregar información de progreso de cuotas a cada transacción
        $transactions->each(function ($transaction) {
            $transaction->installment_progress = $this->calculateInstallmentProgress($transaction);
        });

        // Calcular estadísticas
        $stats = [
            'total_transactions' => $transactions->count(),
            'total_amount' => $transactions->sum('amount'),
            'with_installments' => $transactions->filter(function ($transaction) {
                return $transaction->installment_progress['total'] > 0;
            })->count(),
        ];

        return view('livewire.reports.transaction-report', [
            'borrowers' => $borrowers,
            'transactions' => $transactions,
            'stats' => $stats,
        ]);
    }
}
