<?php

namespace App\Livewire\Dashboard;

use App\Models\FinancialProduct;
use App\Models\Transaction;
use App\Models\Installment;
use App\Services\TransactionService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Dashboard - Control Finance')]
class UserDashboard extends Component
{
    public $totalProducts = 0;
    public $activeProducts = 0;
    public $totalBalance = 0;
    public $totalCreditLimit = 0;
    public $recentTransactions = [];
    public $pendingInstallments = 0;
    public $creditCardsSummary = [];

    public function mount()
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        $userId = auth()->id();

        // Load financial products stats
        $products = FinancialProduct::where('user_id', $userId)->get();
        $this->totalProducts = $products->count();
        $this->activeProducts = $products->where('is_active', true)->count();
        $this->totalBalance = $products->sum('current_balance');
        $this->totalCreditLimit = $products->sum('credit_limit');

        // Load recent transactions
        $this->recentTransactions = Transaction::where('user_id', $userId)
            ->with('financialProduct')
            ->latest('transaction_date')
            ->limit(10)
            ->get();

        // Load pending installments
        $this->pendingInstallments = Installment::where('user_id', $userId)
            ->where('status', 'active')
            ->count();

        // Load billing period information for credit cards
        $this->loadCreditCardsSummary($products);
    }

    protected function loadCreditCardsSummary($products)
    {
        $transactionService = new TransactionService();
        $savingsAccountService = new \App\Services\SavingsAccountService();
        $loanService = new \App\Services\LoanService();

        $this->creditCardsSummary = [];

        foreach ($products as $product) {
            if (!$product->is_active) {
                continue;
            }

            $summaryData = [
                'product' => $product,
                'type' => $product->product_type,
            ];

            // Calcular información según el tipo de producto
            if ($product->isCreditCard() || $product->isCreditLine()) {
                $summaryData['summary'] = $transactionService->getCurrentPeriodSummary($product);
                $summaryData['usage_percent'] = $product->credit_usage_percentage;
            } elseif ($product->isSavingsAccount()) {
                $summaryData['summary'] = $savingsAccountService->getAccountSummary($product);
            } elseif ($product->isLoan()) {
                $summaryData['summary'] = $loanService->getLoanSummary($product);
            } elseif ($product->isDebitCard()) {
                $summaryData['summary'] = [
                    'available_balance' => $product->current_balance,
                    'available_balance_dollars' => $product->current_balance / 100,
                ];
            }

            $this->creditCardsSummary[] = $summaryData;
        }
    }

    public function render()
    {
        return view('livewire.dashboard.user-dashboard');
    }
}
