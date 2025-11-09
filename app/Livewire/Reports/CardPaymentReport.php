<?php

namespace App\Livewire\Reports;

use App\Models\FinancialProduct;
use App\Models\Lender;
use App\Models\Transaction;
use App\Models\Installment;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

#[Layout('components.layouts.app')]
#[Title('Reporte de Pagos por Tarjeta - Control Finance')]
class CardPaymentReport extends Component
{
    public $selectedCard = '';
    public $selectedMonth = '';
    public $selectedYear = '';

    public $searchExecuted = false;
    public $reportData = [];

    public function mount()
    {
        // Inicializar con el mes y año actual
        $this->selectedMonth = now()->format('m');
        $this->selectedYear = now()->format('Y');
        $this->selectedCard = '';
        $this->searchExecuted = false;
    }

    public function generateReport()
    {
        // Validar que los campos obligatorios estén completos
        $this->validate([
            'selectedCard' => 'required',
            'selectedMonth' => 'required',
            'selectedYear' => 'required',
        ], [
            'selectedCard.required' => 'Debe seleccionar una tarjeta',
            'selectedMonth.required' => 'Debe seleccionar un mes',
            'selectedYear.required' => 'Debe seleccionar un año',
        ]);

        $this->searchExecuted = true;
        $this->reportData = $this->getCardPaymentData();
    }

    public function clearSearch()
    {
        $this->selectedCard = '';
        $this->selectedMonth = now()->format('m');
        $this->selectedYear = now()->format('Y');
        $this->searchExecuted = false;
        $this->reportData = [];
    }

    private function getCardPaymentData()
    {
        // Crear fecha de inicio y fin del período
        $startDate = Carbon::create($this->selectedYear, $this->selectedMonth, 1)->startOfMonth();
        $endDate = Carbon::create($this->selectedYear, $this->selectedMonth, 1)->endOfMonth();

        // Obtener la tarjeta seleccionada
        $card = FinancialProduct::find($this->selectedCard);

        // Obtener todos los installments activos de esta tarjeta
        $installments = Installment::where('installments.user_id', auth()->id())
            ->where('installments.financial_product_id', $this->selectedCard)
            ->whereIn('installments.status', [Installment::STATUS_ACTIVE, Installment::STATUS_COMPLETED])
            ->with(['financialProduct'])
            ->get();

        // Calcular cuotas que vencen en el período, agrupadas por prestamista
        $paymentsByLender = [];
        $totalDueAmount = 0;
        $totalDuePending = 0;
        $totalDuePaid = 0;
        $totalInstallmentsCount = 0;

        foreach ($installments as $installment) {
            $dueInstallments = $this->getInstallmentsDueInPeriod($installment, $startDate, $endDate);

            if (!empty($dueInstallments)) {
                // Obtener el prestamista de la transacción original
                // Intentar primero con descripción exacta
                $transaction = Transaction::where('financial_product_id', $installment->financial_product_id)
                    ->where('transaction_date', $installment->purchase_date)
                    ->where('description', $installment->description)
                    ->first();

                // Si no se encuentra, intentar solo con fecha y producto
                if (!$transaction) {
                    $transaction = Transaction::where('financial_product_id', $installment->financial_product_id)
                        ->where('transaction_date', $installment->purchase_date)
                        ->where('transaction_type', 'purchase')
                        ->first();
                }

                // Si aún no se encuentra, buscar la primera transacción del installment
                if (!$transaction) {
                    $transaction = Transaction::where('financial_product_id', $installment->financial_product_id)
                        ->where('is_installment', true)
                        ->where('installment_id', $installment->id)
                        ->first();
                }

                $lenderId = $transaction ? $transaction->lender_id : null;
                $lenderName = 'Sin Prestamista';
                $lenderObject = null;

                if ($lenderId) {
                    $lenderObject = Lender::find($lenderId);
                    $lenderName = $lenderObject ? $lenderObject->full_name : 'Prestamista Desconocido';
                }

                // Agrupar por prestamista
                if (!isset($paymentsByLender[$lenderName])) {
                    $paymentsByLender[$lenderName] = [
                        'lender_id' => $lenderId,
                        'lender_name' => $lenderName,
                        'lender' => $lenderObject,
                        'products' => [],
                        'total_due' => 0,
                        'total_paid' => 0,
                        'total_pending' => 0,
                    ];
                }

                // Agregar producto al prestamista
                $productData = [
                    'installment' => $installment,
                    'due_installments' => $dueInstallments,
                    'total_due' => collect($dueInstallments)->sum('installment_amount'),
                ];

                $paymentsByLender[$lenderName]['products'][] = $productData;
                $paymentsByLender[$lenderName]['total_due'] += $productData['total_due'];

                // Sumar al total general
                $totalDueAmount += $productData['total_due'];
                $totalInstallmentsCount += count($dueInstallments);

                // Separar pagadas y pendientes
                foreach ($dueInstallments as $due) {
                    if ($due['is_paid']) {
                        $paymentsByLender[$lenderName]['total_paid'] += $due['installment_amount'];
                        $totalDuePaid += $due['installment_amount'];
                    } else {
                        $paymentsByLender[$lenderName]['total_pending'] += $due['installment_amount'];
                        $totalDuePending += $due['installment_amount'];
                    }
                }
            }
        }

        return [
            'card' => $card,
            'period' => [
                'month' => $this->selectedMonth,
                'year' => $this->selectedYear,
                'month_name' => Carbon::create($this->selectedYear, $this->selectedMonth, 1)->locale('es')->monthName,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'payments_by_lender' => $paymentsByLender,
            'summary' => [
                'total_due_amount' => $totalDueAmount,
                'total_due_pending' => $totalDuePending,
                'total_due_paid' => $totalDuePaid,
                'installments_count' => $totalInstallmentsCount,
                'lenders_count' => count($paymentsByLender),
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

        // Usar el accessor del modelo que tiene tolerancia para errores de redondeo
        $paidInstallments = $installment->current_installment;

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
                    // Buscar el pago que corresponde a esta cuota
                    $relatedPayments = [];
                    foreach ($payments as $payment) {
                        $paymentDate = Carbon::parse($payment['payment_date']);
                        // Considerar pagos realizados cerca de esta cuota (±45 días)
                        if (abs($paymentDate->diffInDays($dueDate)) <= 45) {
                            $relatedPayments[] = $payment;
                        }
                    }

                    // Si encontramos pagos relacionados, tomar el más cercano
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

        $pdf = Pdf::loadView('reports.card-payment-pdf', $data)
            ->setPaper('a4', 'portrait');

        $filename = 'reporte-tarjeta-' .
                    str_replace(' ', '-', $this->reportData['card']->name) . '-' .
                    $this->selectedMonth . '-' .
                    $this->selectedYear . '.pdf';

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }

    public function render()
    {
        // Obtener lista de tarjetas del usuario autenticado
        $cards = FinancialProduct::where('user_id', auth()->id())
            ->whereIn('product_type', ['credit_card', 'debit_card', 'credit_line'])
            ->where('is_active', true)
            ->orderBy('name')
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

        return view('livewire.reports.card-payment-report', [
            'cards' => $cards,
            'months' => $months,
        ]);
    }
}
