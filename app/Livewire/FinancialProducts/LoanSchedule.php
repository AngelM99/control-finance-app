<?php

namespace App\Livewire\FinancialProducts;

use App\Models\FinancialProduct;
use App\Services\LoanService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Cronograma de Pagos - Control Finance')]
class LoanSchedule extends Component
{
    public $productId;
    public ?FinancialProduct $product = null;
    public $schedule = [];
    public $summary = [];

    public function mount($product)
    {
        $this->product = FinancialProduct::where('id', $product)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if (!$this->product->isLoan()) {
            abort(404, 'Este producto no es un préstamo.');
        }

        $this->loadSchedule();
    }

    public function loadSchedule()
    {
        $loanService = new LoanService();
        $this->schedule = $loanService->generatePaymentSchedule($this->product);
        $this->summary = $loanService->getLoanSummary($this->product);
    }

    public function render()
    {
        return view('livewire.financial-products.loan-schedule');
    }
}
