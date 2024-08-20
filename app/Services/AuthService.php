<?php

namespace App\Services;

use App\Models\User;

class AuthService
{
    protected OtpService $otpService;
    protected EmailService $emailService;

    public function __construct(OtpService $otpService, EmailService $emailService)
    {
        $this->otpService = $otpService;
        $this->emailService = $emailService;
    }

    public function sendOtp(int $userId)
    {
        $otpData = $this->otpService->generateOtp($userId);

        $user = User::find($userId);
        $userEmail = $user->email;
        $firstName = $user->first_name;
        $lastName = $user->last_name;

        $this->emailService->sendOtpMail($userEmail, $otpData['otpCode'], $firstName, $lastName);

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

