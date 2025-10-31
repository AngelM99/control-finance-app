<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'lender_id',
        'financial_product_id',
        'transaction_type',
        'amount',
        'description',
        'transaction_date',
        'category',
        'merchant',
        'reference_number',
        'is_installment',
        'installment_id',
        'status',
        'notes',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'integer',
        'transaction_date' => 'date',
        'is_installment' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Transaction types.
     */
    const TYPE_PURCHASE = 'purchase';
    const TYPE_PAYMENT = 'payment';
    const TYPE_TRANSFER = 'transfer';
    const TYPE_WITHDRAWAL = 'withdrawal';
    const TYPE_DEPOSIT = 'deposit';
    const TYPE_REFUND = 'refund';
    const TYPE_ADJUSTMENT = 'adjustment';

    /**
     * Transaction statuses.
     */
    const STATUS_COMPLETED = 'completed';
    const STATUS_PENDING = 'pending';
    const STATUS_CANCELED = 'canceled';
    const STATUS_REFUNDED = 'refunded';

    /**
     * Get the user that owns the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the lender associated with this transaction.
     */
    public function lender(): BelongsTo
    {
        return $this->belongsTo(Lender::class);
    }

    /**
     * Get the financial product associated with this transaction.
     */
    public function financialProduct(): BelongsTo
    {
        return $this->belongsTo(FinancialProduct::class);
    }

    /**
     * Get the installment associated with this transaction.
     */
    public function installment(): BelongsTo
    {
        return $this->belongsTo(Installment::class);
    }

    /**
     * Get the amount in currency format (dollars).
     */
    public function getAmountInDollarsAttribute(): float
    {
        return $this->amount / 100;
    }

    /**
     * Check if transaction is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if transaction is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Scope to get completed transactions.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope to get pending transactions.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to filter by transaction type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('transaction_type', $type);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by category.
     */
    public function scopeInCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
