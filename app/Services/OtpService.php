<?php

namespace App\Services;

use App\Models\OtpToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    /**
     * Generate and send OTP for a DNI.
     */
    public function generateOtp(string $dni): ?OtpToken
    {
        // Find user by DNI
        $user = User::where('dni', $dni)->first();

        if (!$user) {
            return null;
        }

        // Invalidate old OTPs for this DNI
        OtpToken::where('dni', $dni)
            ->where('used', false)
            ->update(['used' => true]);

        // Create new OTP
        $otp = OtpToken::createForDni($dni, 10);

        // TODO: Send OTP via email or SMS
        // For now, we'll just log it
        \Log::info("OTP generated for DNI {$dni}: {$otp->token}");

        return $otp;
    }

    /**
     * Validate OTP for a DNI.
     */
    public function validateOtp(string $dni, string $token): bool
    {
        $otp = OtpToken::forDni($dni)
            ->where('token', $token)
            ->valid()
            ->first();

        if (!$otp) {
            // Try to find the token to increment attempts
            $attemptOtp = OtpToken::forDni($dni)
                ->where('token', $token)
                ->unused()
                ->first();

            if ($attemptOtp) {
                $attemptOtp->incrementAttempts();
            }

            return false;
        }

        // Check if max attempts reached
        if ($otp->hasMaxAttemptsReached()) {
            return false;
        }

        // Mark as used
        $otp->markAsUsed();

        return true;
    }

    /**
     * Get user by DNI and validated OTP.
     */
    public function getUserByDniAndOtp(string $dni, string $token): ?User
    {
        if ($this->validateOtp($dni, $token)) {
            return User::where('dni', $dni)->first();
        }

        return null;
    }

    /**
     * Clean expired OTPs.
     */
    public function cleanExpiredOtps(): int
    {
        return OtpToken::expired()->delete();
    }
}
