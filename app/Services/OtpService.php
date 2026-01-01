<?php

namespace App\Services;

use App\Models\Otp;
use Carbon\Carbon;

class OtpService
{
    /**
     * Generate and store a new OTP for a recipient.
     */
    public function generateOtp(string $recipient, string $type): string
    {
        // Revoke any old OTPs for this recipient
        Otp::where($type, $recipient)->delete();

        // Generate a new code
        $code = (string) random_int(100000, 999999);
        $expiresAt = Carbon::now()->addMinutes(10); // 10 minute expiry

        Otp::create([
            $type => $recipient,
            'type' => $type,
            'code' => $code,
            'expires_at' => $expiresAt,
        ]);

        return $code;
    }

    /**
     * Get a valid, non-expired OTP.
     */
    public function getValidOtp(string $recipient, string $code, string $type): ?Otp
    {
        return Otp::where($type, $recipient)
            ->where('code', $code)
            ->where('expires_at', '>', Carbon::now())
            ->first();
    }

    /**
     * Delete a used OTP.
     */
    public function deleteOtp(Otp $otp): void
    {
        $otp->delete();
    }
}
