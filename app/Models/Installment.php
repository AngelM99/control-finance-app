<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Installment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'financial_product_id',
        'total_amount',
        'installment_count',
        'installment_amount',
        'current_installment',
        'total_paid',
        'description',
        'merchant',
        'purchase_date',
        'first_payment_date',
        'last_payment_date',
        'status',
        'notes',
        'payment_schedule',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_amount' => 'integer',
        'installment_count' => 'integer',
        'installment_amount' => 'integer',
        'current_installment' => 'integer',
        'total_paid' => 'integer',
        'purchase_date' => 'date',
        'first_payment_date' => 'date',
        'last_payment_date' => 'date',
        'payment_schedule' => 'array',
    ];

    /**
     * Installment statuses.
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELED = 'canceled';

    /**
     * Get the user that owns the installment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the financial product associated with this installment.
     */
    public function financialProduct(): BelongsTo
    {
        return $this->belongsTo(FinancialProduct::class);
    }

    /**
     * Get the total amount in currency format (dollars).
     */
    public function getTotalAmountInDollarsAttribute(): float
    {
        return $this->total_amount / 100;
    }

    /**
     * Get the installment amount in currency format (dollars).
     */
    public function getInstallmentAmountInDollarsAttribute(): float
    {
        return $this->installment_amount / 100;
    }

    /**
     * Get the current installment number (calculated from total paid).
     * This is more accurate than the database field as it accounts for rounding errors.
     */
    public function getCurrentInstallmentAttribute(): int
    {
        // Usar el valor de la base de datos si existe y es mayor que 0
        $dbValue = $this->attributes['current_installment'] ?? 0;

        // Calcular basado en el monto pagado
        $totalPaid = $this->attributes['total_paid'] ?? 0;
        $installmentAmount = $this->attributes['installment_amount'] ?? 1;

        if ($installmentAmount <= 0) {
            return $dbValue;
        }

        // Calcular cuotas pagadas con tolerancia para errores de redondeo (99% de una cuota)
        $calculatedInstallments = 0;
        if ($totalPaid > 0) {
            // Si el pago cubre al menos el 99% de una cuota, contarla como completa
            $exactInstallments = $totalPaid / $installmentAmount;
            $calculatedInstallments = floor($exactInstallments);

            // Si hay un residuo mayor al 99% de una cuota, contar una más
            $remainder = $exactInstallments - $calculatedInstallments;
            if ($remainder >= 0.99) {
                $calculatedInstallments++;
            }
        }

        // Retornar el mayor entre el valor de BD y el calculado
        return max($dbValue, $calculatedInstallments);
    }

    /**
     * Get the remaining installments count.
     */
    public function getRemainingInstallmentsAttribute(): int
    {
        return $this->installment_count - $this->current_installment;
    }

    /**
     * Get the remaining amount to pay.
     */
    public function getRemainingAmountAttribute(): int
    {
        return $this->total_amount - ($this->total_paid ?? 0);
    }

    /**
     * Get the total paid in dollars.
     */
    public function getTotalPaidInDollarsAttribute(): float
    {
        return ($this->total_paid ?? 0) / 100;
    }

    /**
     * Get the remaining amount in dollars.
     */
    public function getRemainingAmountInDollarsAttribute(): float
    {
        return $this->remaining_amount / 100;
    }

    /**
     * Get payment progress percentage.
     */
    public function getPaymentProgressAttribute(): float
    {
        if ($this->total_amount == 0) {
            return 0;
        }
        return (($this->total_paid ?? 0) / $this->total_amount) * 100;
    }

    /**
     * Get the progress percentage.
     */
    public function getProgressPercentageAttribute(): float
    {
        if ($this->installment_count == 0) {
            return 0;
        }
        return ($this->current_installment / $this->installment_count) * 100;
    }

    /**
     * Check if installment is active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if installment is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Scope to get active installments.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope to get completed installments.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }
}
