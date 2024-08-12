<?php

namespace App\Services;

use App\Repositories\OtpRepository;
use Illuminate\Support\Str;

class OtpService
{
    protected $otpRepository;

    public function __construct(OtpRepository $otpRepository)
    {
        $this->otpRepository = $otpRepository;
    }

    public function generateOtp(int $userId)
    {
        $token = Str::random(32);
        $otpCode = str_pad((string) rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $expirationTime = now()->addMinutes(5);

        $this->otpRepository->createOtp($userId, $otpCode, $token, $expirationTime);

        return [
            'otpCode' => $otpCode,
            'token' => $token,
        ];
    }

    public function verifyOtp(int $userId, string $token, string $otp)
    {
        return $this->otpRepository->verifyOtp($userId, $token, $otp);
    }

    public function getLatestOtp(int $userId)
    {
        return $this->otpRepository->getLatestOtp($userId);
    }
}
