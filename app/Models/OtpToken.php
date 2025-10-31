<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OtpToken extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'dni',
        'token',
        'expires_at',
        'used',
        'used_at',
        'ip_address',
        'attempts',
        'last_attempt_at',
        'user_agent',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean',
        'used_at' => 'datetime',
        'last_attempt_at' => 'datetime',
        'attempts' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Generate a random 6-digit OTP code.
     */
    public static function generateToken(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new OTP token for a DNI.
     */
    public static function createForDni(string $dni, int $expirationMinutes = 10): self
    {
        return self::create([
            'dni' => $dni,
            'token' => self::generateToken(),
            'expires_at' => Carbon::now()->addMinutes($expirationMinutes),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Check if the token is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if the token is valid (not used and not expired).
     */
    public function isValid(): bool
    {
        return !$this->used && !$this->isExpired();
    }

    /**
     * Mark the token as used.
     */
    public function markAsUsed(): void
    {
        $this->update([
            'used' => true,
            'used_at' => Carbon::now(),
        ]);
    }

    /**
     * Increment the failed attempts count.
     */
    public function incrementAttempts(): void
    {
        $this->increment('attempts');
        $this->update(['last_attempt_at' => Carbon::now()]);
    }

    /**
     * Check if maximum attempts have been reached.
     */
    public function hasMaxAttemptsReached(int $maxAttempts = 3): bool
    {
        return $this->attempts >= $maxAttempts;
    }

    /**
     * Scope to get valid tokens (not used and not expired).
     */
    public function scopeValid($query)
    {
        return $query->where('used', false)
                    ->where('expires_at', '>', Carbon::now());
    }

    /**
     * Scope to get tokens for a specific DNI.
     */
    public function scopeForDni($query, string $dni)
    {
        return $query->where('dni', $dni);
    }

    /**
     * Scope to get expired tokens.
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', Carbon::now());
    }

    /**
     * Scope to get unused tokens.
     */
    public function scopeUnused($query)
    {
        return $query->where('used', false);
    }
}
