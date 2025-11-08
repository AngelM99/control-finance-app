<?php

namespace App\Livewire\Reports;

use App\Models\Lender;
use App\Models\Transaction;
use App\Models\Installment;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

#[Layout('components.layouts.app')]
#[Title('Estado de Cuenta - Control Finance')]
class AccountStatementReport extends Component
{
    public $selectedLender = '';
    public $selectedMonth = '';
    public $selectedYear = '';

    public $searchExecuted = false;
    public $reportData = [];

    public function mount()
    {
        // Inicializar con el mes y año actual
        $this->selectedMonth = now()->format('m');
        $this->selectedYear = now()->format('Y');
        $this->selectedLender = '';
        $this->searchExecuted = false;
    }

    public function generateReport()
    {
        // Validar que los campos obligatorios estén completos
        $this->validate([
            'selectedLender' => 'required',
            'selectedMonth' => 'required',
            'selectedYear' => 'required',
        ], [
            'selectedLender.required' => 'Debe seleccionar un prestamista',
            'selectedMonth.required' => 'Debe seleccionar un mes',
            'selectedYear.required' => 'Debe seleccionar un año',
        ]);

        $this->searchExecuted = true;
        $this->reportData = $this->getAccountStatementData();
    }

    public function clearSearch()
    {
        $this->selectedLender = '';
        $this->selectedMonth = now()->format('m');
        $this->selectedYear = now()->format('Y');
        $this->searchExecuted = false;
        $this->reportData = [];
    }

    private function getAccountStatementData()
    {
        // Crear fecha de inicio y fin del período
        $startDate = Carbon::create($this->selectedYear, $this->selectedMonth, 1)->startOfMonth();
        $endDate = Carbon::create($this->selectedYear, $this->selectedMonth, 1)->endOfMonth();

        // QUERY DIRECTO: Obtener installments que pertenecen a transacciones del prestamista seleccionado
        $installments = Installment::where('installments.user_id', auth()->id())
            ->where('installments.status', Installment::STATUS_ACTIVE)
            ->join('transactions', function($join) {
                $join->on('installments.financial_product_id', '=', 'transactions.financial_product_id')
                     ->on('installments.purchase_date', '=', 'transactions.transaction_date')
                     ->on('installments.description', '=', 'transactions.description');
            })
            ->where('transactions.user_id', auth()->id())
            ->where('transactions.lender_id', $this->selectedLender)
            ->select('installments.*')
            ->distinct()
            ->with('financialProduct')
            ->get();

        // Calcular cuotas que vencen en el período
        $installmentsDueInPeriod = [];
        $totalDueAmount = 0;
        $totalDuePending = 0;
        $totalDuePaid = 0;

        foreach ($installments as $installment) {
            $dueInstallments = $this->getInstallmentsDueInPeriod($installment, $startDate, $endDate);

            if (!empty($dueInstallments)) {
                $installmentsDueInPeriod[] = [
                    'installment' => $installment,
                    'due_installments' => $dueInstallments,
                    'total_due' => collect($dueInstallments)->sum('installment_amount'),
                ];

                // Sumar al total
                $totalDueAmount += collect($dueInstallments)->sum('installment_amount');

                // Separar pagadas y pendientes
                foreach ($dueInstallments as $due) {
                    if ($due['is_paid']) {
                        $totalDuePaid += $due['installment_amount'];
                    } else {
                        $totalDuePending += $due['installment_amount'];
                    }
                }
            }
        }

        return [
            'lender' => Lender::find($this->selectedLender),
            'period' => [
                'month' => $this->selectedMonth,
                'year' => $this->selectedYear,
                'month_name' => Carbon::create($this->selectedYear, $this->selectedMonth, 1)->locale('es')->monthName,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'installments_due' => $installmentsDueInPeriod,
            'summary' => [
                'total_due_amount' => $totalDueAmount,
                'total_due_pending' => $totalDuePending,
                'total_due_paid' => $totalDuePaid,
                'installments_count' => count($installmentsDueInPeriod),
            ],
        ];
    }

    private function getInstallmentsDueInPeriod($installment, $startDate, $endDate)
    {
        $dueInstallments = [];
        $firstPaymentDate = $installment->first_payment_date;
        $totalInstallments = $installment->installment_count;
        $installmentAmount = $installment->installment_amount;

        // Obtener historial de pagos
        $payments = $installment->payment_schedule['payments'] ?? [];
        $totalPaid = $installment->total_paid ?? 0;

        // Calcular cuántas cuotas se han pagado completamente
        $paidInstallments = $installmentAmount > 0 ? floor($totalPaid / $installmentAmount) : 0;

        for ($i = 1; $i <= $totalInstallments; $i++) {
            // Calcular fecha de vencimiento
            $dueDate = $firstPaymentDate ?
                Carbon::parse($firstPaymentDate)->addMonths($i - 1) :
                Carbon::parse($installment->purchase_date)->addMonths($i);

            // Si la fecha de vencimiento está en el período
            if ($dueDate->between($startDate, $endDate)) {
                // Verificar si esta cuota ya fue pagada
                $isPaid = $i <= $paidInstallments;

                // Buscar información de pagos relacionados a esta cuota
                $paymentInfo = null;
                if ($isPaid && !empty($payments)) {
                    // Buscar el pago que corresponde a esta cuota (aproximadamente)
                    // Si hay múltiples pagos, buscar el que está cerca de la fecha de vencimiento
                    $relatedPayments = [];
                    foreach ($payments as $payment) {
                        $paymentDate = Carbon::parse($payment['payment_date']);
                        // Considerar pagos realizados cerca de esta cuota (±45 días de la fecha de vencimiento)
                        if (abs($paymentDate->diffInDays($dueDate)) <= 45) {
                            $relatedPayments[] = $payment;
                        }
                    }

                    // Si encontramos pagos relacionados, tomar el más cercano a la fecha de vencimiento
                    if (!empty($relatedPayments)) {
                        usort($relatedPayments, function($a, $b) use ($dueDate) {
                            $diffA = abs(Carbon::parse($a['payment_date'])->diffInDays($dueDate));
                            $diffB = abs(Carbon::parse($b['payment_date'])->diffInDays($dueDate));
                            return $diffA - $diffB;
                        });
                        $paymentInfo = $relatedPayments[0];
                    }
                }

                $dueInstallments[] = [
                    'number' => $i,
                    'total' => $totalInstallments,
                    'due_date' => $dueDate,
                    'installment_amount' => $installmentAmount,
                    'is_paid' => $isPaid,
                    'status' => $isPaid ? 'paid' : ($dueDate->isPast() ? 'overdue' : 'pending'),
                    'payment_date' => $paymentInfo ? Carbon::parse($paymentInfo['payment_date']) : null,
                    'payment_amount' => $paymentInfo ? $paymentInfo['amount'] : null,
                ];
            }
        }

        return $dueInstallments;
    }

    public function exportPdf()
    {
        if (!$this->searchExecuted || empty($this->reportData)) {
            session()->flash('error', 'Debe generar el reporte primero antes de exportar.');
            return;
        }

        $data = [
            'reportData' => $this->reportData,
            'user' => auth()->user(),
            'generatedAt' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('reports.account-statement-pdf', $data)
            ->setPaper('a4', 'portrait');

        $filename = 'estado-cuenta-' .
                    $this->reportData['lender']->full_name . '-' .
                    $this->selectedMonth . '-' .
                    $this->selectedYear . '.pdf';

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }

    public function render()
    {
        // Obtener lista de prestamistas registrados del usuario autenticado
        $lenders = Lender::where('user_id', auth()->id())
            ->where('is_active', true)
            ->orderBy('full_name')
            ->get();

        // Meses del año
        $months = [
            '01' => 'Enero',
            '02' => 'Febrero',
            '03' => 'Marzo',
            '04' => 'Abril',
            '05' => 'Mayo',
            '06' => 'Junio',
            '07' => 'Julio',
            '08' => 'Agosto',
            '09' => 'Septiembre',
            '10' => 'Octubre',
            '11' => 'Noviembre',
            '12' => 'Diciembre',
        ];

        return view('livewire.reports.account-statement-report', [
            'lenders' => $lenders,
            'months' => $months,
        ]);
    }
}
