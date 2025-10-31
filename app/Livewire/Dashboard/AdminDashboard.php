<?php

namespace App\Livewire\Dashboard;

use App\Models\User;
use App\Models\FinancialProduct;
use App\Models\Transaction;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Panel de Administración - Control Finance')]
class AdminDashboard extends Component
{
    public $totalUsers = 0;
    public $pendingApprovals = 0;
    public $totalProducts = 0;
    public $totalTransactions = 0;
    public $recentUsers = [];
    public $recentTransactions = [];

    public function mount()
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        // Load user stats
        $this->totalUsers = User::count();
        $this->pendingApprovals = User::where('is_approved', false)->count();

        // Load product stats
        $this->totalProducts = FinancialProduct::count();

        // Load transaction stats
        $this->totalTransactions = Transaction::count();

        // Load recent users
        $this->recentUsers = User::latest()->limit(5)->get();

        // Load recent transactions
        $this->recentTransactions = Transaction::with(['user', 'financialProduct'])
            ->latest('transaction_date')
            ->limit(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard.admin-dashboard');
    }
}
