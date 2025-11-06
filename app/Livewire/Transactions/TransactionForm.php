<?php

namespace App\Livewire\Transactions;

use App\Models\Transaction;
use App\Models\FinancialProduct;
use App\Services\TransactionService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Nueva Transacción - Control Finance')]
class TransactionForm extends Component
{
    public ?Transaction $transaction = null;

    public $financial_product_id = '';
    public $lender_id = ''; // Prestamista
    public $transaction_type = '';
    public $amount = '';
    public $transaction_date = '';
    public $description = '';
    public $reference_number = '';
    public $merchant = '';
    public $installments_count = 3; // Valor por defecto: 3 cuotas
    public $pay_in_installments = false;

    public function mount($transaction = null)
    {
        $this->transaction_date = now()->format('Y-m-d');

        if ($transaction) {
            // Si $transaction es un objeto Transaction, usarlo directamente
            // Si es un ID, buscarlo en la base de datos
            if ($transaction instanceof Transaction) {
                $this->transaction = $transaction;
                // Verificar que la transacción pertenece al usuario actual
                if ($this->transaction->user_id !== auth()->id()) {
                    abort(403, 'No tienes permiso para editar esta transacción.');
                }
            } else {
                $this->transaction = Transaction::where('id', $transaction)
                    ->where('user_id', auth()->id())
                    ->firstOrFail();
            }

            $this->financial_product_id = $this->transaction->financial_product_id;
            $this->lender_id = $this->transaction->lender_id ?? '';
            $this->transaction_type = $this->transaction->transaction_type;
            $this->amount = $this->transaction->amount / 100;
            $this->transaction_date = $this->transaction->transaction_date->format('Y-m-d');
            $this->description = $this->transaction->description ?? '';
            $this->reference_number = $this->transaction->reference_number ?? '';
            $this->merchant = $this->transaction->merchant ?? '';
        }
    }

    protected function rules()
    {
        $rules = [
            'financial_product_id' => ['required', 'exists:financial_products,id'],
            'lender_id' => ['nullable', 'exists:lenders,id'],
            'transaction_type' => ['required', 'in:purchase,payment,transfer,withdrawal,deposit,refund,adjustment'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transaction_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:500'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'merchant' => ['nullable', 'string', 'max:255'],
            'pay_in_installments' => ['boolean'],
        ];

        // Solo validar installments_count si pay_in_installments está activo
        if ($this->pay_in_installments && $this->transaction_type === 'purchase') {
            $rules['installments_count'] = ['required', 'integer', 'min:2', 'max:60'];
        }

        return $rules;
    }

    public function save()
    {
        try {
            $validated = $this->validate();

            // Convertir datetime-local a solo fecha si viene con hora
            if (str_contains($validated['transaction_date'], 'T')) {
                $validated['transaction_date'] = explode('T', $validated['transaction_date'])[0];
            }

            $validated['amount'] = (int)($validated['amount'] * 100);
            $validated['user_id'] = auth()->id();

            // Si no se seleccionó prestamista, dejar como null
            // Usar isset y comparación estricta para evitar problemas con valores "0" o false
            if (!isset($validated['lender_id']) || $validated['lender_id'] === '' || $validated['lender_id'] === null) {
                $validated['lender_id'] = null;
            } else {
                // Asegurar que sea un entero
                $validated['lender_id'] = (int) $validated['lender_id'];
            }

            // Remover pay_in_installments de los datos validados ya que no es parte del modelo
            unset($validated['pay_in_installments']);

            // Si es en cuotas y el tipo es purchase, agregar installments_count
            if ($this->pay_in_installments && $this->transaction_type === 'purchase' && $this->installments_count > 1) {
                $validated['installments_count'] = $this->installments_count;
            }

            $transactionService = new TransactionService();

            if ($this->transaction) {
                $transactionService->updateTransaction($this->transaction, $validated);
                session()->flash('success', 'Transacción actualizada exitosamente.');
            } else {
                $result = $transactionService->createTransaction($validated);

                $message = 'Transacción creada exitosamente.';

                // Mensaje específico para compras con cuotas
                if ($this->transaction_type === 'purchase') {
                    if ($this->pay_in_installments && $this->installments_count > 1) {
                        $message .= " Plan de {$this->installments_count} cuotas creado automáticamente.";
                    } else {
                        $message .= " Se creó como pago único en el próximo ciclo de facturación.";
                    }
                }

                session()->flash('success', $message);
            }

            return $this->redirect(route('transactions.index'), navigate: true);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e; // Re-lanzar para que Livewire lo maneje
        } catch (\Exception $e) {
            // Agregar el error para mostrarlo en la UI
            $this->addError('general', $e->getMessage());
        }
    }

    public function render()
    {
        $products = FinancialProduct::where('user_id', auth()->id())
            ->where('is_active', true)
            ->get();

        $lenders = \App\Models\Lender::where('user_id', auth()->id())
            ->where('is_active', true)
            ->orderBy('full_name')
            ->get();

        return view('livewire.transactions.transaction-form', [
            'products' => $products,
            'lenders' => $lenders
        ]);
    }
}
