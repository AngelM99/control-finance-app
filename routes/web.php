<?php

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\PublicConsultation;
use App\Livewire\Dashboard\UserDashboard;
use App\Livewire\Dashboard\AdminDashboard;
use App\Livewire\FinancialProducts\ProductList;
use App\Livewire\FinancialProducts\ProductForm;
use App\Livewire\FinancialProducts\LoanSchedule;
use App\Livewire\Transactions\TransactionList;
use App\Livewire\Transactions\TransactionForm;
use App\Livewire\Installments\InstallmentList;
use App\Livewire\Lenders\LenderList;
use App\Livewire\Lenders\LenderForm;
use App\Livewire\Reports\BorrowerReport;
use App\Livewire\Reports\MyBorrowers;
use App\Livewire\Reports\TransactionReport;
use App\Livewire\Reports\AccountStatementReport;
use App\Livewire\Reports\CardPaymentReport;
use App\Livewire\Admin\PendingUsers;
use App\Livewire\TestComponent;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// Public Consultation (no authentication required)
Route::get('/consulta-publica', PublicConsultation::class)->name('public.consultation');

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');

    // Google OAuth routes
    Route::get('/auth/google/redirect', [\App\Http\Controllers\Auth\SocialiteController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\SocialiteController::class, 'callback'])->name('auth.google.callback');
});

Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'approved'])->group(function () {

    // Test route
    Route::get('/test-route', function () {
        return 'Route works! User: ' . auth()->user()->name;
    })->name('test.route');

    Route::get('/test-crear', function () {
        return 'Test /crear route works!';
    })->name('test.crear');

    // Dashboard routes
    Route::get('/dashboard', function () {
        if (auth()->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('user.dashboard');
    })->name('dashboard');

    Route::get('/user/dashboard', UserDashboard::class)
        ->name('user.dashboard')
        ->middleware('role:Usuario Activo');

    Route::get('/admin/dashboard', AdminDashboard::class)
        ->name('admin.dashboard')
        ->middleware('role:Administrador');

    // Financial Products
    Route::get('/productos', ProductList::class)->name('products.index');
    Route::get('/productos/crear', ProductForm::class)->name('products.create');
    Route::get('/productos/{product}/editar', ProductForm::class)->name('products.edit');
    Route::get('/productos/{product}/cronograma', LoanSchedule::class)->name('products.loan-schedule');

    // Transactions
    Route::get('/transacciones', TransactionList::class)->name('transactions.index');
    Route::get('/transacciones/crear', TransactionForm::class)->name('transactions.create');
    Route::get('/transacciones/{transaction}/editar', TransactionForm::class)->name('transactions.edit');

    // Installments
    Route::get('/cuotas', InstallmentList::class)->name('installments.index');

    // Lenders (Prestamistas)
    Route::get('/prestamistas', LenderList::class)->name('lenders.index');
    Route::get('/prestamistas/crear', LenderForm::class)->name('lenders.create');
    Route::get('/prestamistas/{lender}/editar', LenderForm::class)->name('lenders.edit');

    // Reports
    Route::get('/reportes/prestamistas', BorrowerReport::class)->name('reports.borrowers')->middleware('role:Administrador');
    Route::get('/mis-prestamistas', MyBorrowers::class)->name('reports.my-borrowers')->middleware('role:Administrador');
    Route::get('/reporte-transacciones', TransactionReport::class)->name('reports.transactions');
    Route::get('/reporte-estado-cuenta', AccountStatementReport::class)->name('reports.account-statement');
    Route::get('/reporte-pagos-tarjeta', CardPaymentReport::class)->name('reports.card-payments');

    // Test
    Route::get('/test-livewire', TestComponent::class)->name('test.livewire');

    // Admin routes
    Route::middleware('role:Administrador')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/usuarios-pendientes', PendingUsers::class)->name('pending-users');
    });
});
