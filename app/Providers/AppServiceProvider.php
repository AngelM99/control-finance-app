<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Models\Transaction;
use App\Models\FinancialProduct;
use App\Models\Lender;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Route Model Binding personalizado para Transaction
        Route::bind('transaction', function ($value) {
            return Transaction::where('id', $value)
                ->where('user_id', auth()->id())
                ->firstOrFail();
        });

        // Route Model Binding personalizado para FinancialProduct
        Route::bind('product', function ($value) {
            return FinancialProduct::where('id', $value)
                ->where('user_id', auth()->id())
                ->firstOrFail();
        });

        // Route Model Binding personalizado para Lender
        Route::bind('lender', function ($value) {
            return Lender::where('id', $value)
                ->where('user_id', auth()->id())
                ->firstOrFail();
        });
    }
}
