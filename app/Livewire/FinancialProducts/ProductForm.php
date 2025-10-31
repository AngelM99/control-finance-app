<?php

namespace App\Livewire\FinancialProducts;

use App\Models\FinancialProduct;
use App\Services\LoanService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Producto Financiero - Control Finance')]
class ProductForm extends Component
{
    public ?FinancialProduct $product = null;
    public $productId;

    // Campos básicos
    public $name = '';
    public $product_type = '';
    public $institution = '';
    public $last_four_digits = '';
    public $card_brand = '';
    public $notes = '';
    public $is_active = true;

    // Campos para tarjetas de crédito y débito
    public $credit_limit = '';
    public $current_balance = '';
    public $billing_day = '';
    public $payment_due_day = '';
    public $expiration_date = '';

    // Campos para cuentas de ahorro
    public $interest_rate = '';
    public $monthly_withdrawal_limit = '';

    // Campos para préstamos
    public $loan_amount = '';
    public $loan_term_months = '';
    public $monthly_payment = '';
    public $start_date = '';

    // Campos para crédito por bien
    public $asset_type = '';
    public $supplier = '';

    public function mount($product = null)
    {
        if ($product) {
            // Si $product es un objeto FinancialProduct, usarlo directamente
            // Si es un ID, buscarlo en la base de datos
            if ($product instanceof FinancialProduct) {
                $this->product = $product;
                // Verificar que el producto pertenece al usuario actual
                if ($this->product->user_id !== auth()->id()) {
                    abort(404);
                }
            } else {
                $this->product = FinancialProduct::where('id', $product)
                    ->where('user_id', auth()->id())
                    ->firstOrFail();
            }

            $this->productId = $this->product->id;
            $this->name = $this->product->name;
            $this->product_type = $this->product->product_type;
            $this->institution = $this->product->institution ?? '';
            $this->last_four_digits = $this->product->last_four_digits ?? '';
            $this->card_brand = $this->product->card_brand ?? '';
            $this->notes = $this->product->notes ?? '';
            $this->is_active = $this->product->is_active;

            // Campos de tarjetas
            $this->credit_limit = $this->product->credit_limit ? $this->product->credit_limit / 100 : '';
            $this->current_balance = $this->product->current_balance ? $this->product->current_balance / 100 : '';
            $this->billing_day = $this->product->billing_day ?? '';
            $this->payment_due_day = $this->product->payment_due_day ?? '';
            $this->expiration_date = $this->product->expiration_date ? $this->product->expiration_date->format('Y-m-d') : '';

            // Campos de ahorro
            $this->interest_rate = $this->product->interest_rate ?? '';
            $this->monthly_withdrawal_limit = $this->product->monthly_withdrawal_limit ?? '';

            // Campos de préstamos
            $this->loan_amount = $this->product->loan_amount ? $this->product->loan_amount / 100 : '';
            $this->loan_term_months = $this->product->loan_term_months ?? '';
            $this->monthly_payment = $this->product->monthly_payment ? $this->product->monthly_payment / 100 : '';
            $this->start_date = $this->product->start_date ? $this->product->start_date->format('Y-m-d') : '';

            // Campos de crédito por bien
            $this->asset_type = $this->product->asset_type ?? '';
            $this->supplier = $this->product->supplier ?? '';
        } else {
            // Valores por defecto para nuevo producto
            $this->start_date = now()->format('Y-m-d');
        }
    }

    /**
     * Calcular cuota mensual cuando cambian los datos del préstamo
     */
    public function updatedLoanAmount()
    {
        $this->calculateMonthlyPayment();
    }

    public function updatedLoanTermMonths()
    {
        $this->calculateMonthlyPayment();
    }

    public function updatedInterestRate()
    {
        if ($this->product_type === 'personal_loan' || $this->product_type === 'asset_loan') {
            $this->calculateMonthlyPayment();
        }
    }

    protected function calculateMonthlyPayment()
    {
        if ($this->loan_amount && $this->loan_term_months && $this->interest_rate) {
            $loanService = new LoanService();
            $loanAmountCents = (int) ($this->loan_amount * 100);
            $monthlyPaymentCents = $loanService->calculateMonthlyPayment(
                $loanAmountCents,
                (float) $this->interest_rate,
                (int) $this->loan_term_months
            );
            $this->monthly_payment = number_format($monthlyPaymentCents / 100, 2, '.', '');
        }
    }

    protected function rules()
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'product_type' => ['required', 'in:credit_card,debit_card,digital_wallet,credit_line,savings_account,personal_loan,asset_loan'],
            'institution' => ['nullable', 'string', 'max:255'],
            'last_four_digits' => ['nullable', 'string', 'max:4'],
            'card_brand' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];

        // Reglas específicas según el tipo de producto
        if (in_array($this->product_type, ['credit_card', 'credit_line'])) {
            $rules['credit_limit'] = ['required', 'numeric', 'min:0'];
            $rules['billing_day'] = ['nullable', 'integer', 'min:1', 'max:31'];
            $rules['payment_due_day'] = ['nullable', 'integer', 'min:1', 'max:31'];
            $rules['current_balance'] = ['nullable', 'numeric', 'min:0'];
            $rules['expiration_date'] = ['nullable', 'date'];
        }

        if ($this->product_type === 'debit_card') {
            $rules['current_balance'] = ['required', 'numeric', 'min:0'];
            $rules['expiration_date'] = ['nullable', 'date'];
        }

        if ($this->product_type === 'savings_account') {
            $rules['current_balance'] = ['required', 'numeric', 'min:0'];
            $rules['interest_rate'] = ['required', 'numeric', 'min:0', 'max:100'];
            $rules['monthly_withdrawal_limit'] = ['nullable', 'integer', 'min:0'];
        }

        if (in_array($this->product_type, ['personal_loan', 'asset_loan'])) {
            $rules['loan_amount'] = ['required', 'numeric', 'min:1'];
            $rules['loan_term_months'] = ['required', 'integer', 'min:1', 'max:360'];
            $rules['interest_rate'] = ['required', 'numeric', 'min:0', 'max:100'];
            $rules['start_date'] = ['required', 'date'];
        }

        if ($this->product_type === 'asset_loan') {
            $rules['asset_type'] = ['required', 'string', 'max:255'];
            $rules['supplier'] = ['nullable', 'string', 'max:255'];
        }

        return $rules;
    }

    protected $messages = [
        'name.required' => 'El nombre del producto es obligatorio.',
        'product_type.required' => 'El tipo de producto es obligatorio.',
        'current_balance.required' => 'El saldo actual es obligatorio.',
    ];

    public function save()
    {
        $validated = $this->validate();

        // Convertir montos de dólares a centavos
        if (isset($validated['credit_limit'])) {
            $validated['credit_limit'] = (int) ($validated['credit_limit'] * 100);
        }

        if (isset($validated['current_balance'])) {
            $validated['current_balance'] = (int) ($validated['current_balance'] * 100);
        }

        if (isset($validated['loan_amount'])) {
            $validated['loan_amount'] = (int) ($validated['loan_amount'] * 100);
        }

        // Para préstamos, inicializar usando LoanService
        if (in_array($validated['product_type'], ['personal_loan', 'asset_loan'])) {
            $loanService = new LoanService();
            $loanData = $loanService->initializeLoan([
                'loan_amount' => $validated['loan_amount'],
                'interest_rate' => $validated['interest_rate'],
                'loan_term_months' => $validated['loan_term_months'],
                'start_date' => $validated['start_date'],
            ]);

            $validated['monthly_payment'] = $loanData['monthly_payment'];
            $validated['next_payment_date'] = $loanData['next_payment_date'];
            $validated['current_balance'] = $loanData['current_balance'];
            $validated['payments_made'] = 0;
        }

        // Para cuentas de ahorro, inicializar contadores
        if ($validated['product_type'] === 'savings_account') {
            $validated['current_month_withdrawals'] = 0;
            $validated['last_interest_date'] = now();
        }

        $validated['user_id'] = auth()->id();

        if ($this->product) {
            $this->product->update($validated);
            session()->flash('success', 'Producto actualizado exitosamente.');
        } else {
            FinancialProduct::create($validated);
            session()->flash('success', 'Producto creado exitosamente.');
        }

        return $this->redirect(route('products.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.financial-products.product-form');
    }
}
