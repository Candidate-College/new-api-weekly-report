<?php

namespace App\Repositories;

use App\Models\OTP;

class OtpRepository
{
    public function createOtp(int $userId, string $otpCode, string $token, $expirationTime)
    {
        return OTP::create([
            'user_id' => $userId,
            'OTP_code' => $otpCode,
            'token' => $token,
            'expiration_time' => $expirationTime,
            'created_at' => now(),
        ]);
    }

    public function getLatestOtp(int $userId)
    {
        return OTP::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function verifyOtp(int $userId, string $token, string $otp)
    {
        return OTP::where('user_id', $userId)
            ->where('token', $token)
            ->where('OTP_code', $otp)
            ->where('expiration_time', '>', now())
            ->first();
    }
}
