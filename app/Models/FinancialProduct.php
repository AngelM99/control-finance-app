<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancialProduct extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'product_type',
        'name',
        'institution',
        'last_four_digits',
        'card_brand',
        'credit_limit',
        'current_balance',
        'available_balance',
        'expiration_date',
        'billing_day',
        'payment_due_day',
        'is_active',
        'notes',
        // Savings account fields
        'interest_rate',
        'last_interest_date',
        'monthly_withdrawal_limit',
        'current_month_withdrawals',
        // Loan fields
        'loan_amount',
        'loan_term_months',
        'monthly_payment',
        'start_date',
        'next_payment_date',
        'payments_made',
        // Asset loan fields
        'asset_type',
        'supplier',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'credit_limit' => 'integer',
        'current_balance' => 'integer',
        'available_balance' => 'integer',
        'expiration_date' => 'date',
        'billing_day' => 'integer',
        'payment_due_day' => 'integer',
        'is_active' => 'boolean',
        // Savings account
        'interest_rate' => 'decimal:2',
        'last_interest_date' => 'date',
        'monthly_withdrawal_limit' => 'integer',
        'current_month_withdrawals' => 'integer',
        // Loans
        'loan_amount' => 'integer',
        'loan_term_months' => 'integer',
        'monthly_payment' => 'integer',
        'start_date' => 'date',
        'next_payment_date' => 'date',
        'payments_made' => 'integer',
    ];

    /**
     * Product types available.
     */
    const TYPE_CREDIT_CARD = 'credit_card';
    const TYPE_DEBIT_CARD = 'debit_card';
    const TYPE_DIGITAL_WALLET = 'digital_wallet';
    const TYPE_CREDIT_LINE = 'credit_line';
    const TYPE_SAVINGS_ACCOUNT = 'savings_account';
    const TYPE_PERSONAL_LOAN = 'personal_loan';
    const TYPE_ASSET_LOAN = 'asset_loan';

    /**
     * Get the user that owns the financial product.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all transactions for this financial product.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get all installments for this financial product.
     */
    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class);
    }

    /**
     * Get the credit limit in currency format (dollars).
     */
    public function getCreditLimitInDollarsAttribute(): float
    {
        return $this->credit_limit / 100;
    }

    /**
     * Get the current balance in currency format (dollars).
     */
    public function getCurrentBalanceInDollarsAttribute(): float
    {
        return $this->current_balance / 100;
    }

    /**
     * Get the available balance in currency format (dollars).
     */
    public function getAvailableBalanceInDollarsAttribute(): float
    {
        return $this->available_balance / 100;
    }

    /**
     * Get the available credit (limit - current balance).
     */
    public function getAvailableCreditAttribute(): int
    {
        if ($this->credit_limit <= 0) {
            return 0;
        }
        return max(0, $this->credit_limit - $this->current_balance);
    }

    /**
     * Get the available credit in dollars.
     */
    public function getAvailableCreditInDollarsAttribute(): float
    {
        return $this->available_credit / 100;
    }

    /**
     * Get the credit usage percentage.
     */
    public function getCreditUsagePercentageAttribute(): float
    {
        if ($this->credit_limit <= 0) {
            return 0;
        }
        return ($this->current_balance / $this->credit_limit) * 100;
    }

    /**
     * Check if this is a credit card.
     */
    public function isCreditCard(): bool
    {
        return $this->product_type === self::TYPE_CREDIT_CARD;
    }

    /**
     * Check if this is a debit card.
     */
    public function isDebitCard(): bool
    {
        return $this->product_type === self::TYPE_DEBIT_CARD;
    }

    /**
     * Check if this is a digital wallet.
     */
    public function isDigitalWallet(): bool
    {
        return $this->product_type === self::TYPE_DIGITAL_WALLET;
    }

    /**
     * Check if this is a credit line.
     */
    public function isCreditLine(): bool
    {
        return $this->product_type === self::TYPE_CREDIT_LINE;
    }

    /**
     * Check if this is a savings account.
     */
    public function isSavingsAccount(): bool
    {
        return $this->product_type === self::TYPE_SAVINGS_ACCOUNT;
    }

    /**
     * Check if this is a personal loan.
     */
    public function isPersonalLoan(): bool
    {
        return $this->product_type === self::TYPE_PERSONAL_LOAN;
    }

    /**
     * Check if this is an asset loan.
     */
    public function isAssetLoan(): bool
    {
        return $this->product_type === self::TYPE_ASSET_LOAN;
    }

    /**
     * Check if this is any type of loan (personal or asset).
     */
    public function isLoan(): bool
    {
        return $this->isPersonalLoan() || $this->isAssetLoan();
    }

    /**
     * Scope to get only active products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only credit cards.
     */
    public function scopeCreditCards($query)
    {
        return $query->where('product_type', self::TYPE_CREDIT_CARD);
    }

    /**
     * Scope to get only debit cards.
     */
    public function scopeDebitCards($query)
    {
        return $query->where('product_type', self::TYPE_DEBIT_CARD);
    }

    /**
     * Scope to get only digital wallets.
     */
    public function scopeDigitalWallets($query)
    {
        return $query->where('product_type', self::TYPE_DIGITAL_WALLET);
    }

    /**
     * Scope to get only credit lines.
     */
    public function scopeCreditLines($query)
    {
        return $query->where('product_type', self::TYPE_CREDIT_LINE);
    }

    /**
     * Scope to get only savings accounts.
     */
    public function scopeSavingsAccounts($query)
    {
        return $query->where('product_type', self::TYPE_SAVINGS_ACCOUNT);
    }

    /**
     * Scope to get only personal loans.
     */
    public function scopePersonalLoans($query)
    {
        return $query->where('product_type', self::TYPE_PERSONAL_LOAN);
    }

    /**
     * Scope to get only asset loans.
     */
    public function scopeAssetLoans($query)
    {
        return $query->where('product_type', self::TYPE_ASSET_LOAN);
    }

    /**
     * Scope to get all loans (personal and asset).
     */
    public function scopeLoans($query)
    {
        return $query->whereIn('product_type', [self::TYPE_PERSONAL_LOAN, self::TYPE_ASSET_LOAN]);
    }

    /**
     * Get the masked account/card number for display.
     */
    public function getMaskedNumberAttribute(): string
    {
        return $this->last_four_digits ? '**** ' . $this->last_four_digits : 'N/A';
    }

    /**
     * Get the loan amount in dollars.
     */
    public function getLoanAmountInDollarsAttribute(): float
    {
        return $this->loan_amount ? $this->loan_amount / 100 : 0;
    }

    /**
     * Get the monthly payment in dollars.
     */
    public function getMonthlyPaymentInDollarsAttribute(): float
    {
        return $this->monthly_payment ? $this->monthly_payment / 100 : 0;
    }

    /**
     * Get remaining loan balance (for loans).
     */
    public function getRemainingLoanBalanceAttribute(): int
    {
        if (!$this->isLoan() || !$this->loan_amount) {
            return 0;
        }
        return max(0, $this->loan_amount - $this->current_balance);
    }

    /**
     * Get remaining loan balance in dollars.
     */
    public function getRemainingLoanBalanceInDollarsAttribute(): float
    {
        return $this->remaining_loan_balance / 100;
    }

    /**
     * Get remaining payments count (for loans).
     */
    public function getRemainingPaymentsAttribute(): int
    {
        if (!$this->isLoan() || !$this->loan_term_months) {
            return 0;
        }
        return max(0, $this->loan_term_months - $this->payments_made);
    }

    /**
     * Get loan progress percentage.
     */
    public function getLoanProgressPercentageAttribute(): float
    {
        if (!$this->isLoan() || !$this->loan_term_months || $this->loan_term_months == 0) {
            return 0;
        }
        return ($this->payments_made / $this->loan_term_months) * 100;
    }

    /**
     * Check if can withdraw (for savings accounts).
     */
    public function canWithdraw(): bool
    {
        if (!$this->isSavingsAccount()) {
            return true;
        }

        if (!$this->monthly_withdrawal_limit) {
            return true;
        }

        return $this->current_month_withdrawals < $this->monthly_withdrawal_limit;
    }

    /**
     * Get remaining withdrawals for this month (for savings accounts).
     */
    public function getRemainingWithdrawalsAttribute(): int
    {
        if (!$this->isSavingsAccount() || !$this->monthly_withdrawal_limit) {
            return 999; // Sin límite
        }
        return max(0, $this->monthly_withdrawal_limit - $this->current_month_withdrawals);
    }
}
