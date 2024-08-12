<?php

namespace App\Services;

use App\Models\User;
use App\Services\OtpService;
use App\Services\EmailService;

class AuthService
{
    protected $otpService;
    protected $emailService;

    public function __construct(OtpService $otpService, EmailService $emailService)
    {
        $this->otpService = $otpService;
        $this->emailService = $emailService;
    }

    public function sendOtp(int $userId)
    {
        $otpData = $this->otpService->generateOtp($userId);
        $userEmail = User::find($userId)->email;

        $this->emailService->sendOtpMail($userEmail, $otpData['otpCode']);

        return $otpData['token'];
    }

    public function verifyOtp(int $userId, string $token, string $otp)
    {
        return $this->otpService->verifyOtp($userId, $token, $otp);
    }

    public function getLatestOtp(int $userId)
    {
        return $this->otpService->getLatestOtp($userId);
    }
}
